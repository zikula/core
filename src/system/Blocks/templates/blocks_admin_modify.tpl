{ajaxheader ui=true}
{pageaddvar name='javascript' value='javascript/helpers/Zikula.itemlist.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit block"}</h3>
</div>

<form id="blockupdateform" class="z-form" action="{modurl modname="Blocks" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
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
                <label for="blocks_description">{gt text="Description"}</label>
                <input id="blocks_description" name="description" type="text" size="40" maxlength="255" value="{$description|safetext}" />
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
                <a href="#block_placement_advanced" id="blocks_advanced_placement_onclick" title="{gt text="Advanced placement options"}" class="z-formnote">{gt text="Show/hide advanced placement options"}</a>
            </div>

            <div id="block_placement_advanced" style="display:none;">
                <p class="z-informationmsg">{gt text="To restrict the block's visibility to certain modules and module functions, you can create filter(s) and select the module, function type, function name and function arguments that you want to apply to the filter. All fields are optional. If you omit a field, it will act as an *. "}</p>
                <p><a id="appendfilter" class="z-icon-es-new" href="javascript:void(0);">{gt text="Create new filter"}</a></p>
                <div>
                    <ol id="placementfilterslist" class="z-itemlist">
                        <li class="z-itemheader z-clearfix">
                            <span class="z-itemcell z-w25">{gt text="Module"}</span>
                            <span class="z-itemcell z-w15">{gt text="Function type"}</span>
                            <span class="z-itemcell z-w25">{gt text="Function name"}</span>
                            <span class="z-itemcell z-w25">{gt text="Function arguments (delimiter=&)"}</span>
                            <span class="z-itemcell z-w10">{gt text="Delete"}</span>
                        </li>

                        {foreach from=$filter item='placementfilter' name='loop_filters'}
                        {assign var='loop_index' value=$smarty.foreach.loop_filters.iteration-1}
                        <li id="li_placementfilterslist_{$loop_index}" class="{cycle values='z-odd,z-even'} z-clearfix">
                            <span class="z-itemcell z-w25">
                                <select id="filters_{$loop_index}_module" name="filters[{$loop_index}][module]">
                                    {foreach from=$mods key='name' item='displayname' name='modlist'}
                                    <option value="{$name}" {if $placementfilter.module eq $name}selected="selected"{/if}>{$displayname}</option>
                                    {/foreach}
                                </select>
                            </span>
                            <span class="z-itemcell z-w15"><input type="text" id="filters_{$loop_index}_ftype" name="filters[{$loop_index}][ftype]" size="10" maxlength="255" value="{$placementfilter.ftype|safetext}" /></span>
                            <span class="z-itemcell z-w25"><input type="text" id="filters_{$loop_index}_fname" name="filters[{$loop_index}][fname]" size="30" maxlength="255" value="{$placementfilter.fname|safetext}" /></span>
                            <span class="z-itemcell z-w25"><input type="text" id="filters_{$loop_index}_fargs" name="filters[{$loop_index}][fargs]" size="30" maxlength="255" value="{$placementfilter.fargs|safetext}" /></span>
                            <span class="z-itemcell z-w10">
                                <button type="button" class="imagebutton-nofloat buttondelete" id="buttondelete_placementfilterslist_{$loop_index}">{img src='14_layer_deletelayer.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' }</button>
                                (<span class="itemid">{$loop_index}</span>)
                            </span>
                        </li>
                        {foreachelse}
                        {* tfotis - i don't know why this is needed, but if it isn't here, item is not appended *}
                        <li class="z-hide"><span class="itemid">-1</span></li>
                        {/foreach}
                    </ol>
                </div>

                <ul style="display:none;">
                    <li id="placementfilterslist_emptyitem" class="z-clearfix">
                        <span class="z-itemcell z-w25">
                            <select class="listinput" id="filters_X_module" name="filtersdummy[]">
                                {foreach from=$mods key='name' item='displayname' name='modlist'}
                                <option value="{$name}">{$displayname}</option>
                                {/foreach}
                            </select>
                        </span>
                        <span class="z-itemcell z-w15"><input type="text" class="listinput" id="filters_X_ftype" name="filtersdummy[]" size="10" maxlength="255" value="" /></span>
                        <span class="z-itemcell z-w25"><input type="text" class="listinput" id="filters_X_fname" name="filtersdummy[]" size="30" maxlength="255" value="" /></span>
                        <span class="z-itemcell z-w25"><input type="text" class="listinput" id="filters_X_fargs" name="filtersdummy[]" size="30" maxlength="255" value="" /></span>
                        <span class="z-itemcell z-w10">
                            <button type="button" class="imagebutton-nofloat buttondelete" id="buttondelete_placementfilterslist_X">{img src='14_layer_deletelayer.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' }</button>
                            (<span class="itemid"></span>)
                        </span>
                    </li>
                </ul>
            </div>
        </fieldset>

        {if $modvars.Blocks.collapseable eq 1}
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
        {if $bkey eq "Html"}{* notify hooks here strictly for html block *}
        {notifydisplayhooks eventname='blocks.ui_hooks.htmlblock.content.form_edit' id=$bid}
        {/if}
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

        {if isset($redirect) && $redirect neq ''}
        {assign var="cancelurl" value=$redirect|urldecode}
        {else}
        {modurl modname="Blocks" type="admin" func="view" assign="cancelurl"}
        {/if}

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{$cancelurl|safetext}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}

{pageaddvarblock}
<script type="text/javascript">
    /* <![CDATA[ */
    var total_existing_filters = {{$filter|@count}};
    var list_placementfilterslist = null;
    var defwindow = null;
    document.observe("dom:loaded",function(){
        list_placementfilterslist = new Zikula.itemlist('placementfilterslist', {headerpresent: true, firstidiszero: true, sortable: false});
        $('appendfilter').observe('click',function(event){
            list_placementfilterslist.appenditem();
            event.stop();
        });
        defwindow = new Zikula.UI.Dialog($('blocks_advanced_placement_onclick'),
        [{label: Zikula.__('Ok'), 'class': 'z-btgreen'}],
        {minmax:true, width: 760, height: 340, resizable: true}
        );
        $('blockupdateform').observe('submit',function(){
            $('blockupdateform').insert($('block_placement_advanced').hide());
        })
    });
    /* ]]> */
</script>
{/pageaddvarblock}
