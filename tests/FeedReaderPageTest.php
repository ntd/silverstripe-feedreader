<?php

namespace eNTiDi\FeedReader\Tests;

use eNTiDi\FeedReader\FeedReaderPage;
use SilverStripe\Dev\SapphireTest;

class FeedReaderPageTest extends SapphireTest
{
    protected static $fixture_file = 'FeedReader.yml';

    public function testService()
    {
        $page = $this->objFromFixture(FeedReaderPage::class, 'TestPage');

        $service = $page->getService();
        $this->assertTrue($service instanceof \eNTiDi\FeedReader\FeedReaderService);
    }
}
