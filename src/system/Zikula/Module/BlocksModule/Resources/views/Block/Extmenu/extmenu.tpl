<div id="navcontainer_{$blockinfo.bid}" class="navcontainer">
    {menu from=$menuitems item='item' name='extmenu' class='navlist'}
    {if $item.name ne '' && $item.url ne ''}
    <li{if $item.url|replace:$baseurl:'' eq $currenturi|urldecode} class="selected"{/if}>
        <a href="{$item.url|safetext}" title="{$item.title}">
            {if $item.image ne ''}
            <img src="{$item.image}" alt="{$item.title}" />
            {/if}
            {$item.name}
        </a>
    </li>
    {else}
    <li style="list-style: none; background: none;">&nbsp;</li>
    {/if}
    {/menu}
    {if $access_edit}
    <p class="extmenuadmin">
        <a href="{route name='zikulablocksmodule_admin_modify' bid=$blockinfo.bid addurl=1}#editmenu" title="{gt text='Add the current URL as a new link in this block' domain='zikula'}">{gt text='Add current URL' domain='zikula'}</a>
        <br />
        <a href="{route name='zikulablocksmodule_admin_modify' bid=$blockinfo.bid fromblock=1}" title="{gt text='Edit this block' domain='zikula'}">{gt text='Edit this block' domain='zikula'}</a>
    </p>
    {/if}
</div>
