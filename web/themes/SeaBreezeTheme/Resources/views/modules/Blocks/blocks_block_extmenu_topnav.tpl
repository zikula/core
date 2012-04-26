{menu from=$menuitems item='item' name='extmenu' id='nav'}
{if $item.name != '' && $item.url != ''}
<li class="page_item {if $item.url|replace:$baseurl:'' eq $currenturi|urldecode}selected{/if}">
    <a href="{$item.url|safetext}" title="{$item.title}">
        {if $item.image != ''}
        <img src="{$item.image}" alt="{$item.title}" />
        {/if}
        {$item.name}
    </a>
</li>
{/if}
{/menu}