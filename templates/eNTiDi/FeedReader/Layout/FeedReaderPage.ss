<div class="container">
  <div class="row">
    <main class="col-md-9 col-sm-12" id="ss-content"><% if $Title %>
    <div class="page-header"><h1>$Title.XML</h1></div><% end_if %>
    $Content.RAW<% loop $Items %>
    <section class="skip">
      <a id="TOC-$Pos" class="anchor"></a>
      <div class="label label-default pull-left">$Date.Date</div>
      <div class="col-md-offset-1">
        <h4><a href="$Link">$Title.XML</a></h4>
        $Content.RAW
      </div>
    </section><% end_loop %>
    </main>
    <div class="col-md-3 hidden-sm hidden-print">
      <aside id="ss-toc" role="complementary">
        <ul class="nav nav-pills nav-stacked">
          <% include AutotocHeader %><% loop $Items %>
          <li><a href="#TOC-$Pos" data-target="#TOC-$Pos">$Title.XML</a></li><% end_loop %>
        </ul>
      </aside>
    </div>
  </div>
</div>
