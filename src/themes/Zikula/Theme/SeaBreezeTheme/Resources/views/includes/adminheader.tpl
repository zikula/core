<div id="topline" class="clearfix">
    <div class="pull-left">{userwelcome|ucwords}</div>
    <div class="pull-right">{blockposition name=search}</div>
</div>
<div id="header" class="clearfix">
    <h1><a href="{homepage}" title="{$modvars.ZConfig.slogan}">{$modvars.ZConfig.sitename}</a></h1>
    <div id="navi" class="z-clearer">
        <ul id="nav">
            <li class="page_item">
                <a href="{homepage}"><span>{gt text="Home"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaSettingsModule' type='admin' func='index'}"><span>{gt text="Settings"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaExtensionsModule' type='admin' func='index'}"><span>{gt text="Extensions"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaBlocksModule' type='admin' func='index'}"><span>{gt text="Blocks"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaUsersModule' type='admin' func='index'}"><span>{gt text="Users"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaGroupsModule' type='admin' func='index'}"><span>{gt text="Groups"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaPermissionsModule' type=admin func='index'}"><span>{gt text="Permission rules"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaThemeModule' type='admin' func='index'}"><span>{gt text="Themes"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='ZikulaCategoriesModule' type='admin' func='index'}"><span>{gt text="Categories"}</span></a>
            </li>
        </ul>
    </div>
</div>
