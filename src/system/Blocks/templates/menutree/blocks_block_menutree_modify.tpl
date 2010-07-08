{ajaxheader modname="blocks" filename="menutree.js" effects=1 dragdrop=1}
{pageaddvar name="javascript" value="system/Blocks/javascript/cookiejar.js"}
{pageaddvar name="javascript" value="system/Blocks/javascript/functions.js"}
{pageaddvar name="javascript" value="system/Blocks/javascript/contextmenu.js"}
{pageaddvar name="stylesheet" value="system/Blocks/style/menutree/adminstyle.css"}
{pageaddvar name="stylesheet" value="system/Blocks/style/menutree/contextmenu.css"}

{if !empty($redirect)}
<input type="hidden" name="redirect" value="{$redirect}" />
{else}
<input type="hidden" id="returntoblock" name="returntoblock" value="{$blockinfo.bid}" />
{/if}

<ul id="menutree_tabs" class="minitabs">
    <li><a href="#menutree_tabmenu" id="menutree_tabmenu_control" class="menutree_tabcontrol">{gt text="Block content"}</a></li>
    {if $menutree_anysettingsaccess}
    <li><a href="#menutree_tabsettings" id="menutree_tabsettings_control" class="menutree_tabcontrol">{gt text="Block settings"}</a></li>
    {/if}
    <li><a href="#menutree_tabhelp" id="menutree_tabhelp_control" class="menutree_tabcontrol">{gt text="Help"}</a></li>
</ul>

{if $menutree_anysettingsaccess}
<div id="menutree_tabsettings" class="menutree_tabcontent">
    {if $menutree_adminaccess}
    <fieldset>
        <legend>{gt text="Permissions"}</legend>
        <p class="z-formnote z-informationmsg">{gt text='You can restrict access to certain options setting higher than the default ("Edit") permissions level. Below is a list of these options.'}</p>
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
        <p class="z-formnote z-informationmsg">{gt text="You can specify diferent block title for each language. If you leave it blank - default block title (shown above) will be displayed."}</p>
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
        <p class="z-formnote z-informationmsg">{gt text='<strong>Note</strong>: some templates and/or stylesheets are found only in certain themes. These templates and stylesheets have been included in the "Only in some themes" group. Choosing a template or a stylesheet from this group you must take into account the fact that it might not be available in certain theme - in such situation default template and style ("menutree_block_menutree.htm") is used.'}</p>
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
                <option value="null">{gt text="choose stylesheet"}</option>
                {html_options options=$styles selected=$menutree_stylesheet}
            </select>
            <p id="menutree_stylesheet_helper" class="z-formnote z-informationmsg" style="display: none;">{gt text='The list of stylesheets has been limited to those which appear to be associated with selected template.<br />If you do not find among them stylesheet you are looking for - you can <a href="#" title="Click here, to display all stylesheets">show all stylesheets</a>.'}</p>
        </div>
        <div class="z-formrow">
            <label for="menutree_editlinks">{gt text="Show editing links"}</label>
            <input id="menutree_editlinks" type="checkbox" name="menutree[editlinks]" {if $menutree_editlinks}checked="checked"{/if}/>
            <em class="z-sub">{gt text='Display in block links: "Add current URL" and "Edit this block".'}</em>
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
            {configgetvar name="shorturls" assign="shorturls"}
            {if $shorturls}
            <p class="z-formnote z-warningmsg">
                {gt text="<strong>Note! Short URL-s are turn on. You're strongly recommended to turn on this option when using short URL-s.</strong>"}
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
            <p class="z-formnote z-informationmsg">{gt text="You can give the CSS class for each link in the menu. This option allows you to prepare a list of classes, which you will be able to choose."}</p>
        </div>
        <fieldset id="menutree_linkclasses_group">
            <legend>{gt text="Class list for links"}</legend>
            <p>{gt text='The title of the class will be visible to the user, the name of the class will be used as an "class" attribute value.'}</p>
            <ul id="menutree_linkclasses_list">
                <li class="head z-clearfix"><span>{gt text="Class name"}</span><span>{gt text="Class title"}</span></li>
                {assign var='classescount' value=$menutree_linkclasses|@count}
                {foreach from=$menutree_linkclasses key=id item=class}
                <li id="class_{$id}">
                    <input type="text" name="menutree[linkclasses][{$id}][name]" value="{$class.name|safehtml}" size="40" maxlength="255" />
                    <input type="text" name="menutree[linkclasses][{$id}][title]" value="{$class.title|safehtml}" size="40" maxlength="255" />
                    {gt text="Delete" assign="alt"}
                    <a href="#" class="menutree_linkclass_del">{img src='delete_table_row.gif' modname='core' set='icons/extrasmall'  alt=$alt}{gt text="Delete class"}</a>
                </li>
                {/foreach}
                <li id="class_{$classescount}">
                    <input type="text" name="menutree[linkclasses][{$classescount}][name]" size="40" maxlength="255" />
                    <input type="text" name="menutree[linkclasses][{$classescount}][title]" size="40" maxlength="255" />
                    <a href="#" class="menutree_linkclass_del">{img src='delete_table_row.gif' modname='core' set='icons/extrasmall'  alt=$alt}{gt text="Delete class"}</a>
                </li>
            </ul>
            {gt text="Add" assign="alt"}
            <a href="#" id="menutree_linkclass_add">{img src='insert_table_row.gif' modname='core' set='icons/extrasmall'  alt=$add}{gt text="Add class"}</a>
        </fieldset>
    </fieldset>
    {/if}
