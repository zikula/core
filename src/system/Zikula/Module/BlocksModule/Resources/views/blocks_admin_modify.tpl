{ajaxheader ui=true}
{pageaddvar name='javascript' value='javascript/helpers/Zikula.itemlist.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit block"}</h3>
</div>

<form id="blockupdateform" class="form-horizontal" role="form" action="{modurl modname="Blocks" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="bid" value="{$bid|safetext}" />
        {if $requirement neq ''}
        <p class="alert alert-warning">
            {$requirement}
        </p>
        {/if}
        <fieldset>
            <legend>{$modtitle|safetext}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_title">{gt text="Title"}</label>
                <div class="col-lg-9">
                    <input id="blocks_title" name="title" class="form-control" type="text" size="40" maxlength="255" value="{$title|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_description">{gt text="Description"}</label>
                <div class="col-lg-9">
                    <input id="blocks_description" name="description" class="form-control" type="text" size="40" maxlength="255" value="{$description|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_language">{gt text="Language"}</label>
                <div class="col-lg-9">
                    {html_select_locales id=blocks_language class="form-control" name=language selected=$language installed=true all=true}
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Block placement filtering"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_position">{gt text="Position(s)"}</label>
                <div class="col-lg-9">
                    <span>
                        {assign var="selectsize" value=$block_positions|@count}{if $selectsize gt 20}{assign var="selectsize" value=20}{/if}{if $selectsize lt 4}{assign var="selectsize" value=4}{/if}
                        <select id="blocks_position" class="form-control" name="positions[]" multiple="multiple" size="{$selectsize}">
                            {html_options options=$block_positions selected=$placements}
                        </select>
                    </span>
                    <a href="#block_placement_advanced" id="blocks_advanced_placement_onclick" title="{gt text="Advanced placement options"}" class="help-block">{gt text="Show/hide advanced placement options"}</a>
                </div>

                <div id="block_placement_advanced" style="display:none;">
                    <p class="alert alert-info">{gt text="To restrict the block's visibility to certain modules and module functions, you can create filter(s) and select the module, function type, function name and function arguments that you want to apply to the filter. All fields are optional. If you omit a field, it will act as an *. "}</p>
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
                                    <select id="filters_{$loop_index}_module" class="form-control" name="filters[{$loop_index}][module]">
                                        {foreach from=$mods key='name' item='displayname' name='modlist'}
                                        <option value="{$name}" {if $placementfilter.module eq $name}selected="selected"{/if}>{$displayname}</option>
                                        {/foreach}
                                    </select>
                                </span>
                                <span class="z-itemcell z-w15"><input class="form-control" type="text" id="filters_{$loop_index}_ftype" name="filters[{$loop_index}][ftype]" size="10" maxlength="255" value="{$placementfilter.ftype|safetext}" /></span>
                                <span class="z-itemcell z-w25"><input class="form-control" type="text" id="filters_{$loop_index}_fname" name="filters[{$loop_index}][fname]" size="30" maxlength="255" value="{$placementfilter.fname|safetext}" /></span>
                                <span class="z-itemcell z-w25"><input class="form-control" type="text" id="filters_{$loop_index}_fargs" name="filters[{$loop_index}][fargs]" size="30" maxlength="255" value="{$placementfilter.fargs|safetext}" /></span>
                                <span class="z-itemcell z-w10">
                                    <button type="button" class="imagebutton-nofloat buttondelete" id="buttondelete_placementfilterslist_{$loop_index}">{img src='14_layer_deletelayer.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' }</button>
                                    (<span class="itemid">{$loop_index}</span>)
                                </span>
                            </li>
                            {foreachelse}
                            {* tfotis - i don't know why this is needed, but if it isn't here, item is not appended *}
                            <li class="hide"><span class="itemid">-1</span></li>
                            {/foreach}
                        </ol>
                    </div>

                    <ul style="display:none;">
                        <li id="placementfilterslist_emptyitem" class="z-clearfix">
                            <span class="z-itemcell z-w25">
                                <select class="listinput" class="form-control" id="filters_X_module" name="filtersdummy[]">
                                    {foreach from=$mods key='name' item='displayname' name='modlist'}
                                    <option value="{$name}">{$displayname}</option>
                                    {/foreach}
                                </select>
                            </span>
                            <span class="z-itemcell z-w15"><input class="form-control" type="text" class="listinput" id="filters_X_ftype" name="filtersdummy[]" size="10" maxlength="255" value="" /></span>
                            <span class="z-itemcell z-w25"><input class="form-control" type="text" class="listinput" id="filters_X_fname" name="filtersdummy[]" size="30" maxlength="255" value="" /></span>
                            <span class="z-itemcell z-w25"><input class="form-control" type="text" class="listinput" id="filters_X_fargs" name="filtersdummy[]" size="30" maxlength="255" value="" /></span>
                            <span class="z-itemcell z-w10">
                                <button type="button" class="imagebutton-nofloat buttondelete" id="buttondelete_placementfilterslist_X">{img src='14_layer_deletelayer.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' }</button>
                                (<span class="itemid"></span>)
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </fieldset>

        {if $modvars.ZikulaBlocksModule.collapseable eq 1}
        <fieldset>
            <legend>{gt text="Collapsibility"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_collapsable">{gt text="Collapsible"}</label>
                <div class="col-lg-9">
                    <div id="blocks_collapsable">
                        <label for="blocks_collapsable_yes">{gt text="Yes"}</label><input id="blocks_collapsable_yes" name="collapsable" type="radio" value="1" {if $collapsable eq 1}checked="checked" {/if}/>
                        <label for="blocks_collapsable_no">{gt text="No"}</label><input id="blocks_collapsable_no" name="collapsable" type="radio" value="0" {if $collapsable neq 1}checked="checked" {/if}/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_defaultstate">{gt text="Default state"}</label>
                <div class="col-lg-9">
                    <div id="blocks_defaultstate">
                        <label for="blocks_defaultstate_expanded">{gt text="Expanded"}</label><input id="blocks_defaultstate_expanded" name="defaultstate" type="radio" value="1" {if $defaultstate eq 1}checked="checked" {/if}/>
                        <label for="blocks_defaultstate_collapsed">{gt text="Collapsed"}</label><input id="blocks_defaultstate_collapsed" name="defaultstate" type="radio" value="0" {if $defaultstate neq 1}checked="checked" {/if}/>
                    </div>
                </div>
            </div>
        </fieldset>
        {/if}

        {if $blockoutput eq '' and $form_content eq true}
        <fieldset>
            <legend>{gt text="Customisation"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_content">{gt text="Content"}</label>
                <div class="col-lg-9">
                    <textarea id="blocks_content" class="form-control" name="content" cols="50" rows="10">{$content|safetext}</textarea>
                </div>
            </div>
        </fieldset>
        {if $bkey eq "HtmlBlock"}{* notify hooks here strictly for html block *}
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
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_refresh">{gt text="Block refresh interval"}</label>
                <div class="col-lg-9">
                    <select id="blocks_refresh" class="form-control" name="refresh">
                        {html_options options=$blockrefreshtimes selected=$refresh}
                    </select>
                </div>
            </div>
        </fieldset>

        {if isset($redirect) && $redirect neq ''}
        {assign var="cancelurl" value=$redirect|urldecode}
        {else}
        {modurl modname="Blocks" type="admin" func="view" assign="cancelurl"}
        {/if}

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a class="btn btn-default" href="{$cancelurl|safetext}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
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
