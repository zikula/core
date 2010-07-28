<div class="inner-page-title">
<br />
					<h2>{gt text='Dashboard'}</h2>
					<span>{gt text='Check out the newest Stuff!'}</span>
				</div>
				<div class="three-column sortable">
					<div class="three-col-mid">
						<div class="column col1">
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">Users<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
								12345
								</div>
							</div>
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">Settings<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
									<p>asasdfdf</p></div>
							</div>
						</div>
						
						<div class="column col2">
						
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">Pending Content<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
								<p>Articles</p>
								</div>
							</div>
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">Pending Content<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
								<p>Images</p>
								</div>
							</div>
						
						</div>
						
						<div class="column col3">
						
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">Forum<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
								Posts
								</div>
							</div>
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">News<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
								asdf
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="clear"></div>
				<div class="content-box">
					<div class="two-column">
						<div class="column">
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								
                <div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">{gt text='Extension News'}:<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
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
						</div>
						<div class="column column-right">
							<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
								<div class="portlet-header ui-widget-header ui-corner-all ui-title-hover">{gt text='Project News'}:<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
								<div class="portlet-content">
                                    {php}
	                                    $src = 'http://community.zikula.org/index.php?module=News&theme=RSS';
	                                    $chan = 'y';
	                                    $num = 4;
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
					</div>
					<div class="clear"></div>
					<div class="z-informationmsg ui-corner-all">
						<span>Stay tuned!</span>
						Visit community.zikula.org !
					</div>
				</div>
				<div class="clear"></div>			 
				