{ajaxheader modname='Extensions' filename='hookui.js'}
{pageaddvarblock}
    <script type="text/javascript">
    {{if $isProvider && !empty($providerAreas) && $total_available_subscriber_areas gt 0}}
    document.observe("dom:loaded", function() {
        $$('#subscriberslist input').each(function(obj) {
            obj.observe('click', subscriberAreaToggle);
        });
    });
    {{/if}}

    {{if $isSubscriber && !empty($subscriberAreas) && $total_attached_provider_areas gt 0}}
    var providerareas = new Array();
    document.observe("dom:loaded", function() {
        {{foreach from=$areasSorting key='category' item='area'}}
        {{foreach from=$area key='sarea' item='pareas'}}
        {{assign var="sarea_md5" value=$sarea|md5}}
        providerareas.push('{{$sarea_md5}}');
        {{/foreach}}
        {{/foreach}}
        initproviderareassorting();
    });
    {{/if}}
    </script>
{/pageaddvarblock}

{admincategorymenu}
<div class="z-adminbox">
    <h1>{$currentmodule}</h1>
    {modulelinks modname=$currentmodule type='admin'}
</div>

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="hook" size="large"}</div>
    <h2>{gt text='Hooks'}</h2>

    <div class="z-form">

        {if $isProvider && !empty($providerAreas) && $total_available_subscriber_areas gt 0}
        <fieldset>
            <legend>{gt text="Connect %s to other modules" tag1=$currentmodule|safetext}</legend>
            <p class="z-informationmsg">
                {assign var="total_provider_areas" value=$providerAreas|@count}
                {gt text="%s module provides the following area:" plural="%s module provides the following areas:" tag1=$currentmodule|safetext count=$total_provider_areas}
                <br />
                {foreach from=$providerAreas item='providerarea' name="loop"}
                {if $total_provider_areas gt 1}{$smarty.foreach.loop.iteration}) {/if}<strong>{$providerAreasTitles.$providerarea} ({$providerarea})</strong><br />
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
                        {if $connection_exists eq false}<span class="z-sub">{gt text="%s module can't connect to %s module. No connections are supported" tag1=$currentmodule tag2=$subscriber.name|safetext}</span>{/if}
                        {continue}
                        {/if}

                        {if $smarty.foreach.loop_sareas.iteration lte $smarty.foreach.loop_sareas.total && $smarty.foreach.loop_sareas.iteration gt 1}
                        {* TODO - do this with styles perhaps ? *}
                        <div style="height:5px; margin-top: 5px; border-top:1px dotted #dedede;"></div>
                        {/if}

                        <div class="z-clearfix">
                            <div class="z-floatleft z-w45">
                                {$subscriber.areasTitles.$sarea} <span class="z-sub">({$sarea})</span>
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
                                <input type="checkbox" id="chk_{$sarea_md5}_{$parea_md5}" name="chk[{$sarea_md5}][{$parea_md5}]" value="subscriberarea={$sarea}#providerarea={$parea}" {if $binding}checked="checked"{/if} /> {$providerAreasTitles.$parea} <span class="z-sub">({$parea})</span><br />
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
        {/if}

        {if $isSubscriber && !empty($subscriberAreas)}
        <fieldset>
            <legend>{gt text="Reorder attached areas"}</legend>

            <p class="z-informationmsg">
                {assign var="total_subscriber_areas" value=$subscriberAreas|@count}
                {gt text="%s module subscribes with the following area:" plural="%s module subscribes with the following areas:" tag1=$currentmodule|safetext count=$total_subscriber_areas}
                <br />
                {foreach from=$subscriberAreas item='subscriberarea' name="loop"}
                {if $total_subscriber_areas gt 1}{$smarty.foreach.loop.iteration}) {/if}<strong>{$subscriberAreasTitles.$subscriberarea} ({$subscriberarea})</strong><br />
                {/foreach}
                <br />
                {gt text="To reorder the attached areas from the list below, drag the area that you want to change and drop it to your desired position. To attach another area to %s module, please visit the module that provides it." tag1=$currentmodule|safetext}

                {if count($suggestedProviders) gt 0}
                {assign var="suggested_modules" value=""}
                {foreach from=$suggestedProviders item=suggestedProvider name="loop_suggested_providers"}
                {modurl modname=$suggestedProvider.name type='admin' func='hooks' assign='suggested_module_url'}
                {if $smarty.foreach.loop_suggested_providers.iteration lt $smarty.foreach.loop_suggested_providers.total}
                {assign var="suggested_module_comma" value=", "}
                {else}
                {assign var="suggested_module_comma" value=""}
                {/if}
                {assign var="suggested_modules" value="`$suggested_modules`<a href=\"`$suggested_module_url`\">`$suggestedProvider.name`</a>`$suggested_module_comma`"}
                {/foreach}
                {gt text="(eg %s)" tag1=$suggested_modules|safehtml}
                {/if}
            </p>

            {foreach from=$areasSorting key='category' item='area'}
            <h3>{$category}</h3>
            
            {foreach from=$area key='sarea' item='pareas' name='loop_sareas'}
            {assign var="sarea_md5" value=$sarea|md5}

            <ol id="providerareassortlist_{$sarea_md5}" class="z-itemlist">
                <li id="providerareassortlistheader_{$sarea_md5}" class="z-itemheader z-clearfix">
                    <span class="z-itemcell z-w100">{$subscriberAreasTitles.$sarea} ({$sarea})</span>
                    <input type="hidden" id="providerareassortlist_{$sarea_md5}_h" value="{$sarea}" />
                </li>

                {foreach from=$pareas item='parea'}
                {assign var="parea_md5" value=$parea|md5}

                <li id="providerarea_{$parea_md5}" class="{cycle name='providerareaslist' values='z-odd,z-even'} z-sortable z-clearfix">
                    <span class="z-itemcell z-w100">{$areasSortingTitles.$parea} ({$parea})</span>
                    <input type="hidden" id="providerarea_{$parea_md5}_h" value="{$parea}" />
                </li>
                {foreachelse}
                <li class="z-clearfix"><span class="z-itemcell z-w100">{gt text="There aren't any areas attached here"}</span></li>
                {/foreach}
            </ol>

            {/foreach}
            {/foreach}
        </fieldset>
        {/if}

    </div>

</div>
