<?php

namespace eNTiDi\FeedReader;

use Page;
use eNTiDi\FeedReader\FeedReaderService;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;

/**
 * Defines the FeedReaderPage page type.
 */
class FeedReaderPage extends Page
{
    private static $icon = 'feedreader/img/rss.png';

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
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Feed', [
            TextField::create('FeedUrl', _t('FeedReader.FEED_URL')),
            NumericField::create('SummaryLen', _t('FeedReader.SUMMARY_LEN'))
                ->setDescription(_t('FeedReader.SUMMARY_LEN_COMMENT')),
            NumericField::create('Expiration', _t('FeedReader.EXPIRATION'))
                ->setDescription(_t('FeedReader.EXPIRATION_COMMENT'))
        ]);
        return $fields;
    }

    public function getService()
    {
        if (! $this->service) {
            $this->service = FeedReaderService::create($this->FeedUrl, $this->Expiration);
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
