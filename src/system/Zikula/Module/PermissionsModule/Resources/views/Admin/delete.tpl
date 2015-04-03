{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Delete permission rule'}
</h3>

<p class="alert alert-warning">{gt text='Do you really want to delete this permission rule?'}</p>
<form class="form-horizontal" role="form" action="{route name='zikulapermissionsmodule_admin_delete' pid=$pid}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="pid" value="{$pid|safetext}" />
        <input type="hidden" name="permgrp" value="{$permgrp|safetext}" />
        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    {button class='btn btn-success' __alt='Delete' __title='Delete' __text='Delete'}
                    <a class="btn btn-danger" href="{route name='zikulapermissionsmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
                </div>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}
