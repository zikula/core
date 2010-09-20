<div id="sidebar">
			<div class="sidebar-content">
				<a id="close_sidebar" class="btn ui-state-default full-link ui-corner-all" href="#drill">
					<span class="ui-icon ui-icon-circle-arrow-e" style="margin-top:3px;">&nbsp;</span>
					{gt text='Close Sidebar'}
				</a>
				<a id="open_sidebar" class="btn tooltip ui-state-default full-link icon-only ui-corner-all" title="{gt text='Open Sidebar'}" href="#"><span class="ui-icon ui-icon-circle-arrow-w im-top">&nbsp;</span></a>
				<div class="hide_sidebar">
				<div class="side_sort" >
				  
					<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header ui-corner-all">
						{gt text='Global Links'}
						<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
						<div class="portlet-content">
							<a class="btn ui-state-default full-link ui-corner-all" href="{modurl modname="Settings" type="admin"}">
								<span class="z-icon-es-config" style="margin-top:2px;">&nbsp;</span>
								{gt text='Settings'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" href="{modurl modname="users" type="admin"}">
								<span class="z-icon-es-user" style="margin-top:2px;">&nbsp;</span>
								{gt text='Users'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" href="{modurl modname="permissions" type="admin"}">
								<span class="z-icon-es-locked" style="margin-top:2px;">&nbsp;</span>
								{gt text='Permissions'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" href="{modurl modname="securitycenter" type="admin"}">
								<span class="z-icon-es-locked" style="margin-top:2px;">&nbsp;</span>
								{gt text='Security'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" href="{modurl modname="theme" type="admin"}">
								<span class="z-icon-es-package" style="margin-top:2px;">&nbsp;</span>
								{gt text='Theme'}
							</a>
										 
					</div></div>
										
					<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header ui-corner-all">
						{gt text='Users'}
						<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
						<div class="portlet-content">
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="users" type="admin" func="view"}">
								<span class="z-icon-es-list">&nbsp;</span>
								{gt text='All Users'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="users" type="admin" func="newUser"}">
								<span class="z-icon-es-new">&nbsp;</span>
								{gt text='create new User'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="users" type="admin" func="import"}">
								<span class="z-icon-es-import">&nbsp;</span>
								{gt text='import Users'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="users" type="admin" func="exporter"}">
								<span class="z-icon-es-export">&nbsp;</span>
								{gt text='export Users'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="users" type="admin" func="search"}">
								<span class="z-icon-es-mail">&nbsp;</span>
								{gt text='E-mail Users'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="users" type="admin" func="modifyConfig"}">
								<span class="z-icon-es-config">&nbsp;</span>
								{gt text='User Settings'}
							</a>
						</div>
					</div>
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header ui-corner-all">
						{gt text='Modules'}
						<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
						
						<div class="portlet-content">
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="modules" type="admin" func="view"}">
								<span class="z-icon-es-list">&nbsp;</span>
								{gt text='Modules List'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="modules" type="admin" func="pluginList"}">
								<span class="z-icon-es-cubes">&nbsp;</span>
								{gt text='Plugin List'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="modules" type="admin" func="hooks"}">
								<span class="z-icon-es-package">&nbsp;</span>
								{gt text='Hooks'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="modules" type="admin" func="systemplugins=1"}">
								<span class="z-icon-es-cubes">&nbsp;</span>
								{gt text='System Plugins'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="modules" type="admin" func="modifyconfig"}">
								<span class="z-icon-es-config">&nbsp;</span>
								{gt text='Settings'}
							</a>
						</div>
						</div>
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header ui-corner-all">
						{gt text='Layout'}
						<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
						<div class="portlet-content">
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="blocks" type="admin" func="view"}">
								<span class="z-icon-es-list">&nbsp;</span>
								{gt text='Blocks list'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="themes" type="admin" func="view"}">
								<span class="z-icon-es-list">&nbsp;</span>
								{gt text='Themes list'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="blocks" type="admin" func="newblock"}">
								<span class="z-icon-es-new">&nbsp;</span>
								{gt text='new block'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="blocks" type="admin" func="newposition"}">
								<span class="z-icon-es-new">&nbsp;</span>
								{gt text='new position'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="modules" type="admin" func="modifyconfig"}">
								<span class="z-icon-es-config">&nbsp;</span>
								{gt text='Block Settings'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="theme" type="admin" func="modifyconfig"}">
								<span class="z-icon-es-config">&nbsp;</span>
								{gt text='Theme Settings'}
							</a>
						</div>
						</div>
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header ui-corner-all">
						{gt text='Tools'}
						<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
						<div class="portlet-content">
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="search" type="admin"}">
								<span class="z-icon-es-config">&nbsp;</span>
								{gt text='Search'}
							</a>
						</div>
						</div>
						<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header ui-corner-all">
						{gt text='Categories'}
						<span class="ui-icon ui-icon-circle-arrow-s">&nbsp;</span></div>
						<div class="portlet-content">
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="categories" type="admin" func="view"}">
								<span class="z-icon-es-list">&nbsp;</span>
								{gt text='list'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="categories" type="admin" func="newcat"}">
								<span class="z-icon-es-new">&nbsp;</span>
								{gt text='new category'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="categories" type="admin" func="editregistry"}">
								<span class="z-icon-es-cubes">&nbsp;</span>
								{gt text='Registry'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="categories" type="admin" func="config"}">
								<span class="z-icon-es-regenerate">&nbsp;</span>
								{gt text='rebuild paths'}
							</a>
							<a class="btn ui-state-default full-link ui-corner-all" style="font-size:12px;"	href="{modurl modname="categories" type="admin" func="preferences"}">
								<span class="z-icon-es-config">&nbsp;</span>
								{gt text='Settings'}
							</a>
						</div>
						</div>
				</div>
					
				
				</div>
			</div>

</div>
		