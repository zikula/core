{adminheader}
{include file="Admin/header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit group"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$item.gid}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_name">{gt text="Name"}</label>
                <div class="col-lg-9">
                <input id="groups_name" name="name" type="text" class="form-control" size="30" maxlength="30" value="{$item.name|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_gtype">{gt text="Type"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="groups_gtype" name="gtype">
                    {html_options options=$grouptype selected=$item.gtype}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_state">{gt text="State"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="groups_state" name="state">
                    {html_options options=$groupstate selected=$item.state}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_nbumax">{gt text="Maximum membership"}</label>
                <div class="col-lg-9">
                <input id="groups_nbumax" name="nbumax" type="text" class="form-control" size="10" maxlength="10" value="{$item.nbumax}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_description">{gt text="Description"}</label>
                <div class="col-lg-9">
                <textarea class="form-control" id="groups_description" name="description" cols="50" rows="5">{$item.description|safetext}</textarea>
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