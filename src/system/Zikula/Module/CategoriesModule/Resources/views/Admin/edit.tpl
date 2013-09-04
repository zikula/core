{ajaxheader modname='ZikulaCategoriesModule' filename='categories_admin_edit.js'}
{adminheader}
{if $mode == "edit"}
    <div id="top" class="z-admin-content-pagetitle">
        {icon type="edit" size="small"}
        <h3>{gt text="Edit category"}</h3>
    </div>
    <form class="form-horizontal" role="form" action="{modurl modname="ZikulaCategoriesModule" type="adminform" func="edit"}" method="post" enctype="application/x-www-form-urlencoded">
{else}
    <div id="top" class="z-admin-content-pagetitle">
        {icon type="new" size="small"}
        <h3>{gt text="Create new category"}</h3>
    </div>
    <form class="form-horizontal" role="form" action="{modurl modname="ZikulaCategoriesModule" type="adminform" func="newcat"}" method="post" enctype="application/x-www-form-urlencoded">
{/if}
    <fieldset>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        {array_field assign='catID' array='category' field='id'}
        {if $catID}
        <input type="hidden" id="category_id" name="category[id]" value="{$category.id}" />
        {/if}
        <legend>{gt text="General settings"}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="category_name">{gt text="Name"}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-lg-9">
                {array_field assign='catName' array='category' field='name'}
                <input id="category_name" name="category[name]" value="{$catName|safetext}" type="text" class="form-control" size="32" maxlength="255" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text="Parent"}</label>
            <div class="col-lg-9">
                {if ($catID != 1)}
                {$categorySelector}
                {else}
                <span><strong>{gt text="No parent category."}</strong></span>
                <input type="hidden" id="category_parent_id" name="category[parent_id]" value="{$category.parent_id}" />
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="category_is_locked">{gt text="Category is locked"}</label>
            <div class="col-lg-9">
                {array_field assign='catIsLocked' array='category' field='is_locked'}
                <input type="checkbox" id="category_is_locked" name="category[is_locked]" value="1"{if ($catIsLocked)} checked="checked"{/if} />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="category_is_leaf">{gt text="Category is a leaf node"}</label>
            <div class="col-lg-9">
            {array_field assign='catIsLeaf' array='category' field='is_leaf'}
            <input type="checkbox" id="category_is_leaf" name="category[is_leaf]" value="1"{if ($catIsLeaf)} checked="checked"{/if} />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="category_value">{gt text="Value"}</label>
            <div class="col-lg-9">
                {array_field assign='catValue' array='category' field='value'}
                <input id="category_value" name="category[value]" value="{$catValue|safetext}" type="text" class="form-control" size="16" maxlength="255" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="category_status">{gt text="Active"}</label>
            <div class="col-lg-9">
                {array_field assign='catStatus' array='category' field='status'}
                {if $mode != "edit"} {assign var="catStatus" value="A"}{/if}
                <input id="category_status" name="category[status]" value="A" type="checkbox" {if ($catStatus == 'A')} checked="checked"{/if} />&nbsp;&nbsp;
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text="Localised output"}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text="Name"}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-lg-9">
                {array_field assign='displayNames' array='category' field='display_name'}
                {foreach item=language from=$languages}
                {array_field assign='displayName' array='displayNames' field=$language}
                <div class="z-formlist">
                    <input id="category_display_name_{$language}" name="category[display_name][{$language}]" value="{$displayName}" type="text" class="form-control" size="50" maxlength="255" />
                    <label for="category_display_name_{$language}">({$language})</label>
                </div>
                {/foreach}
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text="Description"}</label>
            <div class="col-lg-9">
                {array_field assign='displayDescs' array='category' field='display_desc'}
                {foreach item=language from=$languages}
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
        <legend>{gt text="Attributes"}</legend>
        {include file=editattributes.tpl}
    </fieldset>
    {if $mode == "edit"}
    <fieldset>
        <legend><a class="categories_collapse_control" href="#">{gt text='Meta data'}</a></legend>
        <div class="categories_collapse_details">
            <div class="form-group">
                <label class="col-lg-3 control-label" for="category_meta_id">{gt text="Internal ID"}</label>
                <div class="col-lg-9">
                    <span id="category_meta_id">{$category.id|safetext}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="category_path">{gt text="Path"}</label>
                <div class="col-lg-9">
                    <span id="category_path">{$category.path|safetext}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="category_ipath">{gt text="I-path"}</label>
                <div class="col-lg-9">
                    <span id="category_ipath">{$category.ipath|safetext}</span>
                </div>
            </div>
        </div>
    </fieldset>
    {/if}
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
        {if ($mode == "edit")}
            {button class="z-btgreen" src=button_ok.png set=icons/extrasmall name="category_submit" value="update"  __alt="Save" __title="Save" __text="Save"}
            {button class="z-btblue" src=editcopy.png set=icons/extrasmall name="category_copy" value="copy" __alt="Copy" __title="Copy" __text="Copy"}
            {button class="z-btblue" src=editcut.png set=icons/extrasmall name="category_move" value="move" __alt="Move" __title="Move" __text="Move"}
            {button class="z-btred" src=14_layer_deletelayer.png set=icons/extrasmall name="category_delete" value="delete" __alt="Delete" __title="Delete" __text="Delete"}
        {if (!$category.is_leaf && $haveSubcategories && $haveLeafSubcategories)}
            {button src=xedit.png set=icons/extrasmall name="category_user_edit" value="edit" __alt="Edit" __title="Edit" __text="Edit"}
        {/if}
            <a class="btn btn-default" class="z-btred" href="{modurl modname=ZikulaCategoriesModule type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        {else}
            {button class="z-btgreen" src=button_ok.png set=icons/extrasmall name="category_submit" value="add" __alt="Save" __title="Save" __text="Save"}
            <a class="btn btn-default" class="z-btred" href="{modurl modname=ZikulaCategoriesModule type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        {/if}
        </div>
    </div>
</form>
{adminfooter}