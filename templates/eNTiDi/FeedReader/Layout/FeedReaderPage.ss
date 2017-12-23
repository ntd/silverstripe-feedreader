<div class="container">
  <div class="row">
    <main class="col-xs-12 col-md-9" id="ss-content"><% if $Title %>
      <div class="page-header"><h1>$Title.XML</h1></div><% end_if %>
      $Content.RAW<% loop $Items %>
      <section class="skip">
        <a id="TOC-$Pos" class="anchor"></a>
        <div class="label label-default pull-right">$Date.Date</div>
        <div>
          <h4><a href="$Link">$Title.XML</a></h4>
          $Content.RAW
        </div>
      </section><% end_loop %>
    </main>
    <div class="col-md-3 hidden-xs hidden-sm hidden-print">
      <aside id="ss-toc" role="complementary">
        <ul class="nav nav-pills nav-stacked">
          <% include AutotocHeader %><% loop $Items %>
          <li><a href="#TOC-$Pos" data-target="#TOC-$Pos">$Title.XML</a></li><% end_loop %>
        </ul>
      </aside>
    </div>
  </div>
</div>
