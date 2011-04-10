{include file='includes/header.tpl'}

<div id="theme_navigation_bar">
    <ul class="z-clearfix">
        <li><a href="{homepage}">{gt text='Home'}</a></li>
        <li><a href="{modurl modname='Settings' type='admin' func='main'}">{gt text="Settings"}</a></li>
        <li><a href="{modurl modname='Extensions' type='admin' func='main'}">{gt text="Extensions manager"}</a></li>
        <li><a href="{modurl modname='Blocks' type='admin' func='main'}">{gt text="Blocks manager"}</a></li>
        <li><a href="{modurl modname='Users' type='admin' func='main'}">{gt text="User administration"}</a></li>
        <li><a href="{modurl modname='Groups' type='admin' func='main'}">{gt text="Groups manager"}</a></li>
        <li><a href="{modurl modname='Permissions' type='admin' func='main'}">{gt text="Permission rules manager"}</a></li>
        <li><a href="{modurl modname='Theme' type='admin' func='main'}">{gt text="Themes manager"}</a></li>
    </ul>
</div>

{include file="body/$admin.tpl"}
{include file='includes/footer.tpl'}
