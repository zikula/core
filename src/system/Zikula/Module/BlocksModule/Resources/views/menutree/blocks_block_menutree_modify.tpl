{ajaxheader modname="blocks" ui=true}
{pageaddvar name="javascript" value="system/Blocks/javascript/functions.js"}
{pageaddvar name="javascript" value="system/Blocks/javascript/contextmenu.js"}
{pageaddvar name="stylesheet" value="system/Blocks/style/menutree/adminstyle.css"}
{pageaddvar name="stylesheet" value="system/Blocks/style/menutree/contextmenu.css"}

{if !empty($redirect)}
<input type="hidden" name="redirect" value="{$redirect}" />
{else}
<input type="hidden" id="returntoblock" name="returntoblock" value="{$blockinfo.bid}" />
{/if}

<ul id="menutree_tabs" class="z-tabs">
    <li class="tab"><a href="#menutree_tabmenu" id="menutree_tabmenu_control" class="menutree_tabcontrol">{gt text="Block content"}</a></li>
    {if $menutree_anysettingsaccess}
    <li class="tab"><a href="#menutree_tabsettings" id="menutree_tabsettings_control" class="menutree_tabcontrol">{gt text="Block settings"}</a></li>
    {/if}
    <li class="tab">{helplink filename='Menutree/Adminhelp.txt' popup=1 __title='Help'|gt:$zikula_view}</li>
</ul>

