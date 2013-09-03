{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="locale" size="small"}
    <h3>{gt text="Localisation settings"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaSettingsModule" type="admin" func="updatemultilingual"}" method="post">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="mlsettings_timezone_server" type="hidden" name="mlsettings_timezone_server" value="{$timezone_server}" />
        <fieldset>
            <legend>{gt text="Language system"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="mlsettings_multilingual">{gt text="Activate multi-lingual features"}</label>
                <div class="col-lg-9">
                    <span id="mlsettings_multilingual">
                        <input id="multilingual1" type="radio" name="mlsettings_multilingual" value="1"{if $modvars.ZConfig.multilingual eq 1} checked="checked"{/if} />
                        <label for="multilingual1">{gt text="Yes"}</label>
                        <input id="multilingual0" type="radio" name="mlsettings_multilingual" value="0"{if $modvars.ZConfig.multilingual eq 0} checked="checked"{/if} />
                        <label for="multilingual0">{gt text="No"}</label>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="mlsettings_languageurl">{gt text="Add language to URL"}</label>
                <div class="col-lg-9">
                    <span id="mlsettings_languageurl">
                        <input id="languageurl0" type="radio" name="mlsettings_languageurl" value="1"{if $modvars.ZConfig.languageurl eq 1} checked="checked"{/if} />
                        <label for="languageurl0">{gt text="Always"}</label>
                        <input id="languageurl1" type="radio" name="mlsettings_languageurl" value="0"{if $modvars.ZConfig.languageurl eq 0} checked="checked"{/if} />
                        <label for="languageurl1">{gt text="Only for non-default languages"}</label>
                    </span>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Browser"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="mlsettings_language_detect">{gt text="Automatically detect language from browser settings"}</label>
                <div class="col-lg-9">
                    <span id="mlsettings_language_detect">
                        <input id="language_detect1" type="radio" name="mlsettings_language_detect" value="1"{if $modvars.ZConfig.language_detect eq 1} checked="checked"{/if} />
                        <label for="language_detect1">{gt text="Yes"}</label>
                        <input id="language_detect0" type="radio" name="mlsettings_language_detect" value="0"{if $modvars.ZConfig.language_detect eq 0} checked="checked"{/if} />
                        <label for="language_detect0">{gt text="No"}</label>
                    </span>
                </div>
            </div>
            <p class="alert alert-info help-block" data-switch="mlsettings_language_detect" data-switch-value="1">
                     {gt text="If this is set to 'Yes', Zikula try to serve the language requested by the each user's browser (if that language available and allowed by the multi-lingual settings). If users sets their personal language preference, then this setting will be overriden by their personal preference."}
            </p>
        </fieldset>
        <fieldset>
            <legend>{gt text="Server"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="mlsettings_language_i18n">{gt text="Default language to use for this site"}</label>
                <div class="col-lg-9">
                    {html_select_locales id=mlsettings_language_i18n name=mlsettings_language_i18n selected=$modvars.ZConfig.language_i18n installed=1 all=false}
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="mlsettings_timezone_offset">{gt text="Time zone for anonymous guests"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="mlsettings_timezone_offset" size="1" name="mlsettings_timezone_offset">
                        {timezoneselect selected=$modvars.ZConfig.timezone_offset}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Server time zone"}</label>
                <div class="col-lg-9">
                    <div class="form-control-static">{$timezone_server_abbr}</div>
                    <input type="hidden" name="mlsettings_timezone_server" value="{$timezone_server|default:0}" />
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Variable validation"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Allow IDN domain names"}</label>
                <div class="col-lg-9">
                    <div>
                        <input id="idnnamesyes" type="radio" name="idnnames" value="1" {if $modvars.ZConfig.idnnames == 1}checked="checked" {/if}/>
                        <label for="idnnamesyes">{gt text="Yes"}</label>
                        <input id="idnnamesno" type="radio" name="idnnames" value="0" {if $modvars.ZConfig.idnnames != 1}checked="checked" {/if}/>
                        <label for="idnnamesno">{gt text="No"}</label>
                    </div>
                    <div class="help-block z-sub z-italic">{gt text="Notice: With IDN domains, special characters are allowed in e-mail addresses and URLs."}</div>
                </div>
            </div>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
                <a class="btn btn-default" href="{modurl modname="ZikulaSettingsModule" type="admin" func="main"}" title="{gt text="Cancel"}">{img modname="core" src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}