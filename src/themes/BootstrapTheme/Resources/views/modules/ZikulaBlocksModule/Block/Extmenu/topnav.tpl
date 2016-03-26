{*
 this doesn't require surrounding divs because the menu is added to existing navbar
 see themes/BootstrapTheme/Resources/views/Include/main_menu.html.twig
*}
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
