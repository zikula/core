{ajaxheader modname='Extensions' filename='hookui.js' ui=true}
{pageaddvar name='stylesheet' value='system/Extensions/style/hooks.css'}
{pageaddvarblock}
<script type="text/javascript">
    var subscriber_areas = new Array();
    var subscriber_panel = null;

    document.observe("dom:loaded", function() {
        if ($('hooks_tabs')) {
            new Zikula.UI.Tabs('hooks_tabs');
        }

        {{if $isSubscriber && !empty($subscriberAreas)}}
        {{foreach from=$subscriberAreas item='sarea'}}
        {{assign var="sarea_md5" value=$sarea|md5}}
        subscriber_areas.push('sarea_{{$sarea_md5}}');
        {{/foreach}}
        {{/if}}
   
        initAreasSortables();
        initAreasDraggables();
        initAreasDroppables();

        new Zikula.UI.Panels('hooks_provider_areas', {
            active: [0],
            effectDuration: 0.5
        });

        subscriber_panel = new Zikula.UI.Panels('hooks_subscriber_areas', {
            headerSelector: 'h4',
            headerClassName: 'attachedarea-header',
            contentClassName: 'attachedarea-content',
            active: [0],
            effectDuration: 0.5
        });

        $$('.sareas_category div').each(function(item, i){
            item.store('panelIndex', i);
        });

        if ($('registered_provider_areas')) {
            new Zikula.UI.Panels('registered_provider_areas', {
                headerSelector: 'legend',
                headerClassName: 'subscriberarea-header'
            });
        }
    });
</script>
{/pageaddvarblock}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type='hook' size='small'}
    <h3>{gt text='Hooks'}</h3>
</div>

{if $isSubscriber and $isProvider and !empty($providerAreas) and $total_available_subscriber_areas gt 0}
<ul id="hooks_tabs" class="z-tabs">
    <li class="tab"><a href="#hooks_subscriber">{gt text='Subscription'}</a></li>
    <li class="tab"><a href="#hooks_provider">{gt text='Provision'}</a></li>
</ul>
{/if}

