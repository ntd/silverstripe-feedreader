silverstripe-feedreader
=======================
[![License](https://poser.pugx.org/entidi/feedreader/license)](https://packagist.org/packages/entidi/feedreader)
[![Build Status](https://travis-ci.org/ntd/silverstripe-feedreader.svg?branch=master)](https://travis-ci.org/ntd/silverstripe-feedreader)
[![Code Quality](https://scrutinizer-ci.com/g/ntd/silverstripe-feedreader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ntd/silverstripe-feedreader/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/entidi/feedreader/v/stable)](https://packagist.org/packages/entidi/feedreader)

The [silverstripe-feedreader](http://silverstripe.entidi.com/) module
implements a new page type (*FeedReaderPage*) that can access the data
contained in an external RSS 2.0 or ATOM 1.0 feed. The feed format is
automatically deduced from its content, that is if the `//channel/item`
[XPath](http://www.w3.org/TR/xpath/) expression resolves to a non-emtpy
list it is considered an RSS2 feed, otherwise it is considered ATOM1,
and the `//feed/entry` expression will be used instead.

Installation
------------

With composer:

    composer require entidi/silverstripe-feedreader

Without composer, download [the tarball](https://github.com/ntd/silverstripe-feedreader/releases)
and unpack it under the base directory.

Usage
-----

The default template (`templates/Layout/FeedReaderPage.ss`) is
compatible with the [silverstrap](http://dev.entidi.com/p/silverstrap/)
theme but can be easily overriden by redefining the `FeedReaderPage.ss`
file in your own theme. Check the original file as an example.

To provide access to the latest news, you can define a function similar
to the following in your controller:

    public function LatestNews() {
        $news = DataObject::get_one('FeedReaderPage');
        return $news ? $news->Items(1)->first() : null;
    }

Then you can enhance that page with a template snippet similar to the
following one:

    <% with $LatestNews %>
    <h2>Latest news</h2>
    <section>
        <p>$Date.Date: $Summary</p>
        <a href="$Link">More ...</a>
    </section>
    <% end_with %>

Support
-------

This project has been developed by [ntd](mailto:ntd@entidi.it). Its
[home page](http://silverstripe.entidi.com/) is shared by other
[SilverStripe](http://www.silverstripe.org/) modules and themes.

To check out the code, report issues or propose enhancements, go to the
[dedicated tracker](http://dev.entidi.com/p/silverstripe-feedreader).
Alternatively, you can do the same things by leveraging the official
[github repository](https://github.com/ntd/silverstripe-feedreader).
