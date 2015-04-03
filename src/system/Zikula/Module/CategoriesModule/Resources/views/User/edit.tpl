{modgetinfo module='ZikulaCategoriesModule' info='all' assign='modinfo'}
{if $category}
{gt text='Edit category' assign='templatetitle'}
{else}
{gt text='Create new category' assign='templatetitle'}
{/if}

<h2 id="top">{$modinfo.displayname|safetext}</h2>

{formutil_getpassedvalue key='dr' default='0' assign='dr'}

<ul class="navbar navbar-default navbar-modulelinks navbar-modulelinks-main">
    {if ($referer)}
    <li><a class="fa fa-eye" href="{route name='zikulacategoriesmodule_user_referback'}"> {gt text='Return to referring page'}</a></li>
    {/if}
    <li><a class="fa fa-refresh" href="{route name='zikulacategoriesmodule_userform_resequence' assign='resq' dr=$dr}"> {gt text='Resequence'}</a></li>
</ul>

{insert name='getstatusmsg'}
{include file='User/list.tpl'}

<h3>{$templatetitle}</h3>

{if ($category)}
<form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_userform_edit'}" method="post" enctype="application/x-www-form-urlencoded">
{else}
<form class="form-horizontal" role="form" action="{route name="zikulacategoriesmodule_userform_newcat"}" method="post" enctype="application/x-www-form-urlencoded">
{/if}
    <fieldset>
        <legend>{gt text='Category'}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="dr" value="{$rootCat.id|safetext}" />
        <input type="hidden" name="category[parent_id]" value="{$rootCat.id|safetext}" />
        <input type="hidden" name="category[is_locked]" value="0" />
        <input type="hidden" name="category[is_leaf]" value="1" />
        {array_field assign='catID' array='category' field='id'}
        {if $catID}
        <input type="hidden" name="category[id]" value="{$category.id|safetext}" />
        <input type="hidden" name="category[path]" value="{$category.path|safetext}" />
        <input type="hidden" name="category[ipath]" value="{$category.ipath|safetext}" />
        {/if}
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_name">{gt text='Name'}<span class="required"></span></label>
            <div class="col-sm-9">
                {array_field assign='catName' array='category' field='name'}
                <input id="category_name" name="category[name]" value="{$catName|safetext}" type="text" class="form-control" size="32" maxlength="255" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_value">{gt text='Value'}</label>
            <div class="col-sm-9">
                {array_field assign='catValue' array='category' field='value'}
                <input id="category_value" name="category[value]" value="{$catValue|safetext}" type="text" class="form-control" size="16" maxlength="255" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_status">{gt text='Active'}</label>
            <div class="col-sm-9">
                {array_field assign='catStatus' array='category' field='status'}
                <input id="category_status" name="category[status]" value="A" type="checkbox"{if $catStatus eq 'A'} checked="checked"{/if} />
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Localised output'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Name'}<span class="required"></span></label>
            <div class="col-sm-9">
                {array_field assign='displayNames' array='category' field='display_name'}
                {if ($displayNames || !$catID)}
                {foreach item='language' from=$languages}
                {array_field assign='displayName' array='displayNames' field=$language}
                <div class="z-formlist">
                    <input id="category_display_name_{$language}" name="category[display_name][{$language}]" value="{$displayName}" type="text" class="form-control" size="50" maxlength="255" />
                    <label for="category_display_name_{$language}">({$language})</label>
                </div>
                {/foreach}
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Description'}</label>
            <div class="col-sm-9">
                {array_field assign='displayDescs' array='category' field='display_desc'}
                {if ($displayDescs || !$catID)}
                {foreach item='language' from=$languages}
                {array_field assign='displayDesc' array='displayDescs' field=$language}
                <div class="z-formlist">
                    <textarea class="form-control" id="category_display_desc_{$language}" name="category[display_desc][{$language}]" rows="4" cols="56">{$displayDesc}</textarea>
                    <label for="category_display_desc_{$language}">({$language})</label>
                </div>
                {/foreach}
                {/if}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Attributes'}</legend>
        {include file='editattributes.tpl'}
    </fieldset>
    {if $catID}
    <fieldset>
        <legend>{gt text='Category system information'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_id">{gt text='Internal ID'}</label>
            <div class="col-sm-9">
                <span id="category_id">{$category.id|safetext}</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_path">{gt text='Path'}</label>
            <div class="col-sm-9">
                <span id="category_path">{$category.path|safetext}</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_ipath">{gt text='I-path'}</label>
            <div class="col-sm-9">
                <span id="category_ipath">{$category.ipath|safetext}</span>
            </div>
        </div>
    </fieldset>
    {/if}
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
        {if $category}
            <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
            <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_user_edit' dr=$rootCat.id}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        {else}
            <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
        {/if}
        </div>
    </div>
</form>
