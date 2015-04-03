{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Delete registry entry'}
</h3>

<p class="alert alert-warning">
    {gt text='Do you really want to delete this registry entry?'}<br />
    {gt text='Module'}: <strong>{$data.modname}</strong><br />
    {gt text='Entity'}: <strong>{$data.entityname}</strong><br />
    {gt text='Property name'}: <strong>{$data.property}</strong>
</p>

<form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_editregistry' mode='delete'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="id" value="{$data.id}" />
        <input type="hidden" name="mode" value="delete" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    {button class='btn btn-success' __alt='Delete' __title='Delete' __text='Delete'}
                    <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_editregistry'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
                </div>
            </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}
