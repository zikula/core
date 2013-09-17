{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete theme %s" tag1=$name|safetext}</h3>
</div>

<p class="alert alert-warning">{gt text="Do you really want to delete this theme?"}</p>
<form class="form-horizontal" role="form" action="{modurl modname=Theme type=admin func=delete themename=$name|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" name="confirmation" value="1" />

    <fieldset>
        <legend>{gt text="Confirmation prompt"}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="deletefiles">{gt text="Also delete theme files, if possible"}</label>
            <div class="col-lg-9">
                <input type="checkbox" id="deletefiles" name="deletefiles" value="1" />
            </div>
            <div class="alert alert-info">{gt text="Please delete the Theme folder before pressing OK or the Theme will not be deleted."}</div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {button class="z-btgreen" class="btn btn-success" __alt="Delete" __title="Delete" __text="Delete"}
            <a class="btn btn-danger" class="z-btred" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}