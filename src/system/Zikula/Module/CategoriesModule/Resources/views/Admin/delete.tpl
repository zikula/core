{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Delete category'}
</h3>

<p class="alert alert-warning">
    {gt text='Do you really want to delete this category?'}<br />
    {gt text='Category'}: <strong>{$category.name}</strong>
</p>

<form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_delete'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="cid" value="{$category.id}" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            {if $numSubcats gt 0}
                <p class="alert alert-info">
                    {gt text='It contains %s direct sub-categories.' tag1=$numSubcats}
                    {gt text="Please also choose what to do with this category's sub-categories:"}
                </p>
                <div class="z-formlist">
                    <label for="subcat_action_delete" >{gt text='Delete all sub-categories'}</label>
                    <input type="radio" id="subcat_action_delete" name="subcat_action" value="delete" checked="checked" onclick="document.getElementById('subcat_move').style.visibility='hidden'" />
                </div>
                <div class="z-formlist">
                    <label for="subcat_action_move">{gt text='Move all sub-categories to next category'}</label>
                    <input type="radio" id="subcat_action_move" name="subcat_action" value="move" onclick="document.getElementById('subcat_move').style.visibility='visible'" />
                    <div id="subcat_move" style="visibility: hidden;">
                        {$categorySelector}
                    </div>
                </div>
            {else}
                <input type="hidden" name="subcat_action" id="subcat_action" value="delete" />
            {/if}
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    {button class='btn btn-success' __alt='Delete' __title='Delete' __text='Delete'}
                    <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
                </div>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}
