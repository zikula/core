{adminheader}
<h3>
    <span class="icon icon-pencil"></span>
    {gt text="Edit module category"}
</h3>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaAdminModule" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Module category"}</legend>

        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="category[cid]" value="{$category.cid|safetext}" />
        
        <div class="form-group">
            <label class="col-lg-3 control-label" for="admin_name">{gt text="Name"}</label>
            <div class="col-lg-9">
                <input id="admin_name" name="category[name]" type="text" class="form-control" size="30" maxlength="50" required value="{$category.name|safetext}" />
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
            <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
            <a class="btn btn-danger" href="{modurl modname=ZikulaAdminModule type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            <a class="btn btn-info" href="{modurl modname=ZikulaAdminModule type=admin func=help fragment=modify}" title="{gt text="Help"}">{gt text="Help"}</a>
        </div>
    </div>
</form>
{adminfooter}
