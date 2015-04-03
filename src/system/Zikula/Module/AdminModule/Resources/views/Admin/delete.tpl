{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Delete module category'}
</h3>

<p class="alert alert-warning">{gt text="Do you really want to delete module category '%s'?" tag1=$category.name|safetext}</p>
<form class="form-horizontal" role="form" action="{route name='zikulaadminmodule_admin_delete'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='Confirmation prompt'}</legend>
        
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="cid" value="{$category.cid|safetext}" />
        
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Delete'}">{gt text='Delete'}</button>
                <a class="btn btn-danger" href="{route name='zikulaadminmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}
