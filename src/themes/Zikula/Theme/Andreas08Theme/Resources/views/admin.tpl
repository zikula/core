{include file='includes/header.tpl'}

<div id="theme_navigation_bar">
    <ul class="clearfix">
        <li><a href="{homepage}">{gt text='Home'}</a></li>
        <li><a href="{modurl modname='ZikulaSettingsModule' type='admin' func='index'}">{gt text="Settings"}</a></li>
        <li><a href="{modurl modname='ZikulaExtensionsModule' type='admin' func='index'}">{gt text="Extensions"}</a></li>
        <li><a href="{modurl modname='ZikulaBlocksModule' type='admin' func='index'}">{gt text="Blocks"}</a></li>
        <li><a href="{modurl modname='ZikulaUsersModule' type='admin' func='index'}">{gt text="Users"}</a></li>
        <li><a href="{modurl modname='ZikulaGroupsModule' type='admin' func='index'}">{gt text="Groups"}</a></li>
        <li><a href="{modurl modname='ZikulaPermissionsModule' type='admin' func='index'}">{gt text="Permission rules"}</a></li>
        <li><a href="{modurl modname='ZikulaThemeModule' type='admin' func='index'}">{gt text="Themes"}</a></li>
    </ul>
</div>

{include file="body/$admin.tpl"}
{include file='includes/footer.tpl'}
