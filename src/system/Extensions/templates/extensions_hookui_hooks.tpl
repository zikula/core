{ajaxheader modname='Extensions' filename='hookui.js' ui=true}
{pageaddvarblock}
    <script type="text/javascript">
    var subscriber_areas = new Array();
    document.observe("dom:loaded", function() {
        {{if $isSubscriber && !empty($subscriberAreas)}}
        {{foreach from=$subscriberAreas item='sarea'}}
        {{assign var="sarea_md5" value=$sarea|md5}}
        subscriber_areas.push('{{$sarea_md5}}');
        {{/foreach}}
        {{/if}}
            
        initAreasSortables();
        initAreasDraggables();
        initAreasDroppables();
        
        new Zikula.UI.Accordion('accordion_available_provider_areas');
    });
    </script>
{/pageaddvarblock}

{admincategorymenu}
<div class="z-adminbox">
    <h2>{$currentmodule}</h2>
    {modulelinks modname=$currentmodule type='admin'}
</div>

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="hook" size="large"}</div>
    <h3>{gt text='Hooks'}</h3>

    <div class="z-form z-clearfix">
        
        <div class="z-floatleft z-w48">
            <fieldset>
                <legend>{gt text="Attached areas"}</legend>
                
                {if $isSubscriber && !empty($subscriberAreas)}
                <p class="z-informationmsg">
                    {assign var="total_subscriber_areas" value=$subscriberAreas|@count}
                    {gt text="%s module subscribes with the following area:" plural="%s module subscribes with the following areas:" tag1=$currentmodule|safetext count=$total_subscriber_areas}
                    <br />
                    {foreach from=$subscriberAreas item='subscriberarea' name="loop"}
                    {if $total_subscriber_areas gt 1}{$smarty.foreach.loop.iteration}) {/if}<strong>{$subscriberAreasToTitles.$subscriberarea}</strong> <span class="z-sub">({$subscriberarea})</span><br />
                    {/foreach}
                </p>
                {/if}
                
                {if $isSubscriber && !empty($subscriberAreasAndCategories)}
                {foreach from=$subscriberAreasAndCategories key='category' item='areas'}
                
                    <h4>{$category}</h4>
                    
                    {foreach from=$areas item='sarea'}
                        
                        {assign var="sarea_md5" value=$sarea|md5}

                        <ol id="attachedareassortlist_{$sarea_md5}" class="z-itemlist">
                            
                            <li id="attachedareassortlistheader_{$sarea_md5}" class="z-itemheader z-clearfix">
                                <span class="z-itemcell z-w100">{$subscriberAreasToTitles.$sarea} <span class="z-sub">({$sarea})</span></span>
                                <input type="hidden" id="attachedareassortlist_{$sarea_md5}_a" value="{$sarea}" />
                                <input type="hidden" id="attachedareassortlist_{$sarea_md5}_c" value="{$subscriberAreasToCategories.$sarea}" />
                                <input type="hidden" id="attachedareassortlist_{$sarea_md5}_i" value="{$sarea_md5}" />
                            </li>
                            
                            {if isset($areasSorting.$category.$sarea)}    
                                
                                {foreach from=$areasSorting.$category.$sarea item='parea'}
                                    
                                    {assign var="parea_md5" value=$parea|md5}
                                    
                                    <li id="attachedarea_{$parea_md5}" class="{cycle name="attachedareaslist_`$sarea`" values='z-even,z-odd'} z-sortable z-clearfix">
                                        <span class="z-itemcell z-w100">{$areasSortingTitles.$parea} <span class="z-sub">({$parea})</span> <a class="detachlink" style="position:absolute; right:5px; top:1px;" href="javascript:" onclick="unbindProviderAreaFromSubscriberArea('{$sarea_md5}', '{$sarea}', '{$parea_md5}', '{$parea}');" title="{gt text='Detach'} {$areasSortingTitles.$parea}">{img modname='core' set='icons/extrasmall' src='editdelete.png' __alt='detach'}</a></span>
                                        <input type="hidden" id="attachedarea_{$parea_md5}_a" value="{$parea}" />
                                        <input type="hidden" id="attachedarea_{$parea_md5}_c" value="{$category}" />
                                        <input type="hidden" id="attachedarea_{$parea_md5}_i" value="{$parea_md5}" />
                                    </li>
                                    
                                {/foreach}
                                    
                            {/if}  
                            
                            <li id="attachedarea_empty_{$sarea_md5}" class="z-clearfix {if isset($areasSorting.$category.$sarea)}z-hide{/if}"><span class="z-itemcell z-w100">{gt text="There aren't any areas attached here. Drag an area from the right and drop it here to attach it."}</span></li>
                            
                        </ol>
                    
                    {/foreach}
                
                {/foreach}
                {/if}
                
            </fieldset>
        </div>
        
        <div class="z-floatleft z-w04 z-nowrap">&nbsp;</div>
        
        <div id="accordion_available_provider_areas" class="z-floatleft z-w48">
            <fieldset>
                <legend>{gt text="Available areas"}</legend>
                
                {if $isSubscriber && !empty($hookproviders)}
                {foreach from=$hookproviders item='hookprovider'}
                
                    {if !empty($hookprovider.areas)}
                        
                        <h4 class="z-acc-header">{$hookprovider.name}</h4>
                       
                        <div class="z-acc-content">
                            
                            {foreach from=$hookprovider.areasAndCategories key='category' item='areas'}
                
                            <fieldset>
                                <legend>{$category}</legend>
                                
                                {assign var="draglist_identifier" value="`$hookprovider.name`_`$category`"}
                                {assign var="draglist_identifier_md5" value=$draglist_identifier|md5}
                                
                                <ol id="availableareasdraglist_{$draglist_identifier_md5}" class="z-itemlist">
                                    
                                    {foreach from=$areas item='parea'}
                                    
                                        {assign var="parea_md5" value=$parea|md5}
                                       
                                        <li id="availablearea_{$parea_md5}" class="{cycle name="availableareaslist_`$draglist_identifier`" values='z-even,z-odd'} z-draggable z-clearfix">
                                            <span class="z-itemcell z-w100">{$hookprovider.areasToTitles.$parea} <span class="z-sub">({$parea})</span> <a class="detachlink z-hide" style="position:absolute; right:5px; top:1px;" href="javascript:" onclick="unbindProviderAreaFromSubscriberArea('##id', '##name', '{$parea_md5}', '{$parea}');" title="{gt text='Detach'} {$hookprovider.areasToTitles.$parea}">{img modname='core' set='icons/extrasmall' src='editdelete.png' __alt='detach'}</a></span>
                                            <input type="hidden" id="availablearea_{$parea_md5}_a" value="{$parea}" />
                                            <input type="hidden" id="availablearea_{$parea_md5}_c" value="{$hookprovider.areasToCategories.$parea}" />
                                            <input type="hidden" id="availablearea_{$parea_md5}_i" value="{$parea_md5}" />
                                        </li>
                                    
                                    {/foreach}
                                    
                                </ol>
                                
                            </fieldset>
                            
                            {/foreach}
                                
                        </div>
                        
                    {/if}
                
                {/foreach}
                {/if}
                
            </fieldset>
        </div>
        
    </div>

        
    {if $isProvider && !empty($providerAreas) && $total_available_subscriber_areas gt 0}
    <div class="z-form">
        
        <fieldset>
            <legend>{gt text="Connect %s to other modules" tag1=$currentmodule|safetext}</legend>
            <p class="z-informationmsg">
                {assign var="total_provider_areas" value=$providerAreas|@count}
                {gt text="%s module provides the following area:" plural="%s module provides the following areas:" tag1=$currentmodule|safetext count=$total_provider_areas}
                <br />
                {foreach from=$providerAreas item='providerarea' name="loop"}
                {if $total_provider_areas gt 1}{$smarty.foreach.loop.iteration}) {/if}<strong>{$providerAreasToTitles.$providerarea}</strong> <span class="z-sub">({$providerarea})</span><br />
                {/foreach}
                <br />
                {gt text="To connect %s to one of the modules from the list below, click on the checkbox(es) next to the corresponding area." tag1=$currentmodule|safetext}
            </p>

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
                        {if $connection_exists eq false}<span class="z-sub">{gt text="%1$s module can't connect to %2$s module. No connections are supported" tag1=$currentmodule tag2=$subscriber.name|safetext}</span>{/if}
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

</div>
