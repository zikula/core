<div class="z-formrow">
    <label for="search_displaySearchBtn">{gt text="Show 'Search now' button" domain='zikula'}</label>
    {if $searchvars.displaySearchBtn eq 1}
    <input id="search_displaySearchBtn" type="checkbox" name="displaySearchBtn" value="1" checked="checked" />
    {else}
    <input id="search_displaySearchBtn" type="checkbox" name="displaySearchBtn" value="1" />
    {/if}
</div>
<div class="z-formrow">
    <label>{gt text="Search options" domain='zikula'}</label>
    {section name=searchmodules loop=$searchmodules}
    <div class="z-formlist">{$searchmodules[searchmodules].module}</div>
    {/section}
</div>