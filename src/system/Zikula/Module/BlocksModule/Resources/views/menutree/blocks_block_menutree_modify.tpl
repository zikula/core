{ajaxheader modname=$module ui=true}
{pageaddvar name="javascript" value="system/Zikula/Module/BlocksModule/Resources/public/js/functions.js"}
{pageaddvar name="javascript" value="system/Zikula/Module/BlocksModule/Resources/public/js/contextmenu.js"}
{pageaddvar name="stylesheet" value="system/Zikula/Module/BlocksModule/Resources/public/css/menutree/adminstyle.css"}
{pageaddvar name="stylesheet" value="system/Zikula/Module/BlocksModule/Resources/public/css/menutree/contextmenu.css"}

{if !empty($redirect)}
<input type="hidden" name="redirect" value="{$redirect}" />
{else}
<input type="hidden" id="returntoblock" name="returntoblock" value="{$blockinfo.bid}" />
{/if}

{include file="menutree/blocks_block_menutree_help.tpl"}

<ul id="menutree_tabs" class="nav nav-tabs">
    <li class="active"><a href="#menutree_tabmenu" id="menutree_tabmenu_control" data-toggle="tab">{gt text="Block content"}</a></li>
    {if $menutree_anysettingsaccess}
        <li><a href="#menutree_tabsettings" id="menutree_tabsettings_control" data-toggle="tab">{gt text="Block settings"}</a></li>
    {/if}
</ul>

