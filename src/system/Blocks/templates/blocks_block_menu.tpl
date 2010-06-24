<div class="navcontainer">
    <ul class="navlist">
    {foreach from=$menuitems item=item}
        {if $item.MENUITEMTITLE != '' && $item.MENUITEMURL != ''}
            <li><a href="{$item.MENUITEMURL|safetext}" title="{$item.MENUITEMCOMMENT}">{$item.MENUITEMTITLE|safetext}</a></li>
        {else}
            <li style="list-style:none">{$item.MENUITEMTITLE|safehtml}</li>
        {/if}
    {/foreach}
    </ul>
</div>