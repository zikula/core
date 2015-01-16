<fieldset>
    <legend>{gt text='Connect %s to other modules' tag1=$currentmodule|safetext}</legend>
    {assign var='amountOfProviderAreas' value=$providerAreas|@count}
    <div{if $amountOfProviderAreas gt 5} id="registeredProviderAreas"{/if} class="z-form registered_provider_areas">
        <fieldset>
            <legend>{strip}
                {if $amountOfProviderAreas gt 5}<a href="#" onclick="return false">{/if}
                {gt text='%s module provides the following area:' plural='%s module provides the following areas:' tag1=$currentmodule|safetext count=$amountOfProviderAreas}
                {if $amountOfProviderAreas gt 5}</a>{/if}
            {/strip}</legend>
            <div>
                <ol>
                {foreach item='providerArea' from=$providerAreas}
                    <li><strong>{$providerAreasToTitles.$providerArea}</strong> <span class="sub">({$providerArea})</span></li>
                {/foreach}
                </ol>
            </div>
            <div class="alert alert-info">{gt text='To connect %s to one of the modules from the list below, click on the checkbox(es) next to the corresponding area.' tag1=$currentmodule|safetext}</div>

            <table class="table table-bordered table-striped" id="subscriberslist">
                <thead>
                    <tr>
                        <th class="z-w05">{gt text='ID'}</th>
                        <th class="z-w15">{gt text='Display name'}</th>
                        <th class="z-w80">{gt text='Connections'}</th>
                    </tr>
                </thead>
                <tbody>
                {foreach item='subscriber' from=$hooksubscribers}
                {if empty($subscriber.areas)}{continue}{/if}
                    <tr>
                        <td>{$subscriber.id}</td>
                        <td>{$subscriber.displayname|safetext|default:$subscriber.name}</td>
                        <td>
                            {assign var='connection_exists' value=false}

                            {foreach item='sarea' name='loop_sareas' from=$subscriber.areas}
                                {assign var='sarea_md5' value=$sarea|md5}
                                {* preliminary check to see if binding is allowed, if no bindings are allowed we don't show this row. Better usability. *}
                                {assign var='total_bindings' value=0}
                                {foreach item='parea' from=$providerAreas}
                                    {callfunc x_class='HookUtil' x_method='isAllowedBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='allow_binding'}
                                    {if $allow_binding}
                                        {assign var='total_bindings' value=$total_bindings+1}
                                        {assign var='connection_exists' value=true}
                                        {break}
                                    {/if}
                                {/foreach}

                                {if $total_bindings eq 0}
                                    {if $connection_exists eq false}<span class="sub">{gt text='%1$s module can\'t connect to %2$s module. No connections are supported' tag1=$currentmodule tag2=$subscriber.name|safetext}</span>{/if}
                                    {continue}
                                {/if}

                                {if $smarty.foreach.loop_sareas.iteration lte $smarty.foreach.loop_sareas.total && $smarty.foreach.loop_sareas.iteration gt 1}
                                    {* TODO - do this with styles perhaps ? *}
                                    <div style="height:5px; margin-top: 5px; border-top:1px dotted #dedede;"></div>
                                {/if}

                                <div class="clearfix">
                                    <div class="pull-left z-w45">
                                        {$subscriber.areasToTitles.$sarea} <span class="sub">({$sarea})</span>
                                    </div>

                                    <div class="pull-left z-w10 text-center">
                                        <span class="fa fa-paperclip"></span>
                                    </div>

                                    <div class="pull-left z-w45">
                                        {foreach item='parea' from=$providerAreas}
                                            {assign var='parea_md5' value=$parea|md5}
                                            {callfunc x_class='HookUtil' x_method='isAllowedBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='allow_binding'}
                                            {if !$allow_binding}{continue}{/if}
                                            {callfunc x_class='HookUtil' x_method='getBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='binding'}
                                            <input type="checkbox" id="chk_{$sarea_md5}_{$parea_md5}" name="chk[{$sarea_md5}][{$parea_md5}]" onclick="subscriberAreaToggle('{$sarea}', '{$parea}', true);" {if $binding}checked="checked"{/if} /> {$providerAreasToTitles.$parea} <span class="sub">({$parea})</span><br />
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
</fieldset>
