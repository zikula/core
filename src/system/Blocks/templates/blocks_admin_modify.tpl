{include file="blocks_admin_menu.tpl"}
{ajaxheader modname=Blocks filename=blocks_admin_modify.js}
{gt text="Edit block" assign=templatetitle}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=edit.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <form id="blockupdateform" class="z-form" action="{modurl modname="Blocks" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Blocks"}" />
            <input type="hidden" name="bid" value="{$bid|safetext}" />
            {if $requirement neq ''}
            <p class="z-warningmsg">
                {$requirement}
            </p>
            {/if}
            <fieldset>
                <legend>{$modtitle|safetext}</legend>
                <div class="z-formrow">
                    <label for="blocks_title">{gt text="Title"}</label>
                    <input id="blocks_title" name="title" type="text" size="40" maxlength="255" value="{$title|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="blocks_language">{gt text="Language"}</label>
                    {html_select_locales id=blocks_language name=language selected=$language installed=true all=true}
                </div>
            </fieldset>
            <fieldset>
                <legend>{gt text="Block placement filtering"}</legend>
                <div class="z-formrow">
                    <label for="blocks_position">{gt text="Position(s)"}</label>
                    <span>
                        <select id="blocks_position" name="positions[]" multiple="multiple">
                            {html_options options=$block_positions selected=$placements}
                        </select>
                    </span>
                    <a href="javascript:void(0);" id="blocks_advanced_placement_onclick" class="z-formnote z-hide">{gt text="Show/hide advanced placement options"}</a>
                </div>

                <div id="block_placement_advanced">
                    <p class="z-formnote z-informationmsg">{gt text="To restrict the block's visibility to certain modules and module functions, put a checkmark beside the module (or modules) in which you want the block to be visible. If you want to further restrict visibility within a module, then enter the function type (or multiple types separated by a space), the function name, and (optionally) custom arguments."}</p>
                    <div class="z-formrow z-clearfix">
                        <label for="blocks_modules">{gt text="Modules"}</label>
                        <ul id="blocks_modules" class="blocks-modulefilter-splitlist">
                            {foreach name=modlist from=$mods item=mod}
                            {assign var=modname value=$mod.name}
                            <li><input type="checkbox" name="filter[modules][]" value="{$mod.name}"{if isset($filter.modules.$modname)} checked="checked"{/if} /> {$mod.displayname}</li>
                            {/foreach}
                        </ul>
                    </div>
                    <div class="z-formrow">
                        <label for="blocks_type">{gt text="Function type(s)"}</label>
                        <input id="blocks_type" name="filter[type]" type="text" size="40" maxlength="255" value="{$filter.type|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="blocks_functions">{gt text="Function name"}</label>
                        <input id="blocks_functions" name="filter[functions]" type="text" size="40" maxlength="255" value="{$filter.functions|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="blocks_customargs">{gt text="Function arguments"}</label>
                        <input id="blocks_customargs" name="filter[customargs]" type="text" size="40" maxlength="255" value="{$filter.customargs|safetext}" />
                    </div>
                </div>
            </fieldset>
            {if $pncore.Blocks.collapseable eq 1}
            <fieldset>
                <legend>{gt text="Collapsibility"}</legend>
                <div class="z-formrow">
                    <label for="blocks_collapsable">{gt text="Collapsible"}</label>
                    <div id="blocks_collapsable">
                        <label for="blocks_collapsable_yes">{gt text="Yes"}</label><input id="blocks_collapsable_yes" name="collapsable" type="radio" value="1" {if $collapsable eq 1}checked="checked" {/if}/>
                        <label for="blocks_collapsable_no">{gt text="No"}</label><input id="blocks_collapsable_no" name="collapsable" type="radio" value="0" {if $collapsable neq 1}checked="checked" {/if}/>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="blocks_defaultstate">{gt text="Default state"}</label>
                    <div id="blocks_defaultstate">
                        <label for="blocks_defaultstate_expanded">{gt text="Expanded"}</label><input id="blocks_defaultstate_expanded" name="defaultstate" type="radio" value="1" {if $defaultstate eq 1}checked="checked" {/if}/>
                        <label for="blocks_defaultstate_collapsed">{gt text="Collapsed"}</label><input id="blocks_defaultstate_collapsed" name="defaultstate" type="radio" value="0" {if $defaultstate neq 1}checked="checked" {/if}/>
                    </div>
                </div>
            </fieldset>
            {/if}

            {if $blockoutput eq '' and $form_content eq true}
            <fieldset>
                <legend>{gt text="Customisation"}</legend>
                <div class="z-formrow">
                    <label for="blocks_content">{gt text="Content"}</label>
                    <textarea id="blocks_content" name="content" cols="50" rows="10">{$content|safetext}</textarea>
                </div>
            </fieldset>
            {/if}

            {if $blockoutput neq ''}
            <fieldset>
                <legend>{gt text="Customisation"}</legend>
                {if $admin_tableless}
                {$blockoutput}
                {else}
                <table>
                    {$blockoutput}
                </table>
                {/if}
            </fieldset>
            {/if}

            <fieldset>
                <div class="z-formrow">
                    <label for="blocks_refresh">{gt text="Block refresh interval"}</label>
                    <select id="blocks_refresh" name="refresh">
                        {html_options options=$blockrefreshtimes selected=$refresh}
                    </select>
                </div>
            </fieldset>

            {modcallhooks hookobject=item hookaction=modify hookid=$bid module=Blocks}

            {if isset($redirect) && $redirect neq ''}
                {assign var="cancelurl" value=$redirect|urldecode}
            {else}
                {modurl modname="Blocks" type="admin" func="view" assign="cancelurl"}
            {/if}

            <div class="z-buttons z-formbuttons">
                {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{$cancelurl|safetext}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
