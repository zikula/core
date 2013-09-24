{adminheader}
<h3>
    <span class="icon icon-trash"></span>
    {gt text="Delete"}
</h3>

<p class="alert alert-warning">
    {gt text="Do you really want to delete group '%s'?" tag1=$item.name|safetext}
</p>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="delete" gid=$item.gid}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Confirmation prompt"}</legend>
        
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="confirmation" value="1" />
        
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text='Delete'}">
                    {gt text="Delete"}
                </button>
                <a class="btn btn-danger" href="{modurl modname='ZikulaGroupsModule' type='admin' func='view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}