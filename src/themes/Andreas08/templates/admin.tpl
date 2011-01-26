{include file='includes/header.tpl'}

<div id="theme_navigation_bar">
    <ul class="z-clearfix">
        <li><a href="{homepage}">{gt text='Home'}</a></li>
        <li><a href="{modurl modname=Settings type=admin}">{gt text="Settings"}</a></li>
        <li><a href="{modurl modname=Extensions type=admin}">{gt text="Extensions manager"}</a></li>
        <li><a href="{modurl modname=Blocks type=admin}">{gt text="Blocks manager"}</a></li>
        <li><a href="{modurl modname=Users type=admin}">{gt text="User administration"}</a></li>
        <li><a href="{modurl modname=Groups type=admin}">{gt text="Groups manager"}</a></li>
        <li><a href="{modurl modname=Permissions type=admin}">{gt text="Permission rules manager"}</a></li>
        <li><a href="{modurl modname=Theme type=admin}">{gt text="Themes manager"}</a></li>
        <li><a href="{modurl modname=Categories type=admin}">{gt text="Categories"}</a></li>
    </ul>
</div>

{include file="body/$admin.tpl"}
{include file='includes/footer.tpl'}
