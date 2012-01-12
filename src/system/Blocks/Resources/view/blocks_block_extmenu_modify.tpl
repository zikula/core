{ajaxheader modname="Blocks" filename="blocks.js"}
{pageaddvar name="javascript" value="javascript/helpers/Zikula.itemlist.js"}
{pageaddvar name="stylesheet" value="system/Blocks/style/extmenu_modify.css"}
{pageaddvarblock}
    <script type="text/javascript">
        /* <![CDATA[ */
        var list_menuitemlist = null;
        document.observe("dom:loaded",function(){
            list_menuitemlist = new Zikula.itemlist('menuitemlist', {headerpresent: true, firstidiszero: true, recursive: true, inputName: 'linksorder'});
            $('appendmenuitem').observe('click',function(event){
                list_menuitemlist.appenditem();
                event.stop();
            })
        });
        /* ]]> */
    </script>
{/pageaddvarblock}

{if $redirect neq ''}
<input type="hidden" name="redirect" value="{$redirect}" />
{/if}

<h4>{gt text="Template" domain='zikula'}</h4>
<div class="z-formrow">
    <label for="blocks_menu_template">{gt text="Template for this block"}</label>
    <input id="blocks_menu_template" type="text" name="template" size="30" maxlength="60" value="{$template|safetext}" />
</div>

<h4>{gt text="CSS styling" domain='zikula'}</h4>
<div class="z-formrow">
    <label for="blocks_menu_stylesheet">{gt text="Style sheet" domain='zikula'}</label>
    <input id="blocks_menu_stylesheet" type="text" name="stylesheet" size="20" value="{$stylesheet|safetext}" />
</div>

<h4>{gt text="Visibility within block"}</h4>
<div class="z-formrow">
    <label for="blocks_menu_modules">{gt text="Display links to installed modules"}</label>
    <input id="blocks_menu_modules" type="checkbox" value="1" name="displaymodules"{if $displaymodules} checked="checked"{/if} />
</div>

<h4>{gt text="Block titles"}</h4>
<p class="z-informationmsg">{gt text="These block titles override the title entered in the Blocks/extmenu section at the top of this page. If you enter titles here, it is these titles here that will be displayed in site pages. The title entered under Blocks/extmenu will be the title seen in the blocks list in the Blocks administration." domain='zikula'}</p>

{foreach item=blocktitle from=$blocktitles key=lang}
<div class="z-formrow">
    <label for="blocktitle_{$lang}">{$lang}:</label>
    <input type="text" id="blocktitle_{$lang}" name="blocktitles[{$lang}]" size="30" maxlength="60" value="{$blocktitle}" />
</div>
{/foreach}


<h4 id="editmenu">{gt text="Content" domain='zikula'}</h4>
<p class="z-informationmsg">{gt text="In the table below, drag and drop the menu items into your desired order. It is also possible to create nesting menu entries if you move an entry onto an existing row. The item order and the contents of each menu item are saved when you click the 'Save' button. The number in the right-hand column is the ID of the menu item. Use this ID number if you want to use permission rules to restrict access to the menu item." domain='zikula'}</p>
<p class="z-informationmsg">{gt text="You can use bracket URLs in the form: &#123;modname&#125;, &#123;modname:type:func&#125; or &#123;modname:type:func:param1=value1&amp;param2=value2&#125;.  You may also specify the homepage as &#123;homepage&#125;." domain='zikula'}</p>
<p style="margin:2em 0; padding:0;"><a id="appendmenuitem" class="z-icon-es-new" href="#">{gt text="Create new menu item" domain='zikula'}</a></p>

