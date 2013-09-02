{* Do not allow editing of primaryadmingroup. For now it is read-only. *}
{adminheader}
{include file="Admin/header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_itemsperpage">{gt text="Items per page"}</label>
                <div class="col-lg-9">
                <input id="groups_itemsperpage" type="text" class="form-control" name="itemsperpage" size="3" value="{$modvars.ZikulaGroupsModule.itemsperpage|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_defaultgroup">{gt text="Initial user group"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="groups_defaultgroup" name="defaultgroup">
                    {html_options options=$groups selected=$modvars.ZikulaGroupsModule.defaultgroup}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_hideclosed">{gt text="Hide closed groups"}</label>
                <div class="col-lg-9">
                <input id="groups_hideclosed" name="hideclosed" type="checkbox"{if $modvars.ZikulaGroupsModule.hideclosed eq 1} checked="checked"{/if} />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_mailwarning">{gt text="Receive e-mail alert when there are new applicants"}</label>
                <div class="col-lg-9">
                <input id="groups_mailwarning" name="mailwarning" type="checkbox"{if $modvars.ZikulaGroupsModule.mailwarning eq 1} checked="checked"{/if} />
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a class="btn btn-default" href="{modurl modname='ZikulaGroupsModule' type='admin' func='view'}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