</div>
{/if}

<div id="menutree_tabmenu" class="menutree_tabcontent">
    <div class="z-formrow">
        <div class="menuTreeOptions">
            <p>[ <a href="#" id="menutree_newnode">{gt text="Add"}</a> | <a href="#" id="menutree_expandall">{gt text="Expand all"}</a> | <a href="#" id="menutree_collapseall">{gt text="Collapse all"}</a> ]</p>
            {if $multilingual}
            <p>[ {gt text="Change active language: "}
                {foreach from=$languages key=code item=name name=langloop}
                <a href="#" lang="{$code}" class="menutree_langcontrols{if $code == $defaultanguage} activelang{/if}">{$name}</a> {if !$smarty.foreach.langloop.last}|{/if}
                {/foreach}
            ]</p>
            {/if}
        </div>

        {if !empty($menutree_menus)}
        <div class="menuTreeOptions">
            <p>{gt text="You can import one of existing menus. To this purpose choose the appropriate menu from the drop-down list. If the chosen menu had marked appropriate option - links to all modules will be imported."}</p>
            <select id="menutree_menus" name="menutree_menus">
                <option value="null">{gt text="choose menu"}</option>
                {html_options options=$menutree_menus}
            </select>
        </div>
        {/if}
        <div id="menuTreeContainer">
            {admmenutree data=$menutree_content id='adm-menutree' nodeprefix='node_'}
        </div>

        <script type="text/javascript">
            //<![CDATA[
            // some config for js functions
            var MTConfig = new Object;
            MTConfig.cookieName = 'menutree_{{$blockinfo.bid}}';

            var tconfig = {
                treeElement:    'adm-menutree'
                ,formToObserve: 'blockupdateform'
                ,formElement:   'menutree_content'
                ,imagesDir:     'modules/menutree/pnimages/'
                {{*if $multilingual*}}
                ,langs:         ['{{$languages|@array_keys|@implode:"','"}}']
                {{*/if*}}
                {{if $menutree_linkclass && $classescount > 0}}
                ,linkclasses:   [{{foreach from=$menutree_linkclasses item=class name=classloop}}
                {'{{$class.name}}':'{{$class.title}}'}{{if !$smarty.foreach.classloop.last}},{{/if}}
                {{/foreach}}]
                {{/if}}
                ,maxDepth: {{$menutree_maxdepth|default:0}}
                {{if $menutree_stripbaseurl}}
                ,stripbaseurl:       true
                ,baseurl:            '{{$baseurl}}'
                ,cookieName: MTConfig.cookieName
                {{/if}}
                ,langLabels: {
                    delConfirm:         '{{gt text="Do you really want to delete this element and all of it subelements?"}}',
                    linkname:           '{{gt text="Name"}}',
                    linkhref:           '{{gt text="URL"}}',
                    linktitle:          '{{gt text="Title"}}',
                    linkclass:          '{{gt text="CSS class"}}',
                    linkclassblank:     '{{gt text="choose class"}}',
                    linklang:           '{{gt text="Language"}}',
                    linkstate:          '{{gt text="Active?"}}',
                    activate:           '{{gt text="Activate"}}',
                    deactivate:         '{{gt text="Deactivate"}}',
                    edit:               '{{gt text="Edit"}}',
                    remove:             '{{gt text="Delete"}}',
                    add:                '{{gt text="Add new..."}}',
                    before:             '{{gt text="before"}}',
                    after:              '{{gt text="after"}}',
                    bottom:             '{{gt text="as child"}}',
                    expand:             '{{gt text="Expand this node"}}',
                    collapse:           '{{gt text="Collapse this node"}}',
                    multitoggle:        '{{gt text="State"}}',
                    multiactivate:      '{{gt text="Activate for all languages"}}',
                    multideactivate:    '{{gt text="Deactivate for all languages"}}',
                    usedefault:         '{{gt text="Use one for all languages"}}',
                    cancel:             '{{gt text="Cancel"}}',
                    submit:             '{{gt text="Submit"}}',
                    required:           '{{gt text="Please fill required fields"}}',
                    forminfo:           '{{gt text="Field Name is required."}}',
                    maxdepthreached:    '{{gt text="Maximum depth of tree exceeded. Limit is: %s"}}',
                    warnbeforeunload:   '{{gt text="You have unsaved changes, which will be lost if you leave this page without saving!"}}'
                }
            };
            var tree = new myTree(tconfig);

            //add this url
            {{if $menutree_newurl}}
            var data = {linkhref: '{{$menutree_newurl|safetext}}'};
            tree.newNode(data);
            {{/if}}

            //]]>
        </script>

    </div>