{* return to this block after saving *}
<input type="hidden" id="returntoblock" name="returntoblock" value="{$blockinfo.bid}" />
<input type="hidden" id="linksorder" name="linksorder" value="" />
{menu from=$links item='menuitem' key='itemid' name='extmenu' id='menuitemlist' class='z-itemlist' tag='ol' multilang=true}
    {if $extmenu.first}
    <li class="z-clearfix z-itemheader">
        <span class="z-itemcell z-w05"><strong>&nbsp;</strong></span>
        <span class="z-itemcell z-w20"><strong>{gt text="Image" domain='zikula'}</strong></span>
        <span class="z-itemcell z-w22"><strong>{gt text="Name" domain='zikula'}</strong></span>
        <span class="z-itemcell z-w22"><strong>{gt text="URL" domain='zikula'}</strong></span>
        <span class="z-itemcell z-w15"><strong>{gt text="Title" domain='zikula'}&nbsp;<em>({gt text="optional" domain='zikula'})</em></strong></span>
        <span class="z-itemcell z-w10"><strong>{gt text="Active" domain='zikula'}</strong></span>
    </li>
    {/if}
    {if $extmenu.total > 0}
    <li id="li_menuitemlist_{$itemid}" class="{cycle values='z-odd,z-even'} z-sortable z-clearfix">
        {foreach key='thislanguage' item='item' from=$menuitem}
        <div class="z-clearfix">
            <span class="z-itemcell z-w05">
                {$thislanguage}
            </span>
            <span class="z-itemcell z-w20">
                <input type="text" id="links_{$thislanguage}_{$itemid}_image" name="links[{$thislanguage}][{$itemid}][image]" size="25" maxlength="255" value="{$item.image|safetext}" />
            </span>
            <span class="z-itemcell z-w22">
                <input type="text" id="links_{$thislanguage}_{$itemid}_name"  name="links[{$thislanguage}][{$itemid}][name]" size="25" maxlength="255" value="{$item.name|safetext}" />
            </span>
            <span class="z-itemcell z-w22">
                <input type="text" id="links_{$thislanguage}_{$itemid}_url"   name="links[{$thislanguage}][{$itemid}][url]" size="25" maxlength="255" value="{$item.url|safetext}" />
            </span>
            <span class="z-itemcell z-w15">
                <input type="text" id="links_{$thislanguage}_{$itemid}_title" name="links[{$thislanguage}][{$itemid}][title]" size="25" maxlength="255" value="{$item.title|safetext}" />
            </span>
            <span class="z-itemcell z-w10">
                <input type="checkbox" id="links_{$thislanguage}_{$itemid}_active" name="links[{$thislanguage}][{$itemid}][active]" {if isset($item.active) && $item.active}checked="checked"{/if} value="1" />
                {if $thislanguage == $userlanguage}
                <button type="button" class="imagebutton-nofloat buttondelete" id="buttondelete_menuitemlist_{$itemid}">{img src='14_layer_deletelayer.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' }</button>
                (<span class="itemid">{$itemid}</span>)
                {/if}
            </span>
        </div>
        {/foreach}
        {if $item.errors|@count != 0}
        <ul class="errorlist">
            {foreach item=error from=$item.errors}
            <li>
                {gt text="Error! Encountered a problem:"}&nbsp;{$error}
            </li>
            {/foreach}
        </ul>
        {/if}
    </li>
    {/if}
{/menu}

<ul style="display:none">
    <li id="menuitemlist_emptyitem" class="z-sortable z-clearfix" style="padding-left: 30px !important;">
        {foreach item='thislanguage' from=$languages}
        <div class="z-clearfix">
            <span class="z-itemcell z-w05">
                {$thislanguage}
            </span>
            <span class="z-itemcell z-w20">
                <input type="text" class="listinput" id="links_{$thislanguage}_X_image" name="dummy[]" size="25" maxlength="255" value="" />
            </span>
            <span class="z-itemcell z-w22">
                <input type="text" class="listinput" id="links_{$thislanguage}_X_name"  name="dummy[]" size="25" maxlength="255" value="" />
            </span>
            <span class="z-itemcell z-w22">
                <input type="text" class="listinput" id="links_{$thislanguage}_X_url"   name="dummy[]" size="25" maxlength="255" value="" />
            </span>
            <span class="z-itemcell z-w15">
                <input type="text" class="listinput" id="links_{$thislanguage}_X_title" name="dummy[]" size="25" maxlength="255" value="" />
            </span>
            <span class="z-itemcell z-w10">
                <input class="listinput" type="checkbox" id="links_{$thislanguage}_X_active" name="dummy[]" checked="checked" value="1" />
                {if $thislanguage == $userlanguage}
                <button type="button" class="imagebutton-nofloat buttondelete" id="buttondelete_menuitemlist_X">{img src='14_layer_deletelayer.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' }</button>
                (<span class="itemid"></span>)
                {/if}
            </span>
        </div>
        {/foreach}
    </li>
</ul>
