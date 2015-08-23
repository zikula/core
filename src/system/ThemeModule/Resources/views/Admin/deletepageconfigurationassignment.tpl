{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text="Delete page configuration assignment"} - {$name|safetext} - {$pcname|safetext}
</h3>

<p class="alert alert-warning">{gt text="Do you really want to delete this page configuration assignment?"}</p>
<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_deletepageconfigurationassignment' themename=$name|safetext pcname=$pcname|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Confirmation prompt"}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Delete'}">
                    {gt text='Delete'}
                </button>
                <a class="btn btn-danger" href="{mroute name='zikulathememodule_admin_pageconfigurations' themename=$name}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}