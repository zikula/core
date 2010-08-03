<div class="inner-page-title">
<h2>{gt text='Project News'}</h2>
<span>{gt text='Stay up to date!'}</span>
</div>
<div class="two-column">
					<div class="column">
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
							<div class="portlet-header ui-widget-header">{gt text='Newest Extensions'}<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
							<div class="portlet-content">
								{php}
	                                    $src = 'http://community.zikula.org/index.php?module=Extensions&func=view&ot=component&comptype=0&sort=lu_date&sdir=desc&tpl=rss&raw=1&catms=0&catma=0&catmf=0&cattt=0&catrt=0&catcl=0';
	                                    $chan = 'y';
	                                    $num = 5;
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
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
							<div class="portlet-header ui-widget-header">{gt text='Latest Project News'}<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
							<div class="portlet-content">
							{php}
	                                    $src = 'http://community.zikula.org/index.php?module=News&theme=RSS';
	                                    $chan = 'y';
	                                    $num = 5;
	                                    $desc = 0;
	                                    $html = 'n';
	                                    $tz = 'feed';
	                                    $utf = 'y';
	                                    $date = 'n';
	                                    $targ = 'y';
	                                    include 'themes/Admin/templates/rss/feed2php.inc';
                                    {/php}
							</div>
						</div>
					</div>
					
					<div class="column column-right">
					
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
							<div class="portlet-header ui-widget-header">{gt text='Latest Blog Posts'}<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
							<div class="portlet-content">
								{php}
	                                    $src = 'http://blog.zikula.org/index.php?module=CMS&tid=1&template=rss&rss=true';
	                                    $chan = 'y';
	                                    $num = 5;
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
						
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
							<div class="portlet-header ui-widget-header">{gt text='Last Wiki Changes'}<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
							<div class="portlet-content">
									{php}
	                                    $src = 'http://community.zikula.org/module-Wiki-recentchangesxml-theme-rss.htm';
	                                    $chan = 'y';
	                                    $num = 5;
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
					</div>
</div>
<div class="clear"></div>				