{admincategorymenu}
<div class="z-adminbox">
    <h1>{$currentmodule}</h1>
    {modulelinks modname=$currentmodule type='admin'}
</div>

{ajaxheader modname='Modules' filename='hookui.js'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='windowlist.gif' set='icons/large' __alt='Hook Providers'}</div>
    <h2>{gt text='Hook Providers'}</h2>

    {if count($hookproviders) > 0}

        <h3>{gt text='%s is subscribed to the following providers.' tag1=$currentmodule}</h3>

        <p class="z-informationmsg">{gt text='Use drag and drop to arrange the providers into your desired order. The new order will be saved automatically.'}</p>

        <ol id="providerssortlist" class="z-itemlist">
            <li id="providerssortlistheader" class="z-itemheader z-itemsortheader z-clearfix">
            <span class="z-itemcell z-w05">{gt text='ID'}</span>
            <span class="z-itemcell z-w25">{gt text='Display name'}</span>
            <span class="z-itemcell z-w70">{gt text='Description'}</span>
            </li>
            {foreach from=$hookproviders item='provider'}
            <li id="provider_{$provider.id}" class="{cycle name='providerslist' values='z-odd,z-even'} z-sortable z-clearfix">
            <span id="providerdrag_{$provider.id}" class="z-itemcell z-w05">{$provider.id}</span>
            <span class="z-itemcell z-w25">{$provider.displayname|safetext|default:$provider.name}</span>
            <span class="z-itemcell z-w70">{$provider.description|safetext}</span>
            </li>
            {/foreach}
        </ol>

    {else}

        <p>{gt text="There aren't any providers attached to %s." tag1=$currentmodule}</p>

    {/if}
</div>

<script type="text/javascript">
    var subscriber = '{{$currentmodule}}';
    Event.observe(window, 'load', initprovidersorting);
</script>