<div class="form-group">
    <label class="col-sm-3 control-label" for="search_displaySearchBtn">{gt text="Show 'Search now' button" domain='zikula'}</label>
    <div class="col-sm-9">
        <input id="search_displaySearchBtn" type="checkbox" name="displaySearchBtn" value="1"{if $searchvars.displaySearchBtn eq 1} checked="checked"{/if} />
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">{gt text='Search options' domain='zikula'}</label>
    <div class="col-sm-9">
    {section name='searchmodules' loop=$searchmodules}
        <div class="z-formlist">{$searchmodules[searchmodules].module}</div>
    {/section}
</div>
