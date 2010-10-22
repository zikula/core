<div id="topline" class="z-clearfix">
    <div class="z-floatleft">{userwelcome|ucwords}</div>
    <div class="z-floatright">{blockposition name=search}</div>
</div>
<div id="header" class="z-clearfix">
    <h1><a href="{homepage}">{$modvars.ZConfig.sitename}</a></h1>
    <div id="navi" class="z-clearer">
        <ul id="nav">
            <li class="page_item">
                <a href="{homepage}"><span>{gt text="Home"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Settings type=admin}"><span>{gt text="Settings"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Modules type=admin}"><span>{gt text="Modules manager"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Blocks type=admin}"><span>{gt text="Blocks manager"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Users type=admin}"><span>{gt text="User administration"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Groups type=admin}"><span>{gt text="Groups manager"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Permissions type=admin}"><span>{gt text="Permission rules manager"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Theme type=admin}"><span>{gt text="Themes manager"}</span></a>
            </li>
            <li class="page_item">
                <a href="{modurl modname=Categories type=admin}"><span>{gt text="Categories"}</span></a>
            </li>
        </ul>
    </div>
</div>