<div class="tab-content">
{if $menutree_anysettingsaccess}
    <div id="menutree_tabsettings" class="tab-pane">
        {if $menutree_adminaccess}
            <fieldset>
                <legend>{gt text="Permissions"}</legend>
                <p class="help-block alert alert-info">{gt text='You can restrict the access to certain settings higher than the default ("Edit") permissions level. Below is a list of these options.'}</p>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_titlesperms">{gt text="Block titles"}</label>
                    <div class="col-lg-9">
                        <select id="menutree_titlesperms" name="menutree[titlesperms]" class="form-control">
                            {html_options options=$permlevels selected=$menutree_titlesperms}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_displayperms">{gt text="Block display settings"}</label>
                    <div class="col-lg-9">
                        <select id="menutree_displayperms" name="menutree[displayperms]" class="form-control">
                            {html_options options=$permlevels selected=$menutree_displayperms}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_settingsperms">{gt text="Block editing settings"}</label>
                    <div class="col-lg-9">
                        <select id="menutree_settingsperms" name="menutree[settingsperms]" class="form-control">
                            {html_options options=$permlevels selected=$menutree_settingsperms}
                        </select>
                    </div>
                </div>
            </fieldset>
        {/if}

        {if $multilingual && $menutree_titlesaccess}
            <fieldset>
                <legend>{gt text="Block titles"}</legend>
                <p class="help-block alert alert-info">{gt text="You can specify a different a block title for each language. If left blank, the default block title will be displayed."}</p>
                {foreach from=$languages key=code item=name}
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_titles_{$code}">{$name}</label>
                    <div class="col-lg-9">
                        <input id="menutree_titles_{$code}" type="text" name="menutree[titles][{$code}]" value="{$menutree_titles.$code|safehtml}" size="40" maxlength="255" class="form-control" />
                    </div>
                    {/foreach}
                </div>
            </fieldset>
        {/if}

        {if $menutree_displayaccess}
            <fieldset>
                <legend>{gt text="Block display settings"}</legend>
                {if $somethemes}
                    <p class="help-block alert alert-info">{gt text='<strong>Note</strong>: some templates and/or stylesheets are found only in certain themes. These templates and stylesheets have been included in the "Only in some themes" group. Choosing a template or a stylesheet from this group you must take into account the fact that it might not be available in certain theme - in such situation default template and style ("blocks_block_menutree_default.tpl") is used.'}</p>
                {/if}
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_tpl">{gt text="Template"}</label>
                    <div class="col-lg-9">
                        <select id="menutree_tpl" name="menutree[tpl]" class="form-control">
                            {html_options options=$tpls selected=$menutree_tpl}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_stylesheet">{gt text="Stylesheet"}</label>
                    <div class="col-lg-9">
                        <select id="menutree_stylesheet" name="menutree[stylesheet]" class="form-control">
                            <option value="null">{gt text="Choose stylesheet"}</option>
                            {html_options options=$styles selected=$menutree_stylesheet}
                        </select>
                        <p id="menutree_stylesheet_helper" class="help-block alert alert-info" style="display: none;">{gt text='The list of stylesheets has been limited to those which appear to be associated with selected template.<br />If you do not find among them stylesheet you are looking for - you can <a href="#" title="Click here, to display all stylesheets">show all stylesheets</a>.'}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_editlinks">{gt text="Show editing links"}</label>
                    <div class="col-lg-9">
                        <input id="menutree_editlinks" type="checkbox" name="menutree[editlinks]" class="form-control" {if $menutree_editlinks}checked="checked"{/if}/>
                        <em class="sub">{gt text='Display the links: "Add current URL" and "Edit this block".'}</em>
                    </div>
                </div>
            </fieldset>
        {/if}

        {if $menutree_settingsaccess}
            <fieldset>
                <legend>{gt text="Block editing settings"}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_stripbaseurl">{gt text="Strip base url from links"}</label>
                    <div class="col-lg-9">
                        <span>
                            <input id="menutree_stripbaseurl" type="checkbox" name="menutree[stripbaseurl]" class="form-control" {if $menutree_stripbaseurl}checked="checked"{/if}/>
                            <em class="sub">{gt text="Base URL which will be removed: %s." tag1=$baseurl}</em>
                        </span>
                        {if $modvars.ZConfig.shorturls}
                            <p class="help-block alert alert-warning">
                                <strong>{gt text="Note! Short URLs are enabled. It's strongly recommended to turn on this option when using short URLs."}</strong>
                            </p>
                        {/if}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_maxdepth">{gt text="Maximum depth of tree"}</label>
                    <div class="col-lg-9">
                        <span>
                            <input id="menutree_maxdepth" type="text" name="menutree[maxdepth]" value="{$menutree_maxdepth|safehtml}" size="2" maxlength="2" class="form-control" />
                            <em class="sub">{gt text="Zero means no limit."}</em>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="menutree_linkclass">{gt text="Construct class list for links"}</label>
                    <div class="col-lg-9">
                        <input id="menutree_linkclass" type="checkbox" name="menutree[linkclass]" class="form-control" {if $menutree_linkclass}checked="checked"{/if}/>
                        <p class="help-block alert alert-info">{gt text="You can assign a CSS class for each link in the menu. This option allows you to prepare a list of classes, from which you will be able to select for application."}</p>
                    </div>
                    <fieldset id="menutree_linkclasses_group" style="margin: 0 1em;">
                        <legend>{gt text="Class list for links"}</legend>
                        <p>{gt text='The title of the class will be visible to the user, the name of the class will be used as an "class" attribute value.'}</p>
                        <ul id="menutree_linkclasses_list" class="z-itemlist">
                            <li class="clearfix z-itemheader row">
                                <span class="z-itemcell col-xs-5">{gt text="Class name"}</span>
                                <span class="z-itemcell col-xs-7">{gt text="Class title"}</span>
                            </li>
                            {assign var='classescount' value=$menutree_linkclasses|@count}
                            {foreach from=$menutree_linkclasses key=id item=class}
                            <li id="class_{$id}" class="{cycle values='z-odd,z-even'} clearfix row">
                                <span class="z-itemcell col-xs-5">
                                    <input type="text" name="menutree[linkclasses][{$id}][name]" value="{$class.name|safehtml}" size="40" maxlength="255" class="form-control" />
                                </span>
                                <span class="z-itemcell col-xs-5">
                                    <input type="text" name="menutree[linkclasses][{$id}][title]" value="{$class.title|safehtml}" size="40" maxlength="255" class="form-control" />
                                </span>
                                <span class="z-itemcell col-xs-2">
                                    <a href="#" class="menutree_linkclass_del text-danger"><i class=" fa fa-trash-o fa-lg"></i></a>
                                </span>
                            </li>
                            {/foreach}
                            <li id="class_{$classescount}" class="{cycle values='z-odd,z-even'} clearfix row">
                                <span class="z-itemcell col-xs-5">
                                    <input type="text" name="menutree[linkclasses][{$classescount}][name]" size="40" maxlength="255" class="form-control" />
                                </span>
                                <span class="z-itemcell col-xs-5">
                                    <input type="text" name="menutree[linkclasses][{$classescount}][title]" size="40" maxlength="255" class="form-control" />
                                </span>
                                <span class="z-itemcell col-xs-2">
                                    <a href="#" class="menutree_linkclass_del text-danger"><i class=" fa fa-trash-o fa-lg"></i></a>
                                </span>
                            </li>
                        </ul>
                        <a href="#" id="menutree_linkclass_add" class="text-success"><i class="fa fa-plus-square fa-lg"></i>&nbsp;{gt text="Add new class row"}</a>
                    </fieldset>
                </div>
            </fieldset>
        {/if}
    </div>
{/if}

    <div id="menutree_tabmenu" class="tab-pane active">
        <ul class="navbar navbar-default navbar-modulelinks">
            <li><a href="#" id="menutree_newnode"><i class='fa fa-plus'></i> {gt text="Add menu parent item"}</a></li>
            <li><a href="#" id="menutree_expandall"><i class='fa fa-expand'></i> {gt text="Expand all"}</a></li>
            <li><a href="#" id="menutree_collapseall"><i class='fa fa-compress'></i> {gt text="Collapse all"}</a></li>
            {if $multilingual}
                <li>
                    {gt text="Change active language:"}
                    {foreach from=$languages key=code item=name name=langloop}
                        <a href="#" lang="{$code}" class="menutree_langcontrols{if $code == $defaultanguage} activelang{/if}">{$name}</a> {if !$smarty.foreach.langloop.last} | {/if}
                    {/foreach}
                </li>
            {/if}
        </ul>

        {if !empty($menutree_menus)}
            <div id="menuTreeImportOptions">
                <p>{gt text="You can import one of the existing menus. To do so, choose the appropriate menu from the drop-down list and click 'save'. If the selected menu had properly formatted links, they will be imported."}</p>
                <select id="menutree_menus" name="menutree_menus">
                    <option value="null">{gt text="Choose menu"}</option>
                    {html_options options=$menutree_menus}
                </select>
            </div>
        {/if}
        <div id="menuTreeContainer">
            <div class="alert alert-info">{gt text='click on an item to edit, delete, add child, etc.'}</div>
            {$menutree_content}
        </div>
        <div class="alert alert-warning">{gt text='Be sure to save your changes by clicking the "save" button below.'}</div>
    </div>
