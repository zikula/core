{adminheader}
<h3>
    <span class="fa fa-scissors"></span>
    {gt text='Move category'}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_move'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="cid" value="{$category.id}" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='Category'}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='Name'}</label>
                <div class="col-sm-9">
                    <span>{$category.name}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='Path'}</label>
                <div class="col-sm-9">
                    <span>{$category.path}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="subcat_move">{gt text='Move all sub-categories to next category'}</label>
                <div class="col-sm-9">
                    {$categorySelector}
                </div>
            </div>
        </fieldset>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                {button class='btn btn-success' __alt='Move' __title='Move' __text='Move'}
                <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_index'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}