{if $menutree_anysettingsaccess}
<div id="menutree_tabsettings" class="menutree_tabcontent">
    {if $menutree_adminaccess}
    <fieldset>
        <legend>{gt text="Permissions"}</legend>
        <p class="z-formnote z-informationmsg">{gt text='You can restrict the access to certain settings higher than the default ("Edit") permissions level. Below is a list of these options.'}</p>
        <div class="z-formrow">
            <label for="menutree_titlesperms">{gt text="Block titles"}</label>
            <select id="menutree_titlesperms" name="menutree[titlesperms]">
                {html_options options=$permlevels selected=$menutree_titlesperms}
            </select>
        </div>
        <div class="z-formrow">
            <label for="menutree_displayperms">{gt text="Block display settings"}</label>
            <select id="menutree_displayperms" name="menutree[displayperms]">
                {html_options options=$permlevels selected=$menutree_displayperms}
            </select>
        </div>
        <div class="z-formrow">
            <label for="menutree_settingsperms">{gt text="Block editing settings"}</label>
            <select id="menutree_settingsperms" name="menutree[settingsperms]">
                {html_options options=$permlevels selected=$menutree_settingsperms}
            </select>
        </div>
    </fieldset>
    {/if}

    {if $multilingual && $menutree_titlesaccess}
    <fieldset>
        <legend>{gt text="Block titles""}</legend>
        <p class="z-formnote z-informationmsg">{gt text="You can specify a diferent block title for each language. If blank, the default block title will be displayed."}</p>
        {foreach from=$languages key=code item=name}
        <div class="z-formrow">
            <label for="menutree_titles_{$code}">{$name}</label>
            <input id="menutree_titles_{$code}" type="text" name="menutree[titles][{$code}]" value="{$menutree_titles.$code|safehtml}" size="40" maxlength="255" />
        </div>
        {/foreach}
    </fieldset>
    {/if}

    {if $menutree_displayaccess}
    <fieldset>
        <legend>{gt text="Block display settings"}</legend>
        {if $somethemes}
        <p class="z-formnote z-informationmsg">{gt text='<strong>Note</strong>: some templates and/or stylesheets are found only in certain themes. These templates and stylesheets have been included in the "Only in some themes" group. Choosing a template or a stylesheet from this group you must take into account the fact that it might not be available in certain theme - in such situation default template and style ("blocks_block_menutree_default.tpl") is used.'}</p>
        {/if}
        <div class="z-formrow">
            <label for="menutree_tpl">{gt text="Template"}</label>
            <select id="menutree_tpl" name="menutree[tpl]">
                {html_options options=$tpls selected=$menutree_tpl}
            </select>
        </div>
        <div class="z-formrow">
            <label for="menutree_stylesheet">{gt text="Stylesheet"}</label>
            <select id="menutree_stylesheet" name="menutree[stylesheet]">
                <option value="null">{gt text="Choose stylesheet"}</option>
                {html_options options=$styles selected=$menutree_stylesheet}
            </select>
            <p id="menutree_stylesheet_helper" class="z-formnote z-informationmsg" style="display: none;">{gt text='The list of stylesheets has been limited to those which appear to be associated with selected template.<br />If you do not find among them stylesheet you are looking for - you can <a href="#" title="Click here, to display all stylesheets">show all stylesheets</a>.'}</p>
        </div>
        <div class="z-formrow">
            <label for="menutree_editlinks">{gt text="Show editing links"}</label>
            <input id="menutree_editlinks" type="checkbox" name="menutree[editlinks]" {if $menutree_editlinks}checked="checked"{/if}/>
            <em class="z-sub">{gt text='Display the links: "Add current URL" and "Edit this block".'}</em>
        </div>
    </fieldset>
    {/if}

    {if $menutree_settingsaccess}
    <fieldset>
        <legend>{gt text="Block editing settings"}</legend>
        <div class="z-formrow">
            <label for="menutree_stripbaseurl">{gt text="Strip base url from links"}</label>
            <span>
                <input id="menutree_stripbaseurl" type="checkbox" name="menutree[stripbaseurl]" {if $menutree_stripbaseurl}checked="checked"{/if}/>
                <em class="z-sub">{gt text="Base URL which will be removed: %s." tag1=$baseurl}</em>
            </span>
            {if $modvars.ZConfig.shorturls}
            <p class="z-formnote z-warningmsg">
                <strong>{gt text="Note! Short URLs are enabled. It's strongly recommended to turn on this option when using short URLs."}</strong>
            </p>
            {/if}
        </div>
        <div class="z-formrow">
            <label for="menutree_maxdepth">{gt text="Maximum depth of tree"}</label>
            <span>
                <input id="menutree_maxdepth" type="text" name="menutree[maxdepth]" value="{$menutree_maxdepth|safehtml}" size="2" maxlength="2" />
                <em class="z-sub">{gt text="Zero means no limit."}</em>
            </span>
        </div>
        <div class="z-formrow">
            <label for="menutree_linkclass">{gt text="Class list for links"}</label>
            <input id="menutree_linkclass" type="checkbox" name="menutree[linkclass]" {if $menutree_linkclass}checked="checked"{/if}/>
            <p class="z-formnote z-informationmsg">{gt text="You can assign a CSS class for each link in the menu. This option allows you to prepare a list of classes, which you will be able to choose."}</p>
        </div>
        <fieldset id="menutree_linkclasses_group">
            <legend>{gt text="Class list for links"}</legend>
            <p>{gt text='The title of the class will be visible to the user, the name of the class will be used as an "class" attribute value.'}</p>
            <ul id="menutree_linkclasses_list" class="z-itemlist">
                <li class="z-clearfix z-itemheader">
                    <span class="z-itemcell z-w30">{gt text="Class name"}</span>
                    <span class="z-itemcell z-w30">{gt text="Class title"}</span>
                </li>
                {gt text="Delete" assign="alt"}
                {assign var='classescount' value=$menutree_linkclasses|@count}
                {foreach from=$menutree_linkclasses key=id item=class}
                <li id="class_{$id}" class="{cycle values='z-odd,z-even'} z-clearfix">
                    <span class="z-itemcell z-w30">
                        <input type="text" name="menutree[linkclasses][{$id}][name]" value="{$class.name|safehtml}" size="40" maxlength="255" />
                    </span>
                    <span class="z-itemcell z-w30">
                        <input type="text" name="menutree[linkclasses][{$id}][title]" value="{$class.title|safehtml}" size="40" maxlength="255" />
                    </span>
                    <span class="z-itemcell z-w20">
                        <a href="#" class="menutree_linkclass_del">{img src='delete_table_row.png' modname='core' set='icons/extrasmall'  alt=$alt}{gt text="Delete class"}</a>
                    </span>
                </li>
                {/foreach}
                <li id="class_{$classescount}" class="{cycle values='z-odd,z-even'} z-clearfix">
                    <span class="z-itemcell z-w30">
                        <input type="text" name="menutree[linkclasses][{$classescount}][name]" size="40" maxlength="255" />
                    </span>
                    <span class="z-itemcell z-w30">
                        <input type="text" name="menutree[linkclasses][{$classescount}][title]" size="40" maxlength="255" />
                    </span>
                    <span class="z-itemcell z-w20">
                        <a href="#" class="menutree_linkclass_del">{img src='delete_table_row.png' modname='core' set='icons/extrasmall'  alt=$alt}{gt text="Delete class"}</a>
                    </span>
                </li>
            </ul>
            {gt text="Add" assign="alt"}
            <a href="#" id="menutree_linkclass_add">{img src='insert_table_row.png' modname='core' set='icons/extrasmall'  alt=$alt}{gt text="Add class"}</a>
        </fieldset>
    </fieldset>
    {/if}
</div>
{/if}

<div id="menutree_tabmenu" class="menutree_tabcontent">
    <div class="z-formrow">
        <ul class="z-menulinks">
            <li><a href="#" id="menutree_newnode">{gt text="Add"}</a></li>
            <li><a href="#" id="menutree_expandall">{gt text="Expand all"}</a></li>
            <li><a href="#" id="menutree_collapseall">{gt text="Collapse all"}</a></li>
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
            <p>{gt text="You can import one of existing menus. To this purpose choose the appropriate menu from the drop-down list. If the chosen menu had marked appropriate option - links to all modules will be imported."}</p>
            <select id="menutree_menus" name="menutree_menus">
                <option value="null">{gt text="Choose menu"}</option>
                {html_options options=$menutree_menus}
            </select>
        </div>
        {/if}
        <div id="menuTreeContainer">
            {$menutree_content}
        </div>
    </div>
</div>

<script type="text/javascript">
    var tabs = new Zikula.UI.Tabs('menutree_tabs',{linkSelector:'li a.menutree_tabcontrol'});

    //add this url
    {{if $menutree_newurl}}
    Event.observe(window, 'load',function() {
        var data = {link_href: '{{$menutree_newurl|safetext}}'};
        $('menutree_tabs').scrollTo();
        Zikula.Menutree.Tree.inst.newNode(data);
    });
    {{/if}}
</script>

{capture assign="itemForm"}
<div id="menutree_form_container" title="{gt text='Edit menu item'}" style="display: none;">
    <p id="forminfo" class="z-warningmsg">{gt text="Field Name is required."}</p>
    <p id="requiredInfo" class="z-errormsg" style="display: none;">{gt text="Please fill required fields"}</p>
    <form action="#" id="nodeBuilder" class="z-form">
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
                    <div class="z-sub z-formnote">
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
                    <div class="z-sub z-formnote">
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
