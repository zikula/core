{pageaddvar name='javascript' value='system/Zikula/Module/AdminModule/Resources/public/js/admin_admin_admintab.js'}
{pageaddvar name='javascript' value='jquery-ui'}


<ol class="breadcrumb">
    <li>{gt text='You are in:'} <a href="{modurl modname='ZikulaAdminModule' type='admin' func='adminpanel'}">{gt text='Administration'}</a></li>

    {if $func neq 'adminpanel'}
        <li><a href="{modurl modname='ZikulaAdminModule' type='admin' func='adminpanel' acid=$currentcat}">{$menuoptions.$currentcat.title|safetext}</a></li>
    {else}
        <li>{$menuoptions.$currentcat.title|safetext}</li>
    {/if}

    {if $func neq 'adminpanel'}
        {foreach from=$menuoptions.$currentcat.items item='moditem'}
            {if $toplevelmodule eq $moditem.modname}
                <li><a href="{modurl modname=$toplevelmodule type='admin' func='index'}">{$moditem.menutext|safetext}</a></li>
                {break}
            {/if}
        {/foreach}

        {if $func neq 'index'}
            <li class="active z-admin-pagefunc">{$func|safetext}</li>
        {/if}
    {/if}
</ol>

<div id="admin-systemnotices">
{include file='admin_admin_securityanalyzer.tpl'}
{include file='admin_admin_developernotices.tpl'}
{include file='admin_admin_updatechecker.tpl'}
</div>

{insert name="getstatusmsg"}

<div class="admintabs-container" id="admintabs-container">

    <ul id="admintab" class="nav nav-mouseover nav-tabs nav-tabs-admin">
        {foreach from=$menuoptions name='menuoption' item='menuoption'}
        <li class="dropdown droppable{if $currentcat eq $menuoption.cid} active{/if}" data-catid="{$menuoption.cid}">
            <a class="dropdown-toggle" href="{$menuoption.url|safetext}">{$menuoption.title|safetext}
            {if count($menuoption.items) > 0}
                <span class="caret"></span>
            {/if}
            </a>
            {if count($menuoption.items) > 0}
                <ul class="dropdown-menu">
                {foreach from=$menuoption.items item="item"}
                    {assign var="modname" value=$item.modname}
                    <li>
                        <a href="{$item.menutexturl}"><img src="{$item.icon}" width=15 heigh=15 style="margin-right:6px">{$item.menutext}</a>
                    </li>
                {/foreach}
                </ul>
            {/if}
        </li>
        {/foreach}
        <li>
            <a id="admintab-addcat-link" href="{modurl modname=ZikulaAdminModule type=admin func=new}" title="{gt text='New module category'}" {*class="tooltips"*} data-placement="top"><span class="icon icon-plus"></span></a>
            <div id="admintab-addcat-popover" class="hide">
                <div class="input-group">
                    <input type="text" class="form-control" name="name" id="admintab-addcat-name" />
                    <span id="admintab-addcat-cancel" class="input-group-addon icon icon-remove icon-red pointer"></span>
                    <span id="admintab-addcat-save" class="input-group-addon icon icon-ok icon-green pointer"></span>
                </div>
            </div>

        </li>
    </ul>
</div>    

    {*helplink*}

<div class="hide" id="admintabs-none"></div>

<script>
    
jQuery('ul.nav-mouseover li.dropdown').hover(function() {
    jQuery(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn();
}, function() {
    jQuery(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut();
});
    
</script>