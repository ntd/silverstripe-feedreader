<div class="container">
	<div class="row">
		<main class="col-md-9 col-sm-12" id="ss-content"><% if $Title %>
			<div class="page-header"><h1>$Title</h1></div><% end_if %>
			$Content<% loop $Items %>
			<section class="skip">
				<a id="TOC-$Seq" class="anchor"></a>
				<div class="label label-default pull-left">$Date.Date</div>
				<div class="col-md-offset-1">
					<h4><a href="$Link">$Title</a></h4>
					$Content
				</div>
			</section><% end_loop %>
		</main>
		<div class="col-md-3 hidden-sm hidden-print">
			<aside id="ss-toc" role="complementary">
				<ul class="nav nav-pills nav-stacked">
					<li id="ss-toc-header" class="text-muted"><%t Silverstrap.TOC %></li><% loop $Items %>
					<li><a href="#TOC-$Seq" data-target="#TOC-$Seq">$Title</a></li><% end_loop %>
				</ul>
				<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script><script type="text/javascript" src="/themes/silverstrap/js/bootstrap.min.js?m=1392914981"></script><script type="text/javascript" src="/themes/silverstrap/js/jquery.colorbox-min.js?m=1371471961"></script><script type="text/javascript">
					$(document).ready(function() {
						$('body').scrollspy({ target: '#ss-toc' });
						$('#ss-toc').affix({ offset: { top: 72 } });
					});
				</script>
			</aside>
		</div>
	</div>
</div>
