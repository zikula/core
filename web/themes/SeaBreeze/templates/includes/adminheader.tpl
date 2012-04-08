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
                <a href="{modurl modname='Settings' type='admin' func='main'}"><span>{gt text="Settings"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Extensions' type='admin' func='main'}"><span>{gt text="Extensions"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Blocks' type='admin' func='main'}"><span>{gt text="Blocks"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Users' type='admin' func='main'}"><span>{gt text="Users"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Groups' type='admin' func='main'}"><span>{gt text="Groups"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Permissions' type=admin func='main'}"><span>{gt text="Permission rules"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Theme' type='admin' func='main'}"><span>{gt text="Themes"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname='Categories' type='admin' func='main'}"><span>{gt text="Categories"}</span></a>
            </li>
        </ul>
    </div>
</div>