</div><!-- /.tab-content -->

<script type="text/javascript">
    //add this url
    {{if $menutree_newurl}}
    Event.observe(window, 'load', function() {
        var data = {link_href: '{{$menutree_newurl|safetext}}'};
        Zikula.Menutree.Tree.inst.newNode(data);
    });
    {{/if}}
</script>

{capture assign="itemForm"}
    {* ATTENTION: Zikula.UI.FormDialog does not support bootstrap form styling. There is no reason to refactor until Zikula.UI is replaced *}
    <div id="menutree_form_container" title="{gt text='Edit menu item'}" style="display: none;">
        <p id="forminfo" class="z-warningmsg">{gt text="Field Name is required."}</p>
        <p id="requiredInfo" class="z-errormsg" style="display: none;">{gt text="Please fill required fields"}</p>
        <form action="#" id="nodeBuilder" class="z-form" role="form">
            <div>
                <fieldset>
                    <legend>{gt text="Menu item data"}</legend>
                    {if $multilingual}
                    <div class="z-formrow">
                        <label for="link_lang">{gt text="Language"}</label>
                        {html_options name="link_lang" id="link_lang" options=$languages}
                    </div>
                    {/if}
                    <input type="hidden" name="clang" id="clang" />
                    <div class="z-formrow">
                        <label for="link_name">{gt text="Name"}</label>
                        <input type="text" name="link_name" id="link_name" class="required" />
                    </div>
                    <div class="z-formrow">
                        <label for="link_title">{gt text="Title"}</label>
                        <input type="text" name="link_title" id="link_title" />
                    </div>
                    <div class="z-formrow">
                        <label for="link_href">{gt text="URL"}</label>
                        <input type="text" name="link_href" id="link_href" />
                        {if $multilingual}
                            <div class="sub help-block">
                                <input type="checkbox" class="checkbox" name="global_link_href" id="global_link_href" />
                                <label for="global_link_href">{gt text="Use one for all languages"}</label>
                            </div>
                        {/if}
                    </div>
                    <div class="z-formrow">
                        <label for="link_className">{gt text="CSS class"}</label>
                        {if $menutree_linkclass}
                            <select name="link_className" id="link_className">
                                <option value="">{gt text="Choose class"}</option>
                                {foreach from=$menutree_linkclasses key=id item=class}
                                    <option value="{$class.name}">{$class.title}</option>
                                {/foreach}
                            </select>
                        {else}
                            <input type="text" name="link_className" id="link_className" />
                        {/if}
                        {if $multilingual}
                            <div class="sub help-block">
                                <input type="checkbox" class="checkbox" name="global_link_className" id="global_link_className" />
                                <label for="global_link_className">{gt text="Use one for all languages"}</label>
                            </div>
                        {/if}
                    </div>
                    <div class="z-formrow">
                        <label for="link_state">{gt text="Active?"}</label>
                        <input type="checkbox" class="checkbox" name="link_state" id="link_state" />
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
{/capture}
{pageaddvar name="footer" value=$itemForm}
