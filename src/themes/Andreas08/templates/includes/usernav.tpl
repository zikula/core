<div id="theme_navigation_bar">
    <ul class="z-clearfix">
        <li class="selected"><a href="{homepage}">{gt text='Home'}</a></li>

        {if $navmenuitem1}
        <li {if $module eq $navmenuitem1}class="selected"{/if}><a href="{modurl modname=$navmenuitem1}">{modgetinfo modname=$navmenuitem1 info='displayname'}</a></li>
        {/if}
        {if $navmenuitem2}
        <li {if $module eq $navmenuitem2}class="selected"{/if}><a href="{modurl modname=$navmenuitem2}">{modgetinfo modname=$navmenuitem2 info='displayname'}</a></li>
        {/if}
        {if $navmenuitem3}
        <li {if $module eq $navmenuitem3}class="selected"{/if}><a href="{modurl modname=$navmenuitem3}">{modgetinfo modname=$navmenuitem3 info='displayname'}</a></li>
        {/if}
        {if $navmenuitem4}
        <li {if $module eq $navmenuitem4}class="selected"{/if}><a href="{modurl modname=$navmenuitem4}">{modgetinfo modname=$navmenuitem4 info='displayname'}</a></li>
        {/if}

        {checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}
        <li><a href="{modurl modname=Admin type=admin func=adminpanel}">{gt text='Administration'}</a></li>
        {/checkpermissionblock}
    </ul>
</div>