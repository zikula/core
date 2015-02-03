{* Purpose of this template: Display item information for previewing from other modules *}
<dl id="route{$route.id}">
<dt>{$route->getTitleFromDisplayPattern()|notifyfilters:'routes.filter_hooks.routes.filter'|htmlentities}</dt>
{if $route.name ne ''}<dd>{$route.name}</dd>{/if}
</dl>
