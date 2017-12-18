<?php

namespace eNTiDi\FeedReader\Tests;

use eNTiDi\FeedReader;
use SilverStripe\Dev\SapphireTest;

class FeedReaderServiceTest extends SapphireTest
{
    protected $usesDatabase = false;

    /**
     * Check the behavior of the ATOM 1.0 parser.
     */
    public function testAtomParser()
    {
        $atom_uri = 'file://' . __DIR__ . '/sample.atom';
        $service  = new FeedReaderService($atom_uri);
        $items    = $service->getItems();

        $this->assertEquals(count($items), 1);

        $item = $items[0];
        $this->assertEquals($item->Id, 'urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a');
        $this->assertEquals($item->Link, 'http://example.org/2003/12/13/atom03');
        $this->assertEquals($item->Date, '2003-12-13T18:30:02Z');
        $this->assertEquals($item->Title, 'Atom-Powered Robots Run Amok');
        $this->assertEquals($item->Summary, 'Some text.');
        $this->assertTrue(strlen($item->Content) > 20);
    }
}
