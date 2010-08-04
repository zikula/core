<div id="theme_navigation_bar">
    <ul class="z-clearfix">
        <li {if $pagetype eq 'home'}class="selected"{/if}><a href="{homepage}">{gt text='Home'}</a></li>

        {modavailable modname=$navmenuitem1 assign='available1'}
        {if $navmenuitem1 && $available1}
        <li {if $pagetype neq 'home' && $module eq $navmenuitem1}class="selected"{/if}>
            <a href="{modurl modname=$navmenuitem1}">{modgetinfo modname=$navmenuitem1 info='displayname'}</a>
        </li>
        {/if}

        {modavailable modname=$navmenuitem2 assign='available2'}
        {if $navmenuitem2 && $available2}
        <li {if $pagetype neq 'home' && $module eq $navmenuitem2}class="selected"{/if}>
            <a href="{modurl modname=$navmenuitem2}">{modgetinfo modname=$navmenuitem2 info='displayname'}</a>
        </li>
        {/if}

        {modavailable modname=$navmenuitem3 assign='available3'}
        {if $navmenuitem3 && $available3}
        <li {if $pagetype neq 'home' && $module eq $navmenuitem3}class="selected"{/if}>
            <a href="{modurl modname=$navmenuitem3}">{modgetinfo modname=$navmenuitem3 info='displayname'}</a>
        </li>
        {/if}

        {modavailable modname=$navmenuitem4 assign='available4'}
        {if $navmenuitem4 && $available4}
        <li {if $pagetype neq 'home' && $module eq $navmenuitem4}class="selected"{/if}>
            <a href="{modurl modname=$navmenuitem4}">{modgetinfo modname=$navmenuitem4 info='displayname'}</a>
        </li>
        {/if}

        {checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}
        <li><a href="{modurl modname=Admin type=admin func=adminpanel}">{gt text='Administration'}</a></li>
        {/checkpermissionblock}
    </ul>
</div>
