<div class="navcontainer">
    <ul class="navlist">
    {foreach item='item' from=$menuitems}
        {if $item.MENUITEMTITLE ne '' && $item.MENUITEMURL ne ''}
            <li><a href="{$item.MENUITEMURL|safetext}" title="{$item.MENUITEMCOMMENT}">{$item.MENUITEMTITLE|safetext}</a></li>
        {else}
            <li style="list-style: none">{$item.MENUITEMTITLE|safehtml}</li>
        {/if}
    {/foreach}
    </ul>
</div>
