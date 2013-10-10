<ul data-role="listview">
    {foreach from=$menuitems item='item'}
    {if $item.name != '' && $item.url != ''}
    <li>
        <a href="{$item.url|safetext}" title="{$item.title}">
            {$item.name}
        </a>
    </li>
    {/if}
    {/foreach}
    <li><a href="{modurl modname='ZikulaThemeModule' type='User' func='disableMobileTheme'}">{gt text="Leave mobile version"}</a></li>

    {if $access_edit}
        <li data-theme="e">
            <a href="{modurl modname='ZikulaBlocksModule' type='admin' func='modify' bid=$blockinfo.bid addurl=1}#editmenu" title="{gt text='Add the current URL as a new link in this block' domain='zikula'}">{gt text='Add current URL' domain='zikula'}</a>
        </li>
        <li data-theme="e">
            <a href="{modurl modname='ZikulaBlocksModule' type='admin' func='modify' bid=$blockinfo.bid fromblock=1}" title="{gt text='Edit this block' domain='zikula'}">{gt text='Edit this block' domain='zikula'}</a>
        </li>
    {/if}
</ul>