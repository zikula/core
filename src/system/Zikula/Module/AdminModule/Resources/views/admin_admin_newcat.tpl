{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{gt text="Create new module category"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaAdminModule" type="admin" func="create"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="New module category"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_name">{gt text="Name"}</label>
                <div class="col-lg-9">
                <input id="admin_name" name="category[name]" type="text" class="form-control" size="30" maxlength="50" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_description">{gt text="Description"}</label>
                <div class="col-lg-9">
                <textarea class="form-control" id="admin_description" name="category[description]" cols="50" rows="10"></textarea>
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a class="btn btn-default" href="{modurl modname=ZikulaAdminModule type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                <a class="btn btn-default" class="z-btblue" href="{modurl modname=ZikulaAdminModule type=admin func=help fragment=new}" title="{gt text="Help"}">{img modname=core src=agt_support.png set=icons/extrasmall __alt="Help" __title="Help"} {gt text="Help"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}