<nav class="navbar navbar-default" role="navigation">
    <div class="extmenu collapse navbar-collapse" id="extmenu-navbar-collapse-{$blockinfo.bid}">
        {menu from=$menuitems item='item' name='extmenu' class='nav navbar-nav'}
            {if $item.name ne '' && $item.url ne ''}
                <li{if $item.url|replace:$baseurl:'' eq $currenturi|urldecode} class="active"{/if}>
                    <a href="{$item.url|safetext}" title="{$item.title}">
                        {if $item.image ne ''}
                        <img src="{$item.image}" alt="{$item.title}" />
                        {/if}
                        {$item.name}
                    </a>
                </li>
            {else}
                <li>&nbsp;</li>
            {/if}
        {/menu}
    </div>
</nav>
