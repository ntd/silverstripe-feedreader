<div class="container">
	<div class="row">
		<aside class="span3 toc">
			<ul class="nav nav-list affix">
				<li class="nav-header"><%t Silverstrap.TOC %></li><% loop $Items %>
				<li><a href="#TOC-$Seq" data-target="#TOC-$Seq">$Title</a></li><% end_loop %>
			</ul>
		</aside>
		<div class="span9">
			<main id="content" role="main"><% if $Title %>
				<div class="page-header"><h1>$Title</h1></div><% end_if %>
				$Content<% loop $Items %>
				<section class="skip">
					<a id="TOC-$Seq" class="anchor"></a>
					<div class="label pull-left">$Date.Date</div>
					<div class="offset1">
						<h4><a href="$Link">$Title</a></h4>
						$Content
					</div>
				</section><% end_loop %>
			</main>
		</div>
	</div>
</div>
