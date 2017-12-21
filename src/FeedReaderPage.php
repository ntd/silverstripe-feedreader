<?php

namespace eNTiDi\FeedReader;

use Page;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;

/**
 * Defines the FeedReaderPage page type.
 */
class FeedReaderPage extends Page
{
    private static $table_name = 'FeedReaderPage';

    private static $db = [
        'FeedUrl'    => 'Varchar(254)',
        'SummaryLen' => 'Int',
        'Expiration' => 'Int'
    ];

    private static $defaults = [
        'SummaryLen' => 255,
        'Expiration' => 3600,
    ];

    private $service;


    public function getCMSFields()
    {
        $t_FeedUrl    = _t(__CLASS__.'.FEED_URL', 'Feed URL');
        $t_SummaryLen = _t(__CLASS__.'.SUMMARY_LEN', 'Summary length');
        $d_SummaryLen = _t(__CLASS__.'.SUMMARY_LEN_COMMENT', 'Maximum length of the summary field (in bytes) when it is generated programmatically');
        $t_Expiration = _t(__CLASS__.'.EXPIRATION', 'Cache timeout');
        $d_Expiration = _t(__CLASS__.'.EXPIRATION_COMMENT', 'How many seconds a cached copy must be accessed instead of downloading the real feed');

        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Feed', [
            TextField::create('FeedUrl', $t_FeedUrl),
            NumericField::create('SummaryLen', $t_SummaryLen)
                ->setDescription($d_SummaryLen),
            NumericField::create('Expiration', $t_Expiration)
                ->setDescription($d_Expiration),
        ]);
        return $fields;
    }

    public function getService()
    {
        if (! $this->service) {
            $this->service = Injector::inst()->create('eNTiDi\FeedReader\FeedReaderService');
            $this->service->setSummaryLen($this->SummaryLen);
        }
        return $this->service;
    }

    public function Items($count = null)
    {
        $items = $this->getService()->getItems();

        // When $count is null, limit() will use array_slice(..., 0, null)
        // internally, meaning a clone of $items will be returned
        return $items->limit($count);
    }
}
