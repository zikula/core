{ajaxheader modname='Modules' filename='hookui.js'}

{admincategorymenu}
<div class="z-adminbox">
    <h1>{$currentmodule}</h1>
    {modulelinks modname=$currentmodule type='admin'}
</div>

<div class="z-admincontainer">
    {* TODO - large icon for hooks ? *}
    <div class="z-adminpageicon">{img modname='core' src='configure.gif' set='icons/large' __alt='Hooks'}</div>
    <h2>{gt text='Hooks'}</h2>
	
    <div class="z-form">

    {if $isProvider}
	<fieldset>
	<legend>{gt text="Connect %s to other modules" tag1=$currentmodule}</legend>

	<p class="z-warningmsg">
            {gt text="%s module has the following areas:" tag1=$currentmodule}
            <br />
            {foreach from=$providerAreas item='providerarea' name="loop"}
                {assign var="providerarealastdotpos" value=$providerarea|strrpos:'.'}
                {assign var="providerareashort" value=$providerarea|substr:$providerarealastdotpos+1}
                {$smarty.foreach.loop.iteration}) {$providerareashort}<br />
            {/foreach}
	</p>

	<p class="z-informationmsg">{gt text="To connect %s to one of the modules from the list below, click on the checkbox(es) next to the corresponding area." tag1=$currentmodule}</p>

	<table class="z-datatable" id="subscriberslist">
        <thead>
        <tr>
            <th class="z-w05">{gt text='ID'}</th>
            <th class="z-w20">{gt text='Display name'}</th>
            <th class="z-w75">{gt text='Connections'}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$hooksubscribers item='subscriber'}
            {if empty($subscriber.areas)}{continue}{/if}

            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$subscriber.id}</td>
                <td>{$subscriber.displayname|safetext|default:$subscriber.name}</td>
                <td>

                {foreach from=$subscriber.areas item='sarea' name='loop_sareas'}
                    {assign var="sarealastdotpos" value=$sarea|strrpos:'.'}
                    {assign var="sareashort" value=$sarea|substr:$sarealastdotpos+1}

                    <div class="z-clearfix">
                        <div class="z-floatleft z-w20">
                        {$sareashort}
                        </div>

                        <div class="z-floatleft z-w04 z-center">
                        {img src="attach.gif" modname="core" set="icons/extrasmall"}
                        </div>

                        <div class="z-floatleft">
                        {foreach from=$providerAreas item='parea'}
                            {assign var="parealastdotpos" value=$parea|strrpos:'.'}
                            {assign var="pareashort" value=$parea|substr:$parealastdotpos+1}
                            {callfunc x_class='HookUtil' x_method='allowBindingBetweenAreas' sarea=$sarea parea=$parea x_assign='allow_binding'}
                            {if !$allow_binding}{continue}{/if}
                            {callfunc x_class='HookUtil' x_method='bindingBetweenAreas' sarea=$sarea parea=$parea x_assign='binding'}
                            <input type="checkbox" id="chk_{$sareashort}_{$pareashort}" name="chk[{$sareashort}][{$pareashort}]" value="subscriberarea={$sarea}#providerarea={$parea}" {if $binding}checked="checked"{/if} /> {$pareashort}<br />
                        {/foreach}
                        </div>
                    </div>

                    {if $smarty.foreach.loop_sareas.iteration lt $smarty.foreach.loop_sareas.total}
                    {* TODO - do this with styles perhaps ? *}
                    <div style="height:5px; margin-bottom: 5px; border-bottom:1px dotted #dedede;"></div>
                    {/if}

                {/foreach}
                    
                </td>
                
            </tr>
	{/foreach}
        </tbody>
	</table>
    
	</fieldset>
    {/if}

    {if $isSubscriber}
	<fieldset>
	<legend>{gt text="Reorder areas"}</legend>

        <p class="z-warningmsg">
            {gt text="%s module has the following areas:" tag1=$currentmodule}
            <br />
            {foreach from=$subscriberAreas item='subscriberarea' name="loop"}
                {assign var="subscriberarealastdotpos" value=$subscriberarea|strrpos:'.'}
                {assign var="subscriberareashort" value=$subscriberarea|substr:$subscriberarealastdotpos+1}
                {$smarty.foreach.loop.iteration}) {$subscriberareashort}<br />
            {/foreach}
	</p>

        <p class="z-informationmsg">{gt text="To reorder the areas from the list below (for each of %s areas), drag and drop the corresponding area. To attach another area, please visit the module that provides it." tag1=$currentmodule}</p>

        {foreach from=$areasSorting key='sarea' item='pareas' name='loop_sareas'}
            {assign var="sarealastdotpos" value=$sarea|strrpos:'.'}
            {assign var="sareashort" value=$sarea|substr:$sarealastdotpos+1}
            
            <ol id="providerareassortlist_{$sareashort}" class="z-itemlist">
		<li id="providerareassortlistheader_{$sareashort}" class="z-itemheader z-clearfix">
                    <span class="z-itemcell z-w100">{$sareashort}</span>
                    <input type="hidden" id="providerareassortlist_{$sareashort}_h" value="{$sarea}" />
		</li>

                {foreach from=$pareas item='parea'}
                    {assign var="parealastdotpos" value=$parea|strrpos:'.'}
                    {assign var="pareashort" value=$parea|substr:$parealastdotpos+1}
                    
                    <li id="providerarea_{$pareashort}" class="{cycle name='providerareaslist' values='z-odd,z-even'} z-sortable z-clearfix">
			<span class="z-itemcell z-w100">{$pareashort}</span>
                        <input type="hidden" id="providerarea_{$pareashort}_h" value="{$parea}" />
                    </li>
                {/foreach}
            </ol>
        {/foreach}
        </fieldset>
    {/if}

    </div>

</div>

<script type="text/javascript">
{{if $isProvider}}
$$('#subscriberslist input').each(function(obj) {
	obj.observe('click', subscriberAreaToggle);
});
{{/if}}

{{if $isSubscriber}}
var providerareas = new Array();
{{foreach from=$areasSorting key='sarea' item='parea'}}
    {{assign var="sarealastdotpos" value=$sarea|strrpos:'.'}}
    {{assign var="sareashort" value=$sarea|substr:$sarealastdotpos+1}}
    providerareas.push('{{$sareashort}}');
{{/foreach}}
Event.observe(window, 'load', initproviderareassorting);
{{/if}}
</script>

