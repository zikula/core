{ajaxheader filename='categories_admin_view.js'}
{gt text="Categories list" assign=templatetitle}
{include file="categories_admin_menu.htm"}
<input type="hidden" id="categoriesauthid" name="authid" value="{insert name='generateauthkey' module='Categories'}" />
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="View" }</div>
    <h2>{$templatetitle}</h2>
    {$menuTxt}
</div>
