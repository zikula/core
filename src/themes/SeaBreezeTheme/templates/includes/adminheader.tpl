<div id="topline" class="z-clearfix">
    <div class="z-floatleft">{userwelcome|ucwords}</div>
    <div class="z-floatright">{blockposition name=search}</div>
</div>
<div id="header" class="z-clearfix">
    <h1><a href="{homepage}" title="{$modvars.ZConfig.slogan}">{$modvars.ZConfig.sitename}</a></h1>
    <div id="navi" class="z-clearer">
        <ul id="nav">
            <li class="page_item">
                <a href="{homepage}"><span>{gt text="Home"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='SettingsModule' type='admin' func='index'}"><span>{gt text="Settings"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ExtensionsModule' type='admin' func='index'}"><span>{gt text="Extensions"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaBlocksModule' type='admin' func='index'}"><span>{gt text="Blocks"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='UsersModule' type='admin' func='index'}"><span>{gt text="Users"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='GroupsModule' type='admin' func='index'}"><span>{gt text="Groups"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='PermissionsModule' type=admin func='index'}"><span>{gt text="Permission rules"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ThemeModule' type='admin' func='index'}"><span>{gt text="Themes"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Categories' type='admin' func='index'}"><span>{gt text="Categories"}</span></a>
            </li>
        </ul>
    </div>
</div>
