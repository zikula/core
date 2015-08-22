{pageaddvar name='javascript' value='system/CategoriesModule/Resources/public/js/categories_admin_edit.js'}
{adminheader}
{if $mode eq 'edit'}
    <h3>
        <span class="fa fa-pencil"></span>
        {gt text='Edit category'}
    </h3>
    <form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_edit'}" method="post" enctype="application/x-www-form-urlencoded">
{else}
    <h3>
        <span class="fa fa-plus"></span>
        {gt text='Create new category'}
    </h3>
    <form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_newcat'}" method="post" enctype="application/x-www-form-urlencoded">
{/if}
    <fieldset>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        {array_field assign='catID' array='category' field='id'}
        {if $catID}
        <input type="hidden" id="category_id" name="category[id]" value="{$category.id}" />
        {/if}
        <legend>{gt text='General settings'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_name">{gt text='Name'}<span class="required"></span></label>
            <div class="col-sm-9">
                {array_field assign='catName' array='category' field='name'}
                <input id="category_name" name="category[name]" value="{$catName|safetext}" type="text" class="form-control" size="32" maxlength="255" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text="Parent"}</label>
            <div class="col-sm-9">
                {if $catID ne 1}
                {$categorySelector}
                {else}
                <span><strong>{gt text='No parent category.'}</strong></span>
                <input type="hidden" id="category_parent_id" name="category[parent_id]" value="{$category.parent_id}" />
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_is_locked">{gt text='Category is locked'}</label>
            <div class="col-sm-9">
                {array_field assign='catIsLocked' array='category' field='is_locked'}
                <input type="checkbox" id="category_is_locked" name="category[is_locked]" value="1"{if $catIsLocked} checked="checked"{/if} />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="category_is_leaf">{gt text='Category is a leaf node'}</label>
            <div class="col-sm-9">
                {array_field assign='catIsLeaf' array='category' field='is_leaf'}
                <input type="checkbox" id="category_is_leaf" name="category[is_leaf]" value="1"{if $catIsLeaf} checked="checked"{/if} />
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
                {if $mode ne 'edit'} {assign var='catStatus' value='A'}{/if}
                <input id="category_status" name="category[status]" value="A" type="checkbox"{if $catStatus eq 'A'} checked="checked"{/if} />&nbsp;&nbsp;
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Localised output'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Name'}<span class="required"></span></label>
            <div class="col-sm-9">
                {array_field assign='displayNames' array='category' field='display_name'}
                {foreach item='language' from=$languages}
                    {array_field assign='displayName' array='displayNames' field=$language}
                    <div class="z-formlist">
                        <input id="category_display_name_{$language}" name="category[display_name][{$language}]" value="{$displayName}" type="text" class="form-control" size="50" maxlength="255" />
                        <label for="category_display_name_{$language}">({$language})</label>
                    </div>
                {/foreach}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Description'}</label>
            <div class="col-sm-9">
                {array_field assign='displayDescs' array='category' field='display_desc'}
                {foreach item='language' from=$languages}
                    {array_field assign='displayDesc' array='displayDescs' field=$language}
                    <div class="z-formlist">
                        <textarea class="form-control" id="category_display_desc_{$language}" name="category[display_desc][{$language}]" rows="4" cols="56">{$displayDesc}</textarea>
                        <label for="category_display_desc_{$language}">({$language})</label>
                    </div>
                {/foreach}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><a href="#category-attributes" data-toggle="collapse">{gt text='Attributes'} <i class="fa fa-expand"></i></a></legend>
        <div class="collapse" id="category-attributes">
            {include file='editattributes.tpl'}
        </div>
    </fieldset>
    {if $mode eq 'edit'}
    <fieldset>
        <legend><a href="#category-metadata" data-toggle="collapse">{gt text='Meta data'} <i class="fa fa-expand"></i></a></legend>
        <div class="collapse" id="category-metadata">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="category_meta_id">{gt text='Internal ID'}</label>
                <div class="col-sm-9">
                    <div class="form-control-static">
                        <span id="category_meta_id">{$category.id|safetext}</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="category_path">{gt text='Path'}</label>
                <div class="col-sm-9">
                    <div class="form-control-static">
                        <span id="category_path">{$category.path|safetext}</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="category_ipath">{gt text='I-path'}</label>
                <div class="col-sm-9">
                    <div class="form-control-static">
                        <span id="category_ipath">{$category.ipath|safetext}</span>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
    {/if}
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
        {if $mode eq 'edit'}
            <button class="btn btn-success" name="category_submit" value="update" title="{gt text='Save'}">{gt text='Save'}</button>
            <button class="btn btn-default" name="category_copy" value="copy" title="{gt text='Copy'}">{gt text='Copy'}</button>
            <button class="btn btn-default" name="category_move" value="move" title="{gt text='Move'}">{gt text='Move'}</button>
            <button class="btn btn-default" name="category_delete" value="delete" title="{gt text='Delete'}">{gt text='Delete'}</button>
        {if !$category.is_leaf && $haveSubcategories && $haveLeafSubcategories}
            <button class="btn btn-default" name="category_user_edit" value="edit" title="{gt text='Edit'}">{gt text='Edit'}</button>
        {/if}
            <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        {else}
            <button class="btn btn-success" name="category_submit" value="add" title="{gt text='Save'}">{gt text='Save'}</button>
            <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        {/if}
        </div>
    </div>
</form>
{adminfooter}
