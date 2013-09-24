{adminheader}

<h3>
    <span class="icon icon-pencil"></span>
    {gt text="Edit group"}
</h3>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <input type="hidden" name="gid" value="{$item.gid}" />
    <fieldset>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="groups_name">{gt text="Name"}</label>
            <div class="col-lg-9">
                <input id="groups_name" name="name" type="text" class="form-control" size="30" maxlength="30" value="{$item.name|safetext}" required />
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
                <input id="groups_nbumax" name="nbumax" type="number" min="0" class="form-control" size="10" maxlength="10" value="{$item.nbumax}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="groups_description">{gt text="Description"}</label>
            <div class="col-lg-9">
                <textarea class="form-control" id="groups_description" name="description" rows="5">{$item.description|safetext}</textarea>
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            <button class="btn btn-success" title="{gt text="Save"}">
                {gt text="Save"}
            </button>
            <a class="btn btn-danger" href="{modurl modname='ZikulaGroupsModule' type='admin' func='view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}