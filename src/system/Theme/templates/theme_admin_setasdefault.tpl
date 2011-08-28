{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Theme confirmation prompt"}</h3>
</div>

<p class="z-warningmsg">{gt text="Do you really want to set '%s' as the active theme for all site users?" tag1=$themename|safetext}</p>
<form class="z-form" action="{modurl modname="Theme" type="admin" func="setasdefault"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
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
                {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Accept" __title="Accept" __text="Accept"}
                <a class="z-btred" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}