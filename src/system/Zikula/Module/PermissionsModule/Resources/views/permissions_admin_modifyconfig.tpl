{gt text="Settings" assign=templatetitle}

{adminheader}
{include file="permissions_admin_header.tpl"}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{$templatetitle}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="Permissions" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <fieldset>
            <legend>{$templatetitle}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="permissions_lockadmin">{gt text="Lock main administration permission rule"}</label>
                <div class="col-lg-9">
                {if $lockadmin eq 1}
                <input id="permissions_lockadmin" name="lockadmin" type="checkbox" value="1" checked="checked" />
                {else}
                <input id="permissions_lockadmin" name="lockadmin" type="checkbox" value="1" />
                {/if}
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="permission_adminid">{gt text="ID of main administration permission rule"}</label>
                <div class="col-lg-9"><input type="text" class="form-control" name="adminid" id="permission_adminid" size="3" maxlength="3" value="{$adminid}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="permissions_filter">{gt text="Enable filtering of group permissions"}</label>
                <div class="col-lg-9">
                {if $filter eq 1}
                <input id="permissions_filter" name="filter" type="checkbox" value="1" checked="checked" />
                {else}
                <input id="permissions_filter" name="filter" type="checkbox" value="1" />
                {/if}
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="permissions_rowview">{gt text="Minimum row height for permission rules list view (in pixels)"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="permissions_rowview" name="rowview" size="1">
                    <option value="20"  {if $rowview eq 20}selected="selected"{/if}>20</option>
                    <option value="25"  {if $rowview eq 25}selected="selected"{/if}>25</option>
                    <option value="30"  {if $rowview eq 30}selected="selected"{/if}>30</option>
                    <option value="35"  {if $rowview eq 35}selected="selected"{/if}>35</option>
                    <option value="40"  {if $rowview eq 40}selected="selected"{/if}>40</option>
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="permissions_roweditheight">{gt text="Minimum row height for rule editing view (in pixels)"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="permissions_roweditheight" name="rowedit" size="1">
                    <option value="20" {if $rowedit eq 20}selected="selected"{/if}>20</option>
                    <option value="25" {if $rowedit eq 25}selected="selected"{/if}>25</option>
                    <option value="30" {if $rowedit eq 30}selected="selected"{/if}>30</option>
                    <option value="35" {if $rowedit eq 35}selected="selected"{/if}>35</option>
                    <option value="40" {if $rowedit eq 40}selected="selected"{/if}>40</option>
                </select>
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a class="btn btn-default" href="{modurl modname=Permissions type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>

    </div>
</form>
{adminfooter}
