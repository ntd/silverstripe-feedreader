<?php

namespace eNTiDi\FeedReader;

use GuzzleHttp\Client;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM;

class FeedReaderService
{
    private $_url;
    private $_expiration;
    private $_summary_len;


    private static function _dateObject($node)
    {
        $text = (string) $node;
        if (empty($text)) {
            return null;
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            return null;
        }

        return DBField::create_field('SS_Datetime', $timestamp);
    }

    private static function _excerpt($html, $maxlen)
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

    private function _appendRSS2Items(&$items, $response)
    {
        foreach ($response->xpath('//channel/item') as $seq => $node) {
            $content = (string) $node->description;
            $row = ArrayData::create([
                'Id'      => (string) $node->guid,
                'Seq'     => $seq,
                'Link'    => (string) $node->link,
                'Date'    => self::_dateObject($node->pubDate),
                'Title'   => (string) $node->title,

                // RSS 2.0 does not have a summary field: generate it
                // from an excerpt of the "description" field
                'Summary' => self::_excerpt($content, $this->_summary_len),

                'Content' => $content
            ]);
            $items->push($row);
        }
    }

    private function _appendAtom1Items(&$items, $response)
    {
        foreach ($response->xpath('//feed/entry') as $node) {
            $summary = (string) $node->summary;
            $content = (string) $node->content;
            $row = ArrayData::create([
                'Id'      => (string) $node->id,
                'Link'    => (string) $node->link['href'],
                'Date'    => self::_dateObject($node->updated),
                'Title'   => (string) $node->title,
                'Summary' => $summary,

                // Atom 1.0 does not require <content> elements, so
                // ensure it is at least populated with $summary
                'Content' => $content != '' ? $content : $summary
            ]);
            $items->push($row);
        }
    }

    public function __construct($url, $expiration = 3600)
    {
        $this->_url        = $url;
        $this->_expiration = $expiration;
    }

    public function setSummaryLen($maxlen)
    {
        $this->_summary_len = $maxlen;
    }

    public function getSummaryLen()
    {
        return is_int($this->_summary_len) ? $this->_summary_len : 155;
    }

    public function getItems()
    {
        $cache = Injector::inst()->get(CacheInterface::class . '.FeedReader');
        if ($cache->has('items')) {
            $items = $cache->get('items');
        } else {
            $client = new Client([
                'base_uri' => $this->_url,
                'timeout'  => 2,
            ]);
            $client   = new Client([ 'timeout' => 2 ]);
            $response = $client->request('GET', $this->_url);
            $code     = $response->getStatusCode();
            $data     = $response->getBody()->getContents();
            if ($code != 200) {
                user_error("RSS fetch error ($code). The response body is '$data'", E_USER_ERROR);
            }

            $xml = simplexml_load_string($data);
            $items = ArrayList::create();
            $this->_appendRSS2Items($items, $xml);
            $this->_appendAtom1Items($items, $xml);
            $cache->set('items', $items, $this->_expiration);
        }
        return $items;
    }
}
