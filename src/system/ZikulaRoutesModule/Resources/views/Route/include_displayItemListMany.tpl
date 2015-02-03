{* purpose of this template: inclusion template for display of related routes *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{if $lct ne 'admin'}
    {checkpermission component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_EDIT' assign='hasAdminPermission'}
    {checkpermission component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_EDIT' assign='hasEditPermission'}
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
    <a href="{route name='zikularoutesmodule_route_display'  id=$item.id lct=$lct}" title="{$item->getTitleFromDisplayPattern()|replace:"\"":""}">
{/if}
    {$item->getTitleFromDisplayPattern()}
{if !$nolink}
    </a>
    <a id="routeItem{$item.id}Display" href="{route name='zikularoutesmodule_route_display'  id=$item.id lct=$lct theme='Printer'}" title="{gt text='Open quick view window'}" class="fa fa-search-plus hidden"></a>
{/if}
{/strip}
{if !$nolink}
<script type="text/javascript">
/* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            zikulaRoutesInitInlineWindow($('#routeItem{{$item.id}}Display'), '{{$item->getTitleFromDisplayPattern()|replace:"'":""}}');
        });
    })(jQuery);
/* ]]> */
</script>
{/if}
    </li>
    {/if}
{/foreach}
</ul>
{/if}
