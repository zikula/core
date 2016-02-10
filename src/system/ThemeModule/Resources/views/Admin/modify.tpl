{adminheader}
{include file="Admin/modifymenu.tpl"}

<h4>{gt text="Settings for %s" tag1=$themename}</h4>

<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_updatesettings'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_displayname">{gt text="Display name"}</label>
                <div class="col-sm-9">
                <input id="theme_displayname" type="text" class="form-control" name="themeinfo[displayname]" size="30" maxlength="64" value="{$themeinfo.displayname}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_usertheme">{gt text="User theme"}</label>
                <div class="col-sm-9">
                <div>
                    <input id="theme_usertheme" type="checkbox" name="themeinfo[user]" value="1"{if $themeinfo.user} checked="checked"{/if} />
                    <span class="sub help-block">{gt text="Notice: This category is for 'browser-oriented' themes that can be selected by users for their sessions on the site."}</span>
                </div>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_systemtheme">{gt text="System theme"}</label>
                <div class="col-sm-9">
                <div>
                    <input id="theme_systemtheme" type="checkbox" name="themeinfo[system]" value="1"{if $themeinfo.system} checked="checked"{/if} />
                    <span class="sub help-block">{gt text="Notice: This category is for themes used to deliver back-end services (such as RSS feeds, etc.)."}</span>
                </div>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_admintheme">{gt text="Admin panel theme"}</label>
                <div class="col-sm-9">
                <div>
                    <input id="theme_admintheme" type="checkbox" name="themeinfo[admin]" value="1"{if $themeinfo.admin} checked="checked"{/if} />
                    <span class="sub help-block">{gt text="Notice: This category is for themes used to display the site admin panel."}</span>
                </div>
            </div>
        </div>
        </fieldset>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{route name='zikulathememodule_theme_view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}