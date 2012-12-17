{ajaxheader modname="Extensions" filename="extendedhooks.js"}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="hook" size="small"}
    <h3>{gt text="Extended legacy hook settings for"} {modgetinfo modid=$id info=displayname}</h3>
</div>

<p class="z-warningmsg">{gt text="Please note that only legacy module types appear in this list."}</p>
<ul class="z-menulinks">
    <li><a href="{modurl modname="Extensions" type="admin" func="legacyhooks" id=$id}" title="{gt text="Basic legacy hook settings"}">{gt text="Basic legacy hook settings"}</a></li>
    <li><a href="{modurl modname=Extensions type=admin func=extendedhooks id=$id}" title="{gt text="Extended legacy hook settings"}">{gt text="Extended legacy hook settings"}</a></li>
</ul>

{if $grouped_hooks}
<form class="z-form" action="{modurl modname="Extensions" type="admin" func="extendedupdatehooks"}" method="post" enctype="application/x-www-form-urlencoded">
    {foreach key=hookaction item=hookgroup from=$grouped_hooks}
    <fieldset>
        <legend>{gt text="Legacy '%s' hook modules" tag1=$hookaction}</legend>
        <div id="{$hookaction}" class="hookcontainer">
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <input type="hidden" name="id" value="{$id|safetext}" />
            {foreach item=hook from=$hookgroup}
            <div id="hook_{$hookaction}_{$hook.tmodule|safetext}" class="z-formrow z-sortable {cycle values="z-odd,z-even"}" style="background-image:url(../../../images/icons/extrasmall/move.png); background-position:5px 50%; background-repeat:no-repeat; border:1px dotted #999999; padding-left: 30px; line-height:2em; margin:0.5em; padding:0.2em; cursor:move; ">
                <span class="z-label" style="width:40%;">{gt text="Activate"} {$hook.tmodule|safetext} {gt text="for"} {$modinfo.displayname}</span>
                <input id="extensions_{$hookaction}_{$hook.tmodule|safetext}" name="hooks[{$hookaction}][{$hook.tmodule|safetext}]" type="checkbox" {if $hook.hookvalue eq 1}checked="checked"{/if} value="ON" />
            </div>
            {/foreach}
        </div>
    </fieldset>
    {/foreach}
    <div class="z-buttons z-formbuttons">
        {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
        <a href="{modurl modname="Extensions" type="admin" func="view"}" title="{gt text="Cancel"}">{img modname="core" src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>
{else}
<p class="z-warningmsg">{gt text="No legacy hookable modules installed."}</p>
{/if}
{adminfooter}
