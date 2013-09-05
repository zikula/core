{ajaxheader modname=ZikulaAdminModule filename=admin_admin_modifyconfig.js noscriptaculous=true effects=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaAdminModule" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_ignoreinstallercheck">{gt text="Ignore check for installer"}</label>
                <div class="col-lg-9">
                    {if $modvars.ZikulaAdminModule.ignoreinstallercheck eq 1}
                    <input id="admin_ignoreinstallercheck" name="modvars[ignoreinstallercheck]" type="checkbox" value="1" checked="checked" />
                    {else}
                    <input id="admin_ignoreinstallercheck" name="modvars[ignoreinstallercheck]" type="checkbox" value="1" />
                    {/if}
                    <div id="admin_ignoreinstallercheck_warning">
                        <div class="alert alert-warning">{gt text="Warning! Only enable the above option if this site is isolated from the Internet, otherwise security could be endangered if you omit to remove the Installer script from the site root and are not prompted to do so."}</div>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Display settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_graphic">{gt text="Display icons"}</label>
                <div class="col-lg-9">
                    {if $modvars.ZikulaAdminModule.admingraphic eq 1}
                    <input id="admin_graphic" name="modvars[admingraphic]" type="checkbox" value="1" checked="checked" />
                    {else}
                    <input id="admin_graphic" name="modvars[admingraphic]" type="checkbox" value="1" />
                    {/if}
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_displaynametype">{gt text="Form of display for module names"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="admin_displaynametype" name="modvars[displaynametype]">
                        <option value="1" {if $modvars.ZikulaAdminModule.displaynametype eq 1}selected="selected"{/if}>{gt text="Display name"}</option>
                        <option value="2" {if $modvars.ZikulaAdminModule.displaynametype eq 2}selected="selected"{/if}>{gt text="Internal name"}</option>
                        <option value="3" {if $modvars.ZikulaAdminModule.displaynametype eq 3}selected="selected"{/if}>{gt text="Show both internal name and display name"}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_itemsperpage">{gt text="Modules per page in module categories list"}</label>
                <div class="col-lg-9">
                    <input id="admin_itemsperpage" name="modvars[itemsperpage]" type="text" class="form-control" size="3" maxlength="3" value="{$modvars.ZikulaAdminModule.itemsperpage|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_modulesperrow">{gt text="Modules per row in admin panel"}</label>
                <div class="col-lg-9">
                    <input id="admin_modulesperrow" name="modvars[modulesperrow]" type="text" class="form-control" size="3" maxlength="3" value="{$modvars.ZikulaAdminModule.modulesperrow|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admintheme">{gt text="Theme to use"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="admintheme" name="modvars[admintheme]">
                        <option value="">{gt text="Use site's theme"}</option>
                        {html_select_themes state='ThemeUtil::STATE_ACTIVE'|const filter='ThemeUtil::FILTER_ADMIN'|const selected=$modvars.ZikulaAdminModule.admintheme}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_startcategory">{gt text="Category initially selected"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="admin_startcategory" name="modvars[startcategory]">
                        {section name=category loop=$categories}
                        {if  $modvars.ZikulaAdminModule.startcategory eq $categories[category].cid}
                        <option value="{$categories[category].cid|safetext}" selected="selected">{$categories[category].name|safetext}</option>
                        {else}
                        <option value="{$categories[category].cid|safetext}">{$categories[category].name|safetext}</option>
                        {/if}
                        {/section}
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Modules categorisation"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_defaultcategory">{gt text="Default category for newly-added modules"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="admin_defaultcategory" name="modvars[defaultcategory]">
                        {section name=category loop=$categories}
                        {if  $modvars.ZikulaAdminModule.defaultcategory eq $categories[category].cid}
                        <option value="{$categories[category].cid|safetext}" selected="selected">{$categories[category].name|safetext}</option>
                        {else}
                        <option value="{$categories[category].cid|safetext}">{$categories[category].name|safetext}</option>
                        {/if}
                        {/section}
                    </select>
                </div>
            </div>
            {section name=modulecategory loop=$modulecategories}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_{$modulecategories[modulecategory].name}">{$modulecategories[modulecategory].displayname}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="admin_{$modulecategories[modulecategory].name}" name="adminmods[{$modulecategories[modulecategory].name|safetext}]">
                        {section name=category loop=$categories}
                        {if  $modulecategories[modulecategory].category eq $categories[category].cid}
                        <option value="{$categories[category].cid|safetext}" selected="selected">{$categories[category].name|safetext}</option>
                        {else}
                        <option value="{$categories[category].cid|safetext}">{$categories[category].name|safetext}</option>
                        {/if}
                        {/section}
                    </select>
                </div>
            </div>
            {/section}
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">
                    {gt text="Save"}
                </button>
                <a class="btn btn-danger" href="{modurl modname=ZikulaAdminModule type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                <a class="btn btn-info" href="{modurl modname=ZikulaAdminModule type=admin func=help fragment=modifyconfig}" title="{gt text="Help"}">{gt text="Help"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}