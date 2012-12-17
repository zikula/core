{ajaxheader modname=Admin filename=admin_admin_modifyconfig.js noscriptaculous=true effects=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Admin" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="admin_ignoreinstallercheck">{gt text="Ignore check for installer"}</label>
                {if $modvars.Admin.ignoreinstallercheck eq 1}
                <input id="admin_ignoreinstallercheck" name="modvars[ignoreinstallercheck]" type="checkbox" value="1" checked="checked" />
                {else}
                <input id="admin_ignoreinstallercheck" name="modvars[ignoreinstallercheck]" type="checkbox" value="1" />
                {/if}
                <div id="admin_ignoreinstallercheck_warning">
                    <div class="z-warningmsg">{gt text="Warning! Only enable the above option if this site is isolated from the Internet, otherwise security could be endangered if you omit to remove the Installer script from the site root and are not prompted to do so."}</div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Display settings"}</legend>
            <div class="z-formrow">
                <label for="admin_graphic">{gt text="Display icons"}</label>
                {if $modvars.Admin.admingraphic eq 1}
                <input id="admin_graphic" name="modvars[admingraphic]" type="checkbox" value="1" checked="checked" />
                {else}
                <input id="admin_graphic" name="modvars[admingraphic]" type="checkbox" value="1" />
                {/if}
            </div>
            <div class="z-formrow">
                <label for="admin_displaynametype">{gt text="Form of display for module names"}</label>
                <select id="admin_displaynametype" name="modvars[displaynametype]">
                    <option value="1" {if $modvars.Admin.displaynametype eq 1}selected="selected"{/if}>{gt text="Display name"}</option>
                    <option value="2" {if $modvars.Admin.displaynametype eq 2}selected="selected"{/if}>{gt text="Internal name"}</option>
                    <option value="3" {if $modvars.Admin.displaynametype eq 3}selected="selected"{/if}>{gt text="Show both internal name and display name"}</option>
                </select>
            </div>
            <div class="z-formrow">
                <label for="admin_itemsperpage">{gt text="Modules per page in module categories list"}</label>
                <input id="admin_itemsperpage" name="modvars[itemsperpage]" type="text" size="3" maxlength="3" value="{$modvars.Admin.itemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="admin_modulesperrow">{gt text="Modules per row in admin panel"}</label>
                <input id="admin_modulesperrow" name="modvars[modulesperrow]" type="text" size="3" maxlength="3" value="{$modvars.Admin.modulesperrow|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="admintheme">{gt text="Theme to use"}</label>
                <select id="admintheme" name="modvars[admintheme]">
                    <option value="">{gt text="Use site's theme"}</option>
                    {html_select_themes state='ThemeUtil::STATE_ACTIVE'|const filter='ThemeUtil::FILTER_ADMIN'|const selected=$modvars.Admin.admintheme}
                </select>
            </div>
            <div class="z-formrow">
                <label for="admin_startcategory">{gt text="Category initially selected"}</label>
                <select id="admin_startcategory" name="modvars[startcategory]">
                    {section name=category loop=$categories}
                    {if  $modvars.Admin.startcategory eq $categories[category].cid}
                    <option value="{$categories[category].cid|safetext}" selected="selected">{$categories[category].name|safetext}</option>
                    {else}
                    <option value="{$categories[category].cid|safetext}">{$categories[category].name|safetext}</option>
                    {/if}
                    {/section}
                </select>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Modules categorisation"}</legend>
            <div class="z-formrow">
                <label for="admin_defaultcategory">{gt text="Default category for newly-added modules"}</label>
                <select id="admin_defaultcategory" name="modvars[defaultcategory]">
                    {section name=category loop=$categories}
                    {if  $modvars.Admin.defaultcategory eq $categories[category].cid}
                    <option value="{$categories[category].cid|safetext}" selected="selected">{$categories[category].name|safetext}</option>
                    {else}
                    <option value="{$categories[category].cid|safetext}">{$categories[category].name|safetext}</option>
                    {/if}
                    {/section}
                </select>
            </div>
            {section name=modulecategory loop=$modulecategories}
            <div class="z-formrow">
                <label for="admin_{$modulecategories[modulecategory].name}">{$modulecategories[modulecategory].displayname}</label>
                <select id="admin_{$modulecategories[modulecategory].name}" name="adminmods[{$modulecategories[modulecategory].name|safetext}]">
                    {section name=category loop=$categories}
                    {if  $modulecategories[modulecategory].category eq $categories[category].cid}
                    <option value="{$categories[category].cid|safetext}" selected="selected">{$categories[category].name|safetext}</option>
                    {else}
                    <option value="{$categories[category].cid|safetext}">{$categories[category].name|safetext}</option>
                    {/if}
                    {/section}
                </select>
            </div>
            {/section}
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Admin type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            <a class="z-btblue" href="{modurl modname=Admin type=admin func=help fragment=modifyconfig}" title="{gt text="Help"}">{img modname=core src=agt_support.png set=icons/extrasmall __alt="Help" __title="Help"} {gt text="Help"}</a>
        </div>
    </div>
</form>
{adminfooter}