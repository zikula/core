{admincategorymenu}
<div class="z-adminbox">
    <h1>{$currentmodule}</h1>
    {modulelinks modname=$currentmodule type='admin'}
</div>

{ajaxheader modname='Modules' filename='hookui.js' ui='true'}
{gt text="Click to attach this module" assign='str_attach'}
{gt text="Click to detach this module" assign='str_detach'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="View"}</div>
    <h2>{gt text="Hook Subscribers"}</h2>

    {if count($hooksubscribers) > 0}
    
        <p>{gt text="Which modules would you like to attach <strong>%s</strong> to?" tag1=$currentmodule}</p>

        {foreach from=$hooksubscribers item='hooksubscriber'}
        <div>
            {$hooksubscriber.displayname}

            {if $hooksubscriber.attached}
            <a class="actionbutton" href="javascript:void(0);" onclick="togglemodule({$hooksubscriber.id},'{$currentmodule}')">{img src="greenled.gif" modname="core" set="icons/extrasmall" class="tooltips" title=$str_detach alt=$str_detach id="attached_`$hooksubscriber.id`"}{img src="redled.gif" modname="core" set="icons/extrasmall" class="tooltips" title=$str_attach alt=$str_attach style="display: none;" id="detached_`$hooksubscriber.id`"}</a>
            {else}
            <a class="actionbutton" href="javascript:void(0);" onclick="togglemodule({$hooksubscriber.id},'{$currentmodule}')">{img src="greenled.gif" modname="core" set="icons/extrasmall" class="tooltips" title=$str_detach alt=$str_detach style="display: none;" id="attached_`$hooksubscriber.id`"}{img src="redled.gif" modname="core" set="icons/extrasmall" class="tooltips" title=$str_attach alt=$str_attach id="detached_`$hooksubscriber.id`"}</a>
            {/if}

        </div>
        {/foreach}
        
    {else}
        
        <p>{gt text="There aren't any subscribers to attach %s to." tag1=$currentmodule}</p>
        
    {/if}
</div>

<script type="text/javascript">
    Event.observe(window, 'load', initactionbuttons);
    Zikula.UI.Tooltips($$('.tooltips'));
</script>