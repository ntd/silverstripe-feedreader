<?php

namespace eNTiDi\FeedReader\Tests;

use eNTiDi\FeedReader\FeedReaderService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use SilverStripe\Dev\SapphireTest;

class FeedReaderServiceTest extends SapphireTest
{
    protected $usesDatabase = false;

    /**
     * Check the behavior of the ATOM 1.0 parser.
     */
    public function testAtomParser()
    {
        // Use a dummy (but valid) URI for the requests
        $url = 'http://localhost/';

        // Create a mock and queue two responses
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/sample1.atom')),
            new Response(200, [], file_get_contents(__DIR__ . '/sample2.atom')),
        ]);
        $handler = HandlerStack::create($mock);

        $service = new FeedReaderService($url, 3600, [ 'handler' => $handler ]);
        $service->clearCache();

        // First read
        $items = $service->getItems();

        $this->assertEquals(1, $items->count());

        $item = $items[0];
        $this->assertEquals('urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a', $item->Id);
        $this->assertEquals('http://example.org/2003/12/13/atom03', $item->Link);
        $this->assertEquals('2003-12-13 18:30:02', (string) $item->Date);
        $this->assertEquals('Atom-Powered Robots Run Amok', $item->Title);
        $this->assertEquals('Some text.', $item->Summary);
        $this->assertTrue(strlen($item->Content) > 20);

        // Second read: must still return the first one (cached)
        $items = $service->getItems();
        $this->assertEquals(1, $items->count());

        // Third read: must read the sample2.atom mocked response
        $service->clearCache();
        $items = $service->getItems();

        $this->assertEquals(3, $items->count());

        $item = $items[0];
        $this->assertEquals('http://example.org/1', $item->Id);
        $this->assertEquals('', $item->Link);
        $this->assertEquals('2017-12-19 11:22:33', (string) $item->Date);
        $this->assertEquals('Entry 1', $item->Title);
        $this->assertEquals('', $item->Summary);
        $this->assertEquals('', $item->Content);

        $item = $items[1];
        $this->assertEquals('http://example.org/2', $item->Id);
        $this->assertEquals('http://example.org/link/2', $item->Link);
        $this->assertEquals('2017-12-19 22:33:44', (string) $item->Date);
        $this->assertEquals('Entry 2', $item->Title);
        $this->assertEquals('Summary 2', $item->Summary);
        $this->assertTrue(strlen($item->Content) > 5);

        $item = $items[2];
        $this->assertEquals('http://example.org/3', $item->Id);
        $this->assertEquals('http://example.org/link/3', $item->Link);
        $this->assertEquals('2017-12-19 03:44:55', (string) $item->Date);
        $this->assertEquals('Entry 3', $item->Title);
        $this->assertEquals('Summary 3', $item->Summary);
        $this->assertEquals('Summary 3', $item->Content);

        // Forth read: must still return sample2.atom (cached)
        $items = $service->getItems();
        $this->assertEquals(3, $items->count());
    }

    /**
     * Check the behavior of the RSS 2.0 parser.
     */
    public function testRSSParser()
    {
        // Use a dummy (but valid) URI for the requests
        $url = 'http://localhost/';

        // Create a mock and queue two responses
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/sample1.rss')),
            new Response(200, [], file_get_contents(__DIR__ . '/sample2.rss')),
        ]);
        $handler = HandlerStack::create($mock);

        $service = new FeedReaderService($url, 3600, [ 'handler' => $handler ]);
        $service->clearCache();

        // First read
        $items = $service->getItems();

        $this->assertEquals(1, $items->count());

        $item = $items[0];
        $this->assertEquals('7bd204c6-1655-4c27-aeee-53f933c5395f', $item->Id);
        $this->assertEquals('http://www.example.com/blog/post/1', $item->Link);
        $this->assertEquals('2009-09-06 16:20:00', (string) $item->Date);
        $this->assertEquals('Example entry', $item->Title);
        $this->assertEquals('Here is some text containing an interesting description.', $item->Summary);
        $this->assertEquals('Here is some text containing an interesting description.', $item->Content);

        // Second read: must still return the first one (cached)
        $items = $service->getItems();
        $this->assertEquals(1, $items->count());

        // Third read: must read the sample2.rss mocked response
        $service->clearCache();
        $items = $service->getItems();

        $this->assertEquals(3, $items->count());

        $item = $items[0];
        $this->assertEquals('http://example.org/1', $item->Id);
        $this->assertEquals('', $item->Link);
        $this->assertEquals('2017-12-19 11:22:33', (string) $item->Date);
        $this->assertEquals('Entry 1', $item->Title);
        $this->assertEquals('', $item->Summary);
        $this->assertEquals('', $item->Content);

        $item = $items[1];
        $this->assertEquals('http://example.org/2', $item->Id);
        $this->assertEquals('http://example.org/link/2', $item->Link);
        $this->assertEquals('2017-12-19 22:33:44', (string) $item->Date);
        $this->assertEquals('Entry 2', $item->Title);
        $this->assertEquals('Content 2', $item->Summary);
        $this->assertEquals('Content 2', $item->Content);

        $item = $items[2];
        $this->assertEquals('http://example.org/3', $item->Id);
        $this->assertEquals('http://example.org/link/3', $item->Link);
        $this->assertEquals('2017-12-19 03:44:55', (string) $item->Date);
        $this->assertEquals('Entry 3', $item->Title);
        $this->assertEquals('Summary 3', $item->Summary);
        $this->assertEquals('Summary 3', $item->Content);

        // Forth read: must still return sample2.rss (cached)
        $items = $service->getItems();
        $this->assertEquals(3, $items->count());
    }

    /**
     * Check the summary length feature for RSS2 feeds.
     */
    public function testSummaryLen()
    {
        // Create a mock and queue the responses
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/sample1.rss')),
            new Response(200, [], file_get_contents(__DIR__ . '/sample1.rss')),
            new Response(200, [], file_get_contents(__DIR__ . '/sample1.rss')),
            new Response(200, [], file_get_contents(__DIR__ . '/sample1.rss')),
        ]);
        $handler = HandlerStack::create($mock);
        $service = new FeedReaderService('http://localhost/', 3600, [ 'handler' => $handler ]);
        $service->clearCache();

        // Check the default summary length
        $this->assertEquals(155, $service->getSummaryLen());
        $item = $service->getItems()[0];
        $this->assertEquals('Here is some text containing an interesting description.', $item->Summary);

        // Try the ellipsisation algorithm
        $service->setSummaryLen(50);
        $this->assertEquals(50, $service->getSummaryLen());
        $item = $service->getItems()[0];
        $this->assertEquals('Here is some text containing an interesting description.', $item->Summary);

        // Without clearing the cache, the old item values are retained
        $service->clearCache();
        $item = $service->getItems()[0];
        $this->assertEquals('Here is some text containing an interesting...', $item->Summary);

        $service->setSummaryLen(30);
        $this->assertEquals(30, $service->getSummaryLen());
        $service->clearCache();
        $item = $service->getItems()[0];
        $this->assertEquals('Here is some text...', $item->Summary);

        $service->setSummaryLen(31);
        $this->assertEquals(31, $service->getSummaryLen());
        $service->clearCache();
        $item = $service->getItems()[0];
        $this->assertEquals('Here is some text containing...', $item->Summary);
    }
}
