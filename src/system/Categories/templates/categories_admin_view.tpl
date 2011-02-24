{ajaxheader filename='categories_admin_view.js' ui=true}
{pageaddvar name='javascript' value='system/Categories/javascript/categories_admin_edit.js'}
{include file="categories_admin_menu.tpl"}
<input type="hidden" id="categoriesauthid" name="authid" value="{insert name='generateauthkey' module='Categories'}" />
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type='view' size='large'}</div>
    <h2>{gt text='Categories list'}</h2>
    <p class="z-informationmsg">{gt text='You can arrange categories list using drag and drop - just grab page or folder icon and drag it to the new position. New order will be saved automatically.<br />Right click on selected category to open context menu.'}</p>
    <p><a href="#" id="catExpand">{gt text='Expand all'}</a> | <a href="#" id="catCollapse">{gt text='Collapse all'}</a></p>
    {$menuTxt}
</div>



