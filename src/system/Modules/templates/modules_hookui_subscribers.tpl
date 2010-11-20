{admincategorymenu}
<div class="z-adminbox">
    <h1>{$currentmodule}</h1>
    {modulelinks modname=$currentmodule type='admin'}
</div>

{ajaxheader modname='Modules' filename='hookui.js' ui='true'}
{gt text='Click to attach this module' assign='str_attach'}
{gt text='Click to detach this module' assign='str_detach'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='windowlist.gif' set='icons/large' __alt='Hook Subscribers'}</div>
    <h2>{gt text='Hook Subscribers'}</h2>

    {if count($hooksubscribers) > 0}

        <h3>{gt text='Which modules would you like to attach %s to?' tag1=$currentmodule}</h3>

        <p class="z-informationmsg">{gt text='Click on the red icon to attach the provider. Clicking on the green icon, will detach the provider.'}</p>

        <ol id="subscriberslist" class="z-itemlist">
            <li id="subscriberslistheader" class="z-itemheader z-clearfix">
            <span class="z-itemcell z-w05">{gt text='ID'}</span>
            <span class="z-itemcell z-w25">{gt text='Display name'}</span>
            <span class="z-itemcell z-w60">{gt text='Description'}</span>
            <span class="z-itemcell z-w10">{gt text='Status'}</span>
            </li>
            {foreach from=$hooksubscribers item='subscriber'}
            <li id="subscriber_{$subscriber.id}" class="{cycle name='subscriberslist' values='z-odd,z-even'} z-clearfix">
            <span class="z-itemcell z-w05">{$subscriber.id}</span>
            <span class="z-itemcell z-w25">{$subscriber.displayname|safetext|default:$subscriber.name}</span>
            <span class="z-itemcell z-w60">{$subscriber.description|safetext}</span>
            <span class="z-itemcell z-w10">
            {if $subscriber.attached}
            <a class="subscriberbutton" href="javascript:void(0);" onclick="togglesubscriberstatus({$subscriber.id},'{$currentmodule}')">{img src='greenled.gif' modname='core' set='icons/extrasmall' class='tooltips' title=$str_detach alt=$str_detach id="attached_`$subscriber.id`"}{img src='redled.gif' modname='core' set='icons/extrasmall' class='tooltips' title=$str_attach alt=$str_attach style='display: none;' id="detached_`$subscriber.id`"}</a>
            {else}
            <a class="subscriberbutton" href="javascript:void(0);" onclick="togglesubscriberstatus({$subscriber.id},'{$currentmodule}')">{img src='greenled.gif' modname='core' set='icons/extrasmall' class='tooltips' title=$str_detach alt=$str_detach style='display: none;' id="attached_`$subscriber.id`"}{img src='redled.gif' modname='core' set='icons/extrasmall' class='tooltips' title=$str_attach alt=$str_attach id="detached_`$subscriber.id`"}</a>
            {/if}
            </span>
            </li>
            {/foreach}
        </ol>
        
    {else}
        
        <p>{gt text="There aren't any subscribers to attach %s to." tag1=$currentmodule}</p>
        
    {/if}
</div>

<script type="text/javascript">
    Zikula.UI.Tooltips($$('.tooltips'));
</script>