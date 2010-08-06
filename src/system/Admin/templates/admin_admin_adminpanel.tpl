{$menu}
<div id="z-admincontainer" class="z-admincontainer">
    <h2>{$category.catname|safetext}</h2>
    <div class="z-admincategorydescription">{$category.description|safetext}</div>

    {if empty($adminlinks)}
        <p class="z-bold z-center">{gt text="There are currently no modules in this category."}</p>
    {else}
        {if $modvars.moduledescription eq 1}
        {include file="admin_admin_adminpanel_inc1.tpl"}
        {else}
        {include file="admin_admin_adminpanel_inc2.tpl"}
        {/if}
    {/if}
</div>
