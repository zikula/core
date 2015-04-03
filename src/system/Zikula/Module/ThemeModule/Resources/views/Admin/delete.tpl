{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text="Delete theme %s" tag1=$name|safetext}
</h3>

<p class="alert alert-warning">{gt text="Do you really want to delete this theme?"}</p>
<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_delete' themename=$name|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Confirmation prompt"}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <div class="form-group">
            <label class="col-sm-3 control-label" for="deletefiles">{gt text="Also delete theme files, if possible"}</label>
            <div class="col-sm-9">
                <input type="checkbox" id="deletefiles" name="deletefiles" value="1" />
            </div>
            <div class="alert alert-info">{gt text="Please delete the Theme folder before pressing OK or the Theme will not be deleted."}</div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            {button class="btn btn-success" __alt="Delete" __title="Delete" __text="Delete"}
            <a class="btn btn-danger" href="{route name='zikulathememodule_admin_view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}