<?php

/**
 * Defines the FeedReaderPage page type.
 */
class FeedReaderPage extends Page
{
    static $icon = 'feedreader/img/rss.png';
    static $db = array(
        'FeedUrl'    => 'Varchar(254)',
        'SummaryLen' => 'Int',
        'Expiration' => 'Int'
    );
    public static $defaults = array(
        'SummaryLen' => 255,
        'Expiration' => 3600
    );


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('FeedUrl', _t('FeedReader.FEED_URL')),
            NumericField::create('SummaryLen', _t('FeedReader.SUMMARY_LEN'))
                ->setDescription(_t('FeedReader.SUMMARY_LEN_COMMENT')),
            NumericField::create('Expiration', _t('FeedReader.EXPIRATION'))
                ->setDescription(_t('FeedReader.EXPIRATION_COMMENT'))
        ), 'Content');

        return $fields;
    }

    private $_service;

    private function _getService()
    {
        if (is_null($this->_service)) {
            $this->_service = new FeedReaderService($this->FeedUrl, $this->Expiration);
            $this->_service->setSummaryLen($this->SummaryLen);
        }

        return $this->_service;
    }

    public function Items($count = null)
    {
        $service = $this->_getService();
        $items = $service->getItems();

        // When $count is null, limit() will use array_slice(..., 0, null)
        // internally, meaning a clone of $items will be returned
        return $items->limit($count);
    }
}

class FeedReaderPage_Controller extends Page_Controller
{
}
