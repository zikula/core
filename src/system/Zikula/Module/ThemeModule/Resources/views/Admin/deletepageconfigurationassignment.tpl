{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete page configuration assignment"} - {$name|safetext} - {$pcname|safetext}</h3>
</div>

<p class="alert alert-warning">{gt text="Do you really want to delete this page configuration assignment?"}</p>
<form class="form-horizontal" role="form" action="{modurl modname=Theme type=admin func=deletepageconfigurationassignment themename=$name|safetext pcname=$pcname|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class="z-btgreen" class="btn btn-success" __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="btn btn-danger" class="z-btred" href="{modurl modname=Theme type=admin func=pageconfigurations themename=$name}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}