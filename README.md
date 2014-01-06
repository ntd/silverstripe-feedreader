silverstripe-feedreader
=======================

The [silverstripe-feedreader](http://silverstripe.entidi.com/) module
implements a new page type (*FeedReaderPage*) that can access the data
contained in an external RSS 2.0 or ATOM 1.0 feed. The feed format is
automatically deduced from its content, that is if the `//channel/item`
[XPath](http://www.w3.org/TR/xpath/) expression resolves to a non-emtpy
list it is considered an RSS2 feed, otherwise it is considered ATOM1,
and the `//feed/entry` expression will be used instead.

Installation
------------

1. Install the module in your base path (the directory with `cms` and
   `framework` in it) in one of the following way:
    1. Download the tarball and extract it:<br>
        <pre><code>wget http://github.com/ntd/silverstripe-feedreader/archive/dev.zip
        unzip dev.zip</code></pre>
    2. Clone the repository:<br>
        <pre><code>git clone https://github.com/ntd/silverstripe-feedreader.git</code></pre>
    3. Install with composer:<br>
        <pre><code>composer require entidi/silverstripe-feedreader dev-master</code></pre>

2. Make sure the folder after being extracted is named `feedreader`
3. Run in your browser `/dev/build` to rebuild the database.
4. You should see the new _Feed reader page_ type in the CMS.


How to use
----------

The default template (`templates/Layout/FeedReaderPage.ss`) is
compatible with the [silverstrap](http://dev.entidi.com/p/silverstrap/)
theme but can be easily overriden by redefining the `FeedReaderPage.ss`
file in your own theme. Check the original one for an example on how to
use this module from a template.

To provide access to the latest news, you can define a function similar
to the following one in any of your controllers:

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

For bug report or feature requests, go to the dedicated [development
tracker](http://dev.entidi.com/p/silverstripe-feedreader/).
