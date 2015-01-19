{pageaddvar name='javascript' value='jquery'}
{pageaddvar name='javascript' value='web/jstree/dist/jstree.min.js'}
{pageaddvar name='stylesheet' value='web/jstree/dist/themes/default/style.min.css'}
{pageaddvar name='javascript' value='system/Zikula/Module/CategoriesModule/Resources/public/js/categories_admin_view.js'}
{pageaddvar name='javascript' value='system/Zikula/Module/CategoriesModule/Resources/public/js/categories_admin_edit.js'}
{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text='Categories list'}
</h3>

<p class="alert alert-info">{gt text='You can arrange categories list using drag and drop - just grab page or folder icon and drag it to the new position. New order will be saved automatically.<br />Right click on selected category to open context menu.'}</p>

<p>
    <label for="categoryTreeSearchTerm">{gt text='Quick search'}:</label>
    <input type="search" id="categoryTreeSearchTerm" value="" />
</p>

<p><a href="#" id="catExpand">{gt text='Expand all'}</a> | <a href="#" id="catCollapse">{gt text='Collapse all'}</a></p>

<div id="categoryTreeContainer">
    {$menuTxt}
</div>

{adminfooter}
