{adminheader}
{include file="theme_admin_modifymenu.tpl"}

<h4>{gt text="Settings for %s" tag1=$themename}</h4>

<form class="z-form" action="{modurl modname="Theme" type="admin" func="updatesettings"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="theme_displayname">{gt text="Display name"}</label>
                <input id="theme_displayname" type="text" name="themeinfo[displayname]" size="30" maxlength="64" value="{$themeinfo.displayname}" />
            </div>
            <div class="z-formrow">
                <label for="theme_usertheme">{gt text="User theme"}</label>
                <div>
                    <input id="theme_usertheme" type="checkbox" name="themeinfo[user]" value="1"{if $themeinfo.user} checked="checked"{/if} />
                    <span class="z-sub z-formnote">{gt text="Notice: This category is for 'browser-oriented' themes that can be selected by users for their sessions on the site."}</span>
                </div>
            </div>
            <div class="z-formrow">
                <label for="theme_systemtheme">{gt text="System theme"}</label>
                <div>
                    <input id="theme_systemtheme" type="checkbox" name="themeinfo[system]" value="1"{if $themeinfo.system} checked="checked"{/if} />
                    <span class="z-sub z-formnote">{gt text="Notice: This category is for themes used to deliver back-end services (such as RSS feeds, etc.)."}</span>
                </div>
            </div>
            <div class="z-formrow">
                <label for="theme_admintheme">{gt text="Admin panel theme"}</label>
                <div>
                    <input id="theme_admintheme" type="checkbox" name="themeinfo[admin]" value="1"{if $themeinfo.admin} checked="checked"{/if} />
                    <span class="z-sub z-formnote">{gt text="Notice: This category is for themes used to display the site admin panel."}</span>
                </div>
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}