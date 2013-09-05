{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit module category"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaAdminModule" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="category[cid]" value="{$category.cid|safetext}" />
        <fieldset>
            <legend>{gt text="Module category"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_name">{gt text="Name"}</label>
                <div class="col-lg-9">
                <input id="admin_name" name="category[name]" type="text" class="form-control" size="30" maxlength="50" value="{$category.name|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="admin_description">{gt text="Description"}</label>
                <div class="col-lg-9">
                <textarea class="form-control" id="admin_description" name="category[description]" cols="50" rows="10">{$category.description|safetext}</textarea>
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-default" type="submit" name="submit" title="{gt text="Save"}"><span title="{gt text="Save"}" alt="{gt text="Save"}" class="glyphicon glyphicon16 glyphicon-ok glyphicon-green"></span> Save</button>
                <a class="btn btn-default" href="{modurl modname=ZikulaAdminModule type=admin func=view}" title="{gt text="Cancel"}"><span title="{gt text="Cancel"}" alt="{gt text="Cancel"}" class="glyphicon glyphicon16 glyphicon-remove glyphicon-red"></span> {gt text="Cancel"}</a>
                <a class="btn btn-default" class="z-btblue" href="{modurl modname=ZikulaAdminModule type=admin func=help fragment=modify}" title="{gt text="Help"}"><span title="{gt text="Help"}" alt="{gt text="Help"}" class="glyphicon glyphicon16 glyphicon-question-sign glyphicon-bluelight"></span> {gt text="Help"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
