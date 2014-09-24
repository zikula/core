{adminheader}
<h3>
    <span class="fa fa-plus"></span>
    {gt text="Create new block position"}
</h3>

<p class="alert alert-info">{gt text="After create this block position, you will be able to assign some blocks for it, and adjust the order you want them to be displayed."}</p>

<form class="form-horizontal" role="form" action="{route name="zikulablocksmodule_admin_createposition"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="New block position"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_positionname">{gt text="Name"}</label>
                <div class="col-lg-9">
                <input type="text" class="form-control" id="blocks_positionname" name="position[name]" value="{$name|safetext}" size="50" maxlength="255" />
                <em class="help-block sub">{gt text="Characters allowed: a-z, A-Z, 0-9, dash (-) and underscore (_)."}</em>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_positiondescription">{gt text="Description"}</label>
                <div class="col-lg-9">
                <textarea class="form-control" name="position[description]" id="blocks_positiondescription" rows="5" cols="30"></textarea>
            </div>
        </div>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{route name="zikulablocksmodule_admin_view"}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}