{if $isSubscriber}
<div id="hooks_subscriber" class="z-form z-clearfix">

    <div id="hooks_subscriber_areas" class="z-floatleft z-w49">
        <fieldset>
            <legend>{gt text='Attached areas'}</legend>

            {foreach from=$subscriberAreasAndCategories key='category' item='areas'}

            <fieldset class="sareas_category">
                <legend>{$category|safetext}</legend>

                {foreach from=$areas item='sarea'}
                    {assign var='sarea_md5' value=$sarea|md5}

                    <div id="sarea_{$sarea_md5}" class="sarea_wrapper">
                        <input type="hidden" id="sarea_{$sarea_md5}_a" value="{$sarea}" />
                        <input type="hidden" id="sarea_{$sarea_md5}_c" value="{$subscriberAreasToCategories.$sarea}" />
                        <input type="hidden" id="sarea_{$sarea_md5}_i" value="{$sarea_md5}" />

                        <h4>{$subscriberAreasToTitles.$sarea}<span class="z-sub">({$sarea})</span></h4>
                        <ol id="sarea_{$sarea_md5}_list" class="z-itemlist">
                            {if isset($areasSorting.$category.$sarea)}
                                {foreach from=$areasSorting.$category.$sarea item='parea'}
                                    {assign var='parea_md5' value=$parea|md5}
                                    {assign var='attached_area_identifier' value="`$parea_md5`-`$sarea_md5`"}

                                    <li id="attachedarea_{$attached_area_identifier}" class="z-clearfix z-sortable {cycle name="attachedareaslist_`$sarea`" values='z-even,z-odd'}">
                                        <span>
                                            {$areasSortingTitles.$parea} <span class="z-sub">({$parea})</span>
                                            <a class="detachlink" title="{gt text='Detach'} {$areasSortingTitles.$parea}" href="javascript:void(0)" onclick="unbindProviderAreaFromSubscriberArea('{$sarea_md5}', '{$sarea}', '{$parea_md5}', '{$parea}');" onmouseover="this.up().up().addClassName('attachedarea_detach')" onmouseout="this.up().up().removeClassName('attachedarea_detach')">{img modname='core' set='icons/extrasmall' src='button_cancel.png' width='10' height='10' __alt='detach'}</a>
                                        </span>
                                        <input type="hidden" id="attachedarea_{$attached_area_identifier}_a" value="{$parea}" />
                                        <input type="hidden" id="attachedarea_{$attached_area_identifier}_c" value="{$category}" />
                                        <input type="hidden" id="attachedarea_{$attached_area_identifier}_i" value="{$parea_md5}" />
                                    </li>

                                {/foreach}
                            {/if}

                            <li id="sarea_empty_{$sarea_md5}" class="z-clearfix sarea_empty {if isset($areasSorting.$category.$sarea)}z-hide{/if}">
                                <span class="z-itemcell">{gt text="There aren't any areas attached here.<br />Drag an area from the right and drop it here to attach it."}</span>
                            </li>
                        </ol>
                    </div>
                
                {/foreach}
            </fieldset>
                
            {/foreach}
        </fieldset>
    </div>

    <div id="hooks_provider_areas" class="z-floatright z-w49">
        <fieldset>
            <legend>{gt text='Available areas'}</legend>

            {foreach from=$hookproviders item='hookprovider'}

                {if !empty($hookprovider.areas)}
                    <div class="parea_wrapper">
                        <h4 class="z-panel-header">{$hookprovider.name}</h4>

                        <div class="z-panel-content">
                            {foreach from=$hookprovider.areasAndCategories key='category' item='areas'}
                            <fieldset class="pareas_category">
                                <legend>{$category}</legend>

                                {assign var="draglist_identifier" value="`$hookprovider.name`_`$category`"}
                                {assign var="draglist_identifier_md5" value=$draglist_identifier|md5}

                                <ol id="availableareasdraglist_{$draglist_identifier_md5}" class="z-itemlist">
                                    {foreach from=$areas item='parea'}
                                        {assign var="parea_md5" value=$parea|md5}
                                        {assign var="available_area_identifier" value="`$parea_md5`-::sarea_identifier::"}

                                        <li id="availablearea_{$available_area_identifier}" class="{cycle name="availableareaslist_`$draglist_identifier`" values='z-even,z-odd'} z-draggable z-clearfix">
                                            <span class="z-itemcell">{$hookprovider.areasToTitles.$parea} <span class="z-sub">({$parea})</span> <a class="detachlink z-hide" href="javascript:" onclick="unbindProviderAreaFromSubscriberArea('##id', '##name', '{$parea_md5}', '{$parea}');" onmouseover="this.up().up().addClassName('attachedarea_detach')" onmouseout="this.up().up().removeClassName('attachedarea_detach')" title="{gt text='Detach'} {$hookprovider.areasToTitles.$parea}">{img modname='core' set='icons/extrasmall' src='button_cancel.png' width='10' height='10' __alt='detach'}</a></span>
                                            <input type="hidden" id="availablearea_{$available_area_identifier}_a" value="{$parea}" />
                                            <input type="hidden" id="availablearea_{$available_area_identifier}_c" value="{$hookprovider.areasToCategories.$parea}" />
                                            <input type="hidden" id="availablearea_{$available_area_identifier}_i" value="{$parea_md5}" />
                                        </li>

                                    {/foreach}
                                </ol>
                            </fieldset>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            {foreachelse}
                <p class="z-warningmsg">{gt text='There are no providers available for %s.' tag1=$currentmodule}</p>
            {/foreach}
        </fieldset>
    </div>
</div>
{/if}


