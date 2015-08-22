{pageaddvar name='javascript' value='jquery'}
{pageaddvar name='javascript' value='web/jstree/dist/jstree.min.js'}
{pageaddvar name='stylesheet' value='web/jstree/dist/themes/default/style.min.css'}
{pageaddvar name='javascript' value='system/CategoriesModule/Resources/public/js/categories_admin_view.js'}
{pageaddvar name='javascript' value='system/CategoriesModule/Resources/public/js/categories_admin_edit.js'}
{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text='Categories list'}
</h3>

<p class="alert alert-info">{gt text='You can arrange categories list using drag and drop. New order will be saved automatically.<br />Right click on selected category to open context menu.'}</p>

<p>
    <label for="categoryTreeSearchTerm">{gt text='Quick search'}:</label>
    <input type="search" id="categoryTreeSearchTerm" value="" />
</p>

<p><a href="#" id="catExpand">{gt text='Expand all'}</a> | <a href="#" id="catCollapse">{gt text='Collapse all'}</a></p>

<div id="categoryTreeContainer">
    {$menuTxt}
</div>

<div class="modal fade" id="categoryEditModal" tabindex="-1" role="dialog" aria-labelledby="categoryEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{gt text='Close'}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="categoryEditModalLabel">{gt text='Edit category'}</h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" value="Submit" class="btn btn-primary">{gt text='Submit'}</button>
                <button type="button" value="Cancel" class="btn btn-default" data-dismiss="modal">{gt text='Cancel'}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="categoryDeleteModal" tabindex="-1" role="dialog" aria-labelledby="categoryDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{gt text='Close'}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="categoryDeleteModalLabel">{gt text='Confirmation prompt'}</h4>
            </div>
            <div class="modal-body">
                <p>{gt text='Do you really want to delete this category?'}
                <p id="deleteWithSubCatInfo"></p>
            </div>
            <div class="modal-footer">
                <button type="button" id='cat_delete' value="Delete" class="btn btn-primary">{gt text='Delete'}</button>
                <button type="button" id='cat_delete_all' value="Delete" class="btn btn-primary" style="display:none;">{gt text='Delete all sub-categories'}</button>
                <button type="button" id='cat_delete_move' value="DeleteAndMoveSubs" class="btn btn-default" style="display:none;">{gt text='Move all sub-categories'}</button>
                <button type="button" id='cat_delete_move_action' value="DeleteAndMoveSubs" class="btn btn-success" style="display:none;">{gt text='Move and delete'}</button>
                <button type="button" id='cat_cancel' value="Cancel" class="btn btn-default" data-dismiss="modal">{gt text='Cancel'}</button>
            </div>
        </div>
    </div>
</div>

{adminfooter}
