<?php

class FeedReaderService extends RestfulService
{
    private $_items;
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

    private function _addRSS2Items($response)
    {
        foreach ($response->xpath('//channel/item') as $seq => $node) {
            $content = (string) $node->description;
            $row = new ArrayData(array(
                'Id'      => (string) $node->guid,
                'Seq'     => $seq,
                'Link'    => (string) $node->link,
                'Date'    => self::_dateObject($node->pubDate),
                'Title'   => (string) $node->title,

                // RSS 2.0 does not have a summary field: generate it
                // from an excerpt of the "description" field
                'Summary' => self::_excerpt($content, $this->_summary_len),

                'Content' => $content
            ));
            $this->_items->push($row);
        }
    }

    private function _addAtom1Items($response)
    {
        foreach ($response->xpath('//feed/entry') as $node) {
            $summary = (string) $node->summary;
            $content = (string) $node->content;
            $row = new ArrayData(array(
                'Id'      => (string) $node->id,
                'Link'    => (string) $node->link['href'],
                'Date'    => self::_dateObject($node->updated),
                'Title'   => (string) $node->title,
                'Summary' => $summary,

                // Atom 1.0 does not require <content> elements, so
                // ensure it is at least populated with $summary
                'Content' => $content != '' ? $content : $summary
            ));
            $this->_items->push($row);
        }
    }


    public function __construct($url, $expiration = 3600)
    {
        parent::__construct($url, $expiration);
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
        if (is_null($this->_items)) {
            $response = $this->request();
            $code = $response->getStatusCode();
            if ($code != 200) {
                $body = $response->getBody();
                user_error("RSS fetch error ($code). The response body is '$body'", E_USER_ERROR);
            }
            $this->_items = new ArrayList();
            $this->_addRSS2Items($response);
            $this->_addAtom1Items($response);
        }

        return $this->_items;
    }
}
