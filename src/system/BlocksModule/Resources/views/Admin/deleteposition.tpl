{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Delete block position'}
</h3>

<p class="alert alert-warning">{gt text="Do you really want to delete block position '%s'?" tag1=$position.name|safetext}</p>

<form class="form-horizontal" role="form" action="{route name='zikulablocksmodule_admin_deleteposition'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='Confirmation prompt'}</legend>
        
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="pid" value="{$position.pid|safetext}" />

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                {button class='btn btn-success' __alt='Delete' __title='Delete' __text='Delete'}
                <a class="btn btn-danger" href="{route name='zikulablocksmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
        </fieldset>
</form>
{adminfooter}
