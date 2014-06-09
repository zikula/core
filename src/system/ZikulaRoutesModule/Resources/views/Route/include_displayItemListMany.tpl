{* purpose of this template: inclusion template for display of related routes *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{if $lct ne 'admin'}
    {checkpermission component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_COMMENT' assign='hasAdminPermission'}
    {checkpermission component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_COMMENT' assign='hasEditPermission'}
{/if}
{if !isset($nolink)}
    {assign var='nolink' value=false}
{/if}
{if isset($items) && $items ne null && count($items) gt 0}
<ul class="zikularoutesmodule-related-item-list route">
{foreach name='relLoop' item='item' from=$items}
    {if $hasAdminPermission || $item.workflowState eq 'approved'}
    <li>
{strip}
{if !$nolink}
    <a href="{modurl modname='ZikulaRoutesModule' type='route' func='display' id=$item.id lct=$lct}" title="{$item->getTitleFromDisplayPattern()|replace:"\"":""}">
{/if}
    {$item->getTitleFromDisplayPattern()}
{if !$nolink}
    </a>
    <a id="routeItem{$item.id}Display" href="{modurl modname='ZikulaRoutesModule' type='route' func='display' id=$item.id lct=$lct theme='Printer'}" title="{gt text='Open quick view window'}" class="fa fa-search-plus hidden"></a>
{/if}
{/strip}
{if !$nolink}
<script type="text/javascript">
/* <![CDATA[ */
    document.observe('dom:loaded', function() {
        routesInitInlineWindow($('routeItem{{$item.id}}Display'), '{{$item->getTitleFromDisplayPattern()|replace:"'":""}}');
    });
/* ]]> */
</script>
{/if}
    </li>
    {/if}
{/foreach}
</ul>
{/if}
