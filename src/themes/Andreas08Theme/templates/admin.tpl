{include file='includes/header.tpl'}

<div id="theme_navigation_bar">
    <ul class="z-clearfix">
        <li><a href="{homepage}">{gt text='Home'}</a></li>
        <li><a href="{modurl modname='SettingsModule' type='admin' func='index'}">{gt text="Settings"}</a></li>
        <li><a href="{modurl modname='ExtensionsModule' type='admin' func='index'}">{gt text="Extensions"}</a></li>
        <li><a href="{modurl modname='BlocksModule' type='admin' func='index'}">{gt text="Blocks"}</a></li>
        <li><a href="{modurl modname='UsersModule' type='admin' func='index'}">{gt text="Users"}</a></li>
        <li><a href="{modurl modname='GroupsModule' type='admin' func='index'}">{gt text="Groups"}</a></li>
        <li><a href="{modurl modname='PermissionsModule' type='admin' func='index'}">{gt text="Permission rules"}</a></li>
        <li><a href="{modurl modname='ThemeModule' type='admin' func='index'}">{gt text="Themes"}</a></li>
    </ul>
</div>

{include file="body/$admin.tpl"}
{include file='includes/footer.tpl'}
