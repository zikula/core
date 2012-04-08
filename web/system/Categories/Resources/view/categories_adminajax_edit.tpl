{if $mode == "edit"}
    {gt text="Edit category" assign="windowtitle"}
{else}
    {gt text="Create new category" assign="windowtitle"}
{/if}
<div id="categories_ajax_form_container" style="display: none;" title="{$windowtitle}">
    <form id="categories_ajax_form" class="z-form" action="#" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <input type="hidden" id="category_parent_id" name="category[parent_id]" value="{$category.parent_id}" />
            {array_field assign='catID' array='category' field='id'}
            {if ($catID)}
            <input type="hidden" id="category_id" name="category[id]" value="{$category.id}" />
            {/if}
            <div class="z-formrow">
                <label for="category_name">{gt text="Name"}{formutil_getfieldmarker objectType="category" field="name" validation=$validation}</label>
                {array_field assign='catName' array='category' field='name'}
                <input id="category_name" name="category[name]" value="{$catName|safetext}" type="text" size="32" maxlength="255" />
                {formutil_getvalidationerror objectType="category" field="name"}
            </div>
            <div class="z-formrow">
                <span class="z-label">{gt text="Parent"}</span>
                <span><strong>{category_path id=$category.parent_id field='name'}</strong></span>
            </div>
            <div class="z-formrow">
                <label for="category_is_locked">{gt text="Category is locked"}</label>
                {array_field assign='catIsLocked' array='category' field='is_locked'}
                <input type="checkbox" id="category_is_locked" name="category[is_locked]" value="1"{if ($catIsLocked)} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="category_is_leaf">{gt text="Category is a leaf node"}</label>
                {array_field assign='catIsLeaf' array='category' field='is_leaf'}
                <input type="checkbox" id="category_is_leaf" name="category[is_leaf]" value="1"{if ($catIsLeaf)} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="category_value">{gt text="Value"}</label>
                {array_field assign='catValue' array='category' field='value'}
                <input id="category_value" name="category[value]" value="{$catValue|safetext}" type="text" size="16" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="category_status">{gt text="Active"}</label>
                {array_field assign='catStatus' array='category' field='status'}
                {if $mode != "edit"} {assign var="catStatus" value="A"}{/if}
                <input id="category_status" name="category[status]" value="A" type="checkbox" {if ($catStatus == 'A')} checked="checked"{/if} />&nbsp;&nbsp;
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Localised output"}</legend>
            <div class="z-formrow">
                <label>{gt text="Name"}<span class="z-form-mandatory-flag">*</span></label>
                {array_field assign='displayNames' array='category' field='display_name'}
                {foreach item=language from=$languages}
                {array_field assign='displayName' array='displayNames' field=$language}
                <div class="z-formlist">
                    <input id="category_display_name_{$language}" name="category[display_name][{$language}]" value="{$displayName}" type="text" size="50" maxlength="255" />
                    <label for="category_display_name_{$language}">({$language})</label>
                </div>
                {/foreach}
            </div>
            <div class="z-formrow">
                <label>{gt text="Description"}</label>
                {array_field assign='displayDescs' array='category' field='display_desc'}
                {foreach item=language from=$languages}
                {array_field assign='displayDesc' array='displayDescs' field=$language}
                <div class="z-formlist">
                    <textarea id="category_display_desc_{$language}" name="category[display_desc][{$language}]" rows="4" cols="56">{$displayDesc}</textarea>
                    <label for="category_display_desc_{$language}">({$language})</label>
                </div>
                {/foreach}
            </div>
        </fieldset>
        <fieldset>
            <legend><a class="categories_collapse_control" href="#">{gt text="Attributes"}</a></legend>
            <div class="categories_collapse_details">
                {include file=categories_include_editattributes.tpl}
            </div>
        </fieldset>
        {if $mode == "edit"}
        <fieldset>
            <legend><a class="categories_collapse_control" href="#">{gt text='Meta data'}</a></legend>
            <div class="categories_collapse_details">
                <div class="z-formrow">
                    <span class="z-label">{gt text="Internal ID"}</span>
                    <span id="category_meta_id">{$category.id|safetext}</span>
                </div>
                <div class="z-formrow">
                    <span class="z-label">{gt text="Path"}</span>
                    <span id="category_path">{$category.path|safetext}</span>
                </div>
                <div class="z-formrow">
                    <span class="z-label">{gt text="I-path"}</span>
                    <span id="category_ipath">{$category.ipath|safetext}</span>
                </div>
                {usergetvar name='uname' uid=$category.cr_uid assign='crusername'}
                {usergetvar name='uname' uid=$category.lu_uid assign='luusername'}
                <ul class="z-formnote">
                    <li>{gt text='Created by %s.' domain='zikula' tag1=$crusername}</li>
                    <li>{gt text='Created on %s.' domain='zikula' tag1=$category.cr_date|date_format}</li>
                    <li>{gt text='Last edited by %s.' domain='zikula' tag1=$luusername}</li>
                    <li>{gt text='Updated on %s.' domain='zikula' tag1=$category.lu_date|date_format}</li>
                </ul>
            </div>
        </fieldset>
        {/if}
    </form>
</div>
