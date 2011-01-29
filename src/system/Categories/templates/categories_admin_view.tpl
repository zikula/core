{ajaxheader filename='categories_admin_view.js' ui=true}
{include file="categories_admin_menu.tpl"}
<input type="hidden" id="categoriesauthid" name="authid" value="{insert name='generateauthkey' module='Categories'}" />
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="view" size="large"}</div>
    <h2>{gt text="Categories list"}</h2>
    {$menuTxt}
</div>



