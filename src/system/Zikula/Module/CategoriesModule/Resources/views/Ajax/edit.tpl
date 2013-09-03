{insert name='getstatusmsg'}
{if $mode == "edit"}
    {gt text="Edit category" assign="windowtitle"}
{else}
    {gt text="Create new category" assign="windowtitle"}
{/if}
<div id="categories_ajax_form_container" style="display: none;" title="{$windowtitle}">
    <form id="categories_ajax_form" class="form-horizontal" role="form" action="#" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <input type="hidden" id="category_parent_id" name="category[parent_id]" value="{$category.parent_id}" />
            {array_field assign='catID' array='category' field='id'}
            {if ($catID)}
            <input type="hidden" id="category_id" class="form-control" name="category[id]" value="{$category.id}" />
            {/if}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="category_name">{gt text="Name"}<span class="z-form-mandatory-flag">*</span></label>
                <div class="col-lg-9">
                    {array_field assign='catName' array='category' field='name'}
                    <input id="category_name" name="category[name]" class="form-control" value="{$catName|safetext}" type="text" size="32" maxlength="255" />
                </div>
            </div>
            <div class="form-group">
                <span class="col-lg-3 control-label">{gt text="Parent"}</span>
                <div class="col-lg-9">
                    <div class="form-control-static">
                        <strong>{category_path id=$category.parent_id field='name'}</strong>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="category_is_locked">{gt text="Category is locked"}</label>
                <div class="col-lg-9">
                    <div class="form-control-static">
                        <strong>{category_path id=$category.parent_id field='name'}</strong>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Parent"}</label>
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
                    <input id="category_value" class="form-control" name="category[value]" value="{$catValue|safetext}" type="text" size="16" maxlength="255" />
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
                    <input id="category_display_name_{$language}" class="form-control" name="category[display_name][{$language}]" value="{$displayName}" type="text" size="50" maxlength="255" />
                    <em class="help-block" for="category_display_name_{$language}">({$language})</em>
                    {/foreach}
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Description"}</label>
                <div class="col-lg-9">
                    {array_field assign='displayDescs' array='category' field='display_desc'}
                    {foreach item=language from=$languages}
                    {array_field assign='displayDesc' array='displayDescs' field=$language}
                    <textarea id="category_display_desc_{$language}" class="form-control" name="category[display_desc][{$language}]" rows="4" cols="56">{$displayDesc}</textarea>
                    <em class="help-block" for="category_display_desc_{$language}">({$language})</em>
                    {/foreach}
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend><a class="categories_collapse_control" href="#">{gt text="Attributes"}</a></legend>
            <div class="categories_collapse_details">
                {include file=editattributes.tpl}
            </div>
        </fieldset>
        {if $mode == "edit"}
        <fieldset>
            <legend><a class="categories_collapse_control" href="#">{gt text='Meta data'}</a></legend>
            <div class="categories_collapse_details">
                <div class="form-group">
                    <label class="col-lg-3 control-label">{gt text="Internal ID"}</label>
                    <div class="col-lg-9">
                        <div class="form-control-static">
                            <span id="category_meta_id">{$category.id|safetext}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">{gt text="Path"}</label>
                    <div class="col-lg-9">
                        <div class="form-control-static">
                            <span id="category_path">{$category.path|safetext}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">{gt text="I-path"}</label>
                    <div class="col-lg-9">
                        <div class="form-control-static">
                            <span id="category_ipath">{$category.ipath|safetext}</span>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        {/if}
    </form>
</div>
