<h1><a href="index.php">{$modvars.ZConfig.sitename}</a></h1>
            <div class="top-right-navi">
                <strong style="font-weight:bold;">{userwelcome|ucwords}</strong>
                <span>|</span>
                <a href="index.php" target="_blank" >{gt text='Visit your site!'}</a>
                <span>|</span>
                <a href="#defwindow_content_modal-1" id="defwindowmodal-1" >{gt text='My Account'}</a>
                <span>|</span>
                <a id="defwindowmodal-0" href="#defwindow_content_modal-0" >{gt text='Settings'}</a>
                <span>|</span>
                <a title="{gt text='Log out'}" href="{modurl modname=users type=user func=logout}" >{gt text='Log out'}</a>
            </div>
<div id="defwindow_content_modal-0" style="display: none;">
<h2>{gt text='Choose your Layout'}</h2>
<div id="switcher">
</div>
</div>
<div id="defwindow_content_modal-1" style="display: none;">
{modfunc modname="users" type="user" func="main"}
</div>