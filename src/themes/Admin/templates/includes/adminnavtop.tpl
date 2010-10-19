<h1><a href="index.php">{sitename}</a></h1>
			<div id="top-right-navi">
				<strong style="font-weight:bold;">{userwelcome|ucwords}</strong>
				<span>|</span>
                <a href="index.php" target="_blank">{gt text='Visit your site!'}</a>
				<span>|</span>
				<a href="#defwindow_content_modal-1" id="defwindowmodal-1">{gt text='My Account'}</a>
				<span>|</span>
				<a href="{modurl modname=users type=user func=logout}">{gt text='Log out'}</a>
			</div>

<!-- <a id="defwindowmodal-0" title="{gt text='Settings'}" class="z-button ui-corner-all" href="#defwindow_content_modal-0">
	<span class="z-icon-es-edit" style="margin-top:2px;">&nbsp;</span>
		{gt text='Settings'}
</a> -->

<!-- <div id="defwindow_content_modal-0" style="display: none;">
<h2>{gt text='Choose your Layout'}</h2>
<div id="switcher">
</div>
</div>	 -->
<div id="defwindow_content_modal-1" style="display: none;">
{modfunc modname="users" type="user" func="main"}
</div>	