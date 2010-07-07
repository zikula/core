{*  $Id$  *}
{include file='theme_admin_menu.tpl'}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=xedit.gif set=icons/large __alt="Set default theme"}</div>
    <h2>{gt text="Theme confirmation prompt"}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to set '%s' as the active theme for all site users?" tag1=$themename}</p>
    <form class="z-form" action="{modurl modname="Theme" type="admin" func="setasdefault"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module=Theme}" />
            <input type="hidden" name="themename" value="{$themename|safetext}" />
            <input type="hidden" name="confirmation" value="1" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                {if $theme_change}
                <div class="z-formrow">
                    <label for="themeswitcher_theme_change">{gt text="Override users' theme settings"}</label>
                    <input id="themeswitcher_theme_change" name="resetuserselected" type="checkbox" value="1"  />
                </div>
                {/if}
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.gif set=icons/extrasmall __alt="Accept" __title="Accept" __text="Accept"}
                    <a class="z-btred" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
