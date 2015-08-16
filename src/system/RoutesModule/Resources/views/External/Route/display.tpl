{* Purpose of this template: Display one certain route within an external context *}
<div id="route{$route.id}" class="zikularoutesmodule-external-route">
{if $displayMode eq 'link'}
    <p>
    {$route->getTitleFromDisplayPattern()|notifyfilters:'routes.filter_hooks.routes.filter'}
    </p>
{/if}
{checkpermissionblock component='ZikulaRoutesModule::' instance='::' level='ACCESS_EDIT'}
    {if $displayMode eq 'embed'}
        <p class="zikularoutesmodule-external-title">
            <strong>{$route->getTitleFromDisplayPattern()|notifyfilters:'routes.filter_hooks.routes.filter'}</strong>
        </p>
    {/if}
{/checkpermissionblock}

{if $displayMode eq 'link'}
{elseif $displayMode eq 'embed'}
    <div class="zikularoutesmodule-external-snippet">
        &nbsp;
    </div>

    {* you can distinguish the context like this: *}
    {*if $source eq 'contentType'}
        ...
    {elseif $source eq 'scribite'}
        ...
    {/if*}

    {* you can enable more details about the item: *}
    {*
        <p class="zikularoutesmodule-external-description">
            {if $route.name ne ''}{$route.name}<br />{/if}
        </p>
    *}
{/if}
</div>