{if $isProvider and !empty($providerAreas) and $total_available_subscriber_areas gt 0}
<div id="hooks_provider" class="z-form">

    <fieldset>
        <legend>{gt text="Connect %s to other modules" tag1=$currentmodule|safetext}</legend>
        {assign var="total_provider_areas" value=$providerAreas|@count}
        <div{if $total_provider_areas gt 5} id="registered_provider_areas"{/if} class="z-form registered_provider_areas">
            <fieldset>
                <legend>
                    {if $total_provider_areas gt 5}<a href="#" onclick="return false">{/if}
                    {gt text="%s module provides the following area:" plural="%s module provides the following areas:" tag1=$currentmodule|safetext count=$total_provider_areas}
                    {if $total_provider_areas gt 5}</a>{/if}
                </legend>
                <div>
                    <ol>
                    {foreach from=$providerAreas item='providerarea' name="loop"}
                        <li><strong>{$providerAreasToTitles.$providerarea}</strong> <span class="z-sub">({$providerarea})</span></li>
                    {/foreach}
                    </ol>
                </div>
            </fieldset>
        </div>

        <div class="z-informationmsg">{gt text="To connect %s to one of the modules from the list below, click on the checkbox(es) next to the corresponding area." tag1=$currentmodule|safetext}</div>

        <table class="z-datatable" id="subscriberslist">
            <thead>
                <tr>
                    <th class="z-w05">{gt text='ID'}</th>
                    <th class="z-w15">{gt text='Display name'}</th>
                    <th class="z-w80">{gt text='Connections'}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$hooksubscribers item='subscriber'}
            {if empty($subscriber.areas)}{continue}{/if}

            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$subscriber.id}</td>
                <td>{$subscriber.displayname|safetext|default:$subscriber.name}</td>
                <td>
                    {assign var="connection_exists" value=false}

                    {foreach from=$subscriber.areas item='sarea' name='loop_sareas'}
                    {assign var="sarea_md5" value=$sarea|md5}
                    {* preliminary check to see if binding is allowed, if no bindings are allowed we don't show this row. Better usability. *}
                    {assign var="total_bindings" value=0}
                    {foreach from=$providerAreas item='parea'}
                    {callfunc x_class='HookUtil' x_method='isAllowedBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='allow_binding'}
                    {if $allow_binding}
                    {assign var="total_bindings" value=$total_bindings+1}
                    {assign var="connection_exists" value=true}
                    {break}
                    {/if}
                    {/foreach}

                    {if $total_bindings eq 0}
                    {if $connection_exists eq false}<span class="z-sub">{gt text='%1$s module can\'t connect to %2$s module. No connections are supported' tag1=$currentmodule tag2=$subscriber.name|safetext}</span>{/if}
                    {continue}
                    {/if}

                    {if $smarty.foreach.loop_sareas.iteration lte $smarty.foreach.loop_sareas.total && $smarty.foreach.loop_sareas.iteration gt 1}
                    {* TODO - do this with styles perhaps ? *}
                    <div style="height:5px; margin-top: 5px; border-top:1px dotted #dedede;"></div>
                    {/if}

                    <div class="z-clearfix">
                        <div class="z-floatleft z-w45">
                            {$subscriber.areasToTitles.$sarea} <span class="z-sub">({$sarea})</span>
                        </div>

                        <div class="z-floatleft z-w10 z-center">
                            {img src="attach.png" modname="core" set="icons/extrasmall"}
                        </div>

                        <div class="z-floatleft z-w45">
                            {foreach from=$providerAreas item='parea'}
                            {assign var="parea_md5" value=$parea|md5}

                            {callfunc x_class='HookUtil' x_method='isAllowedBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='allow_binding'}
                            {if !$allow_binding}{continue}{/if}
                            {callfunc x_class='HookUtil' x_method='getBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='binding'}
                            <input type="checkbox" id="chk_{$sarea_md5}_{$parea_md5}" name="chk[{$sarea_md5}][{$parea_md5}]" onclick="subscriberAreaToggle('{$sarea}', '{$parea}', true);" {if $binding}checked="checked"{/if} /> {$providerAreasToTitles.$parea} <span class="z-sub">({$parea})</span><br />
                            {/foreach}
                        </div>
                    </div>

                    {/foreach}
                </td>
            </tr>

            {/foreach}
            </tbody>
        </table>

    </fieldset>
</div>
{/if}
{adminfooter}
