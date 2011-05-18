{modgetinfo module=Categories info=all assign=modinfo}
{if $category}
{gt text="Edit category" assign=templatetitle}
{else}
{gt text="Create new category" assign=templatetitle}
{/if}

<h2 id="top">{$modinfo.displayname|safetext}</h2>

{formutil_getpassedvalue key="dr" default="0" assign="dr"}
{modurl modname="Categories" type="userform" func="resequence" assign="resq" dr=$dr}

<ul class="z-menulinks">
    {if ($referer)}
    <li><a class="z-icon-es-view" href="{modurl modname="Categories" type="user" func="referBack"}">{gt text="Return to referring page"}</a></li>
    {/if}
    <li><a class="z-icon-es-regenerate" href="{$resq|safetext}">{gt text="Resequence"}</a></li>
</ul>

{insert name="getstatusmsg"}
{include file="categories_user_list.tpl"}

<h3>{$templatetitle}</h3>

{if ($category)}
<form class="z-form" action="{modurl modname="Categories" type="userform" func="edit"}" method="post" enctype="application/x-www-form-urlencoded">
    {else}
    <form class="z-form" action="{modurl modname="Categories" type="userform" func="newcat"}" method="post" enctype="application/x-www-form-urlencoded">
        {/if}
        <fieldset>
            <legend>{gt text="Category"}</legend>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <input type="hidden" name="dr" value="{$rootCat.id|safetext}" />
            <input type="hidden" name="category[parent_id]" value="{$rootCat.id|safetext}" />
            <input type="hidden" name="category[is_locked]" value="0" />
            <input type="hidden" name="category[is_leaf]" value="1" />
            {array_field assign='catID' array='category' field='id'}
            {if $catID}
            <input type="hidden" name="category[id]"              value="{$category.id|safetext}" />
            <input type="hidden" name="category[path]"            value="{$category.path|safetext}" />
            <input type="hidden" name="category[ipath]"           value="{$category.ipath|safetext}" />
            <input type="hidden" name="category[obj_status]"      value="{$category.obj_status|safetext}" />
            <input type="hidden" name="category[cr_date]"         value="{$category.cr_date|safetext}" />
            <input type="hidden" name="category[cr_uid]"          value="{$category.cr_uid|safetext}" />
            <input type="hidden" name="category[lu_date]"         value="{$category.lu_date|safetext}" />
            <input type="hidden" name="category[lu_uid]"          value="{$category.lu_uid|safetext}" />
            {/if}
            <div class="z-formrow">
                <label for="category_name">{gt text="Name"}</label>
                {array_field assign='catName' array='category' field='name'}
                <input id="category_name" name="category[name]" value="{$catName|safetext}" type="text" size="32" maxlength="255" />
                {php}
                $tplVars =& $this->_tpl_vars;
                if (isset($tplVars['validationErrors']) && $tplVars['validationErrors'])
                {
                {/php}
                {formutil_getfieldmarker objectType="category" field="name" validation=$validation validationErrors=$validationErrors}
                {formutil_getvalidationerror objectType="category" field="name"}
                {php}
                }
                {/php}
            </div>
            <div class="z-formrow">
                <label for="category_value">{gt text="Value"}</label>
                {array_field assign='catValue' array='category' field='value'}
                <input id="category_value" name="category[value]" value="{$catValue|safetext}" type="text" size="16" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="category_status">{gt text="Active"}</label>
                {array_field assign='catStatus' array='category' field='status'}

                <input id="category_status" name="category[status]" value="A" type="checkbox"{if ($catStatus=='A')} checked="checked"{/if} />
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Localised output"}</legend>
            <div class="z-formrow">
                <label>{gt text="Name"}<span class="z-form-mandatory-flag">*</span></label>
                {array_field assign='displayNames' array='category' field='display_name'}
                {if ($displayNames || !$catID)}
                {foreach item=language from=$languages}
                {array_field assign='displayName' array='displayNames' field=$language}
                <div class="z-formlist">
                    <input id="category_display_name_{$language}" name="category[display_name][{$language}]" value="{$displayName}" type="text" size="50" maxlength="255" />
                    <label for="category_display_name_{$language}">({$language})</label>
                </div>
                {/foreach}
                {/if}
            </div>
            <div class="z-formrow">
                <label>{gt text="Description"}</label>
                {array_field assign='displayDescs' array='category' field='display_desc'}
                {if ($displayDescs || !$catID)}
                {foreach item=language from=$languages}
                {array_field assign='displayDesc' array='displayDescs' field=$language}
                <div class="z-formlist">
                    <textarea id="category_display_desc_{$language}" name="category[display_desc][{$language}]" rows="4" cols="56">{$displayDesc}</textarea>
                    <label for="category_display_desc_{$language}">({$language})</label>
                </div>
                {/foreach}
                {/if}
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Attributes"}</legend>
            {include file=categories_include_editattributes.tpl}
        </fieldset>
        {if $catID}
        <fieldset>
            <legend>{gt text="Category system information"}</legend>
            <div class="z-formrow">
                <label for="category_id">{gt text="Internal ID"}</label>
                <span id="category_id">{$category.id|safetext}</span>
            </div>
            <div class="z-formrow">
                <label for="category_path">{gt text="Path"}</label>
                <span id="category_path">{$category.path|safetext}</span>
            </div>
            <div class="z-formrow">
                <label for="category_ipath">{gt text="I-path"}</label>
                <span id="category_ipath">{$category.ipath|safetext}</span>
            </div>
        </fieldset>
        {/if}
        <div class="z-buttons z-formbuttons">
            {if ($category)}
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Categories type=user func=edit dr=$rootCat.id}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            {else}
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            {/if}
        </div>
    </form>
