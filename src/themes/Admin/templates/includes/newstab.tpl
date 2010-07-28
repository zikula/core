<br />
<div class="inner-page-title">
<h2>{gt text='Project News'}</h2>
<span>{gt text='Stay up to date!'}</span>
</div>
<div id="main"><!-- #main content area -->
			<div id="content"><!-- #content -->
						<div class="rss-holder">
						<div class="zk-accordion">						
						<ul>
							<li>
						        <h3>Project News</h3>
								<div>
								<div class="portlet-content">
									{php}
	                                    $src = 'http://community.zikula.org/index.php?module=News&theme=RSS';
	                                    $chan = 'y';
	                                    $num = 4;
	                                    $desc = 0;
	                                    $html = 'y';
	                                    $tz = 'feed';
	                                    $utf = 'y';
	                                    $date = 'n';
	                                    $targ = 'y';
	                                    include 'themes/Admin/templates/rss/feed2php.inc';
                                    {/php}
								</div>
								</div>
							</li>
							<li>

						        <h3>Community Blog</h3>
								<div>
								<div class="portlet-content">
									{php}
	                                    $src = 'http://blog.zikula.org/index.php?module=CMS&tid=1&template=rss&rss=true';
	                                    $chan = 'y';
	                                    $num = 4;
	                                    $desc = 0;
	                                    $html = 'y';
	                                    $tz = 'feed';
	                                    $utf = 'y';
	                                    $date = 'n';
	                                    $targ = 'y';
	                                    include 'themes/Admin/templates/rss/feed2php.inc';
                                    {/php}
								</div>
								</div>

							</li>
							<li>
						        <h3>Extension News</h3>
								<div>
								<div class="portlet-content">
									{php}
	                                    $src = 'http://community.zikula.org/index.php?module=Extensions&func=view&ot=component&comptype=0&sort=lu_date&sdir=desc&tpl=rss&raw=1&catms=0&catma=0&catmf=0&cattt=0&catrt=0&catcl=0';
	                                    $chan = 'y';
	                                    $num = 4;
	                                    $desc = 0;
	                                    $html = 'y';
	                                    $tz = 'feed';
	                                    $utf = 'y';
	                                    $date = 'n';
	                                    $targ = 'y';
	                                    include 'themes/Admin/templates/rss/feed2php.inc';
                                    {/php}
								</div>
								</div>
							</li>
							<li>
						        <h3>WIKI Updates</h3>
								<div>
								<div class="portlet-content">
									{php}
	                                    $src = 'http://community.zikula.org/module-Wiki-recentchangesxml-theme-rss.htm';
	                                    $chan = 'y';
	                                    $num = 4;
	                                    $desc = 0;
	                                    $html = 'y';
	                                    $tz = 'feed';
	                                    $utf = 'y';
	                                    $date = 'n';
	                                    $targ = 'y';
	                                    include 'themes/Admin/templates/rss/feed2php.inc';
                                    {/php}
								</div>
								</div>
							</li>
						</ul>
						</div>
						</div>
			
			</div><!-- end of #content -->

	</div><!-- end of #main content -->
