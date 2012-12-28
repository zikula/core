{checkpermissionblock component='Admin::' instance='::' level=ACCESS_ADMIN}
{modurl modname=Theme type=admin func=modifyconfig assign=themeurl}
{if $notices.developer.devmode}
<div id="z-developernotices">
    <strong>{gt text="Developer notices (development mode on)" domain="zikula"}</strong>
    <ul class="z-hide">
        {if isset($notices.developer.render)}
        <li>
            <a href="{$themeurl|safetext}">{gt text="Enabled Template settings" domain="zikula"}:</a>
            {foreach from=$notices.developer.render name=item item=item}
            {$item.title}{if !$smarty.foreach.item.last}, {/if}
            {/foreach}
        </li>
        {/if}
        {if isset($notices.developer.theme)}
        <li>
            <a href="{$themeurl|safetext}">{gt text="Enabled Theme settings" domain="zikula"}:</a>
            {foreach from=$notices.developer.theme name=item item=item}
            {$item.title}{if !$smarty.foreach.item.last}, {/if}
            {/foreach}
        </li>
        {/if}
        {if isset($notices.developer.cssjscombine) && $notices.developer.cssjscombine}
        <li>{gt text="CSS/JS combination is enabled" domain="zikula"}</li>
        {/if}
        <li>
            <a href="{modurl modname=Theme type=admin func=clearallcompiledcaches}">{gt text="Clear all cache and compile directories"}</a>
        </li>
    </ul>
</div>
{/if}
{/checkpermissionblock}
