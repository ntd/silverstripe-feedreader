<?php

namespace eNTiDi\FeedReader;

use GuzzleHttp\Client;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\ArrayData;
use SimpleXMLElement;

class FeedReaderService
{
    private $url;
    private $expiration;
    private $options;
    private $summary_len;

    private static function getCache()
    {
        return Injector::inst()->get(CacheInterface::class . '.FeedReader');
    }

    private function getKey()
    {
        return urlencode($this->url);
    }

    private static function dateObject($node)
    {
        $text = (string) $node;
        if (empty($text)) {
            return null;
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            return null;
        }

        return DBField::create_field('DBDatetime', $timestamp);
    }

    private static function excerpt($html, $maxlen)
    {
        // Strip HTML tags and convert blank chains to a single space
        $excerpt = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        if (strlen($excerpt) <= $maxlen) {
            return $excerpt;
        }

        // Try to cut the excerpt on a word boundary
        $pivot = strrpos(substr($excerpt, 0, $maxlen - 2), ' ');
        if ($pivot === false || $pivot < $maxlen - 15) {
            $pivot = $maxlen - 3;
        }
        $excerpt = rtrim(substr($excerpt, 0, $pivot));

        // Ellipsize the final result
        return rtrim($excerpt, '.') . '...';
    }

    private function appendRSS2Items(&$items, $xml)
    {
        foreach ($xml->xpath('//channel/item') as $node) {
            $content = (string) $node->description;
            $row = new ArrayData([
                'Id'      => (string) $node->guid,
                'Link'    => (string) $node->link,
                'Date'    => self::dateObject($node->pubDate),
                'Title'   => (string) $node->title,

                // RSS 2.0 does not have a summary field: generate it
                // from an excerpt of the "description" field
                'Summary' => self::excerpt($content, $this->getSummaryLen()),

                'Content' => $content
            ]);
            $items->push($row);
        }
    }

    private function appendAtom1Items(&$items, $xml)
    {
        $xml->registerXPathNamespace('A', 'http://www.w3.org/2005/Atom');
        foreach ($xml->xpath('//A:feed/A:entry') as $node) {
            $summary = (string) $node->summary;
            if (! $node->content) {
                // Atom 1.0 does not require <content> elements, so
                // ensure it is at least populated with $summary
                $content = $summary;
            } elseif ($node->content->count() > 0) {
                $content = $node->content->children()[0]->asXML();
            } else {
                $content = (string) $node->content;
            }
            $row = new ArrayData([
                'Id'      => (string) $node->id,
                'Link'    => (string) $node->link['href'],
                'Date'    => self::dateObject($node->updated),
                'Title'   => (string) $node->title,
                'Summary' => $summary,
                'Content' => $content,
            ]);
            $items->push($row);
        }
    }

    public function __construct($url, $expiration = 3600, $options = [])
    {
        $this->url        = $url;
        $this->expiration = $expiration;
        $this->options    = $options;
    }

    public function setSummaryLen($maxlen)
    {
        $this->summary_len = $maxlen;
    }

    public function getSummaryLen()
    {
        return is_int($this->summary_len) ? $this->summary_len : 155;
    }

    public function getItems()
    {
        $cache = self::getCache();
        $key   = $this->getKey();
        if ($cache->has($key)) {
            $items = $cache->get($key);
        } else {
            $client   = new Client($this->options + [ 'timeout' => 2 ]);
            $response = $client->request('GET', $this->url);
            $code     = $response->getStatusCode();
            $data     = $response->getBody()->getContents();
            if ($code != 200) {
                user_error("RSS fetch error ($code). The response body is '$data'", E_USER_ERROR);
            }

            $xml = new SimpleXMLElement($data);
            $items = new ArrayList();
            $this->appendRSS2Items($items, $xml);
            $this->appendAtom1Items($items, $xml);
            $cache->set($key, $items, $this->expiration);
        }
        return $items;
    }

    public function clearCache()
    {
        $cache = self::getCache();
        $cache->delete($this->getKey());
    }
}
