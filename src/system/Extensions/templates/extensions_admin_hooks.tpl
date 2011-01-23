{include file="extensions_admin_menu.tpl"}
{ajaxheader modname="Extensions" filename="hooks.js"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=package.gif set=icons/large __alt="Hooks to enable for this module"}</div>
    <h2>{gt text="Basic hook settings for"} {modgetinfo modid=$id info=displayname}</h2>
    <ul id="extendedhookslinks" class="z-hide z-menulinks">
        <li><a href="{modurl modname=Extensions type=admin func=hooks id=$id}" title="{gt text="Basic hook settings"}">{gt text="Basic hook settings"}</a></li>
        <li><a href="{modurl modname=Extensions type=admin func=extendedhooks id=$id}" title="{gt text="Extended hook settings"}">{gt text="Extended hook settings"}</a></li>
    </ul>
    {if $hooks}
    <form class="z-form" action="{modurl modname="Extensions" type="admin" func="updatehooks"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Extensions"}" />
            <input type="hidden" name="id" value="{$id|safetext}" />
            <fieldset>
                <legend>{gt text="Hooked modules"}</legend>
                {section name=hook loop=$hooks}
                <div class="z-formrow">
                    <label for="extensions_{$hooks[hook].tmodule|safetext}" style="width:40%;">{gt text="Activate"} {$hooks[hook].tmodule|safetext} {gt text="for"} {$modinfo.displayname}</label>
                    {if $hooks[hook].hookvalue eq 1}
                    <input id="extensions_{$hooks[hook].tmodule|safetext}" name="hooks_{$hooks[hook].tmodule|safetext}" type="checkbox" checked="checked" value="ON" />
                    {else}
                    <input id="extensions_{$hooks[hook].tmodule|safetext}" name="hooks_{$hooks[hook].tmodule|safetext}" type="checkbox" value="ON" />
                    {/if}
                </div>
                {/section}
            </fieldset>
            <div class="z-buttons z-formbuttons">
                {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Extensions type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
    {else}
    <p class="z-warningmsg">{gt text="No hookable modules installed."}</p>
    {/if}
</div>