</div>
<div id="menutree_tabhelp" class="menutree_tabcontent">
    <div>{gt text='<p>Block "Tree-like menu (menutree)" allows you to create the extended menu with nested structure. Below you will find some basic information. </p><h4>How to start?</h4><p>To <strong>add the first item</strong> to the menu, click link "<strong>Add</strong>". You will get a form in which you must enter the name of the item and possibly other parameters (title, address, css class). At the top of the form you can find drop-down list with installed languages, which allows switching between languages.</p><p>While adding a new block, you can <strong>import </strong>an existing menu (blocks: "menu", "extmenu" and "menutree"). To this purpose choose a block from the drop-down list.</p><h4>The basic options</h4><p>To <strong>move </strong>an item, simply <strong>grab a folder icon</strong> and drop it on desired position.</p><p><strong>Clicking the name</strong> of the item, you can view the <strong>context menu</strong>, which includes the following options:</p><ul><li><strong>Edit</strong>: opens form to edit</li><li><strong>Delete</strong>: removes selected element and all its subelements</li><li><strong>Add new...</strong><ul><li><strong>before</strong>: opens form and adds new item before selected element</li><li><strong>after</strong>: opens form and adds new item after selected element</li><li><strong>as child</strong>:opens form and adds new item as a child of selected element</li></ul></li><li><strong>Expand (Collapse) this node</strong>: expands or collapses selected node</li><li><strong>Deactivate (Activate)</strong>: turns on or off selected element (and all its subitems) for the current language</li><li><strong>State</strong><ul><li><strong>Activate for all languages</strong>: turns on selected element (and all its subitems) for all languages</li><li><strong>Deactivate for all languages</strong>:turns off selected element (and all its subitems) for all languages </li></ul></li></ul><h4>Multilingual options</h4><p>If site has installed many languages - menu will include all language versions. Each menu element can have separate name and title for each language. Url adress and css class may (but not need) be common for all languages.</p><p>Above the menu is displayed list of available languages (current language is marked). By clicking language name you can change displayed language.</p><h4>More information</h4><p>If you want to learn more, read the<strong> help.txt</strong> file, included in the package.</p>'}</div>
</div>
