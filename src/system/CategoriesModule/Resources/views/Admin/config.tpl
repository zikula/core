{adminheader}
<h3>
    <span class="fa fa-refresh"></span>
    {gt text='Rebuild paths'}
</h3>

<p class="alert alert-warning">{gt text='Are you sure you want to rebuild all the internal paths for categories?'}&nbsp;{gt text='Warning! If you have a large number of categories then this action may time out, or may exceed the memory limit configured within your PHP installation.'}</p>

<form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_rebuildpaths'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='Confirmation prompt'}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                {button class='btn btn-success' __alt='Rebuild paths' __title='Rebuild paths' __text='Rebuild paths'}
                <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_index'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}
