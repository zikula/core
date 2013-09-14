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

    
    
    <ul id="admintab" class="nav nav-tabs nav-tabs-admin">
        {foreach from=$menuoptions name='menuoption' item='menuoption'}
        <li class="dropdown droppable{if $currentcat eq $menuoption.cid} active{/if}" data-catid="{$menuoption.cid}">
            <a class="dropdown-toggle" href="{$menuoption.url|safetext}">{$menuoption.title|safetext}
                <span class="icon icon-caret-down{if count($menuoption.items) == 0} admintab-action{/if}"></span>
            </a>
            
                <ul class="dropdown-menu" >
                    <li class="admintab-action admintab-action-makedefault">
                        <a href="#"><span class="icon icon-asterisk icon-fixed-width" style="padding:0 17px 0 3px"></span> {gt text='Make default category'}</a>
                    </li>
                    <li class="admintab-action admintab-action-edit">
                        <a href="#"><span class="icon icon-pencil icon-fixed-width" style="padding:0 17px 0 3px"></span> {gt text='Edit category'}</a>
                    </li>
                    <li class="admintab-action admintab-delete">
                        <a href="#"><span class="icon icon-trash icon-fixed-width" style="padding:0 17px 0 3px"></span> {gt text='Delete category'}</a>
                    </li>
                    {if count($menuoption.items) > 0}
                    <li class="divider"></li>
                    {/if}
                    {foreach from=$menuoption.items item="item"}
                    <li>
                        <a href="{$item.menutexturl}"><img src="{$item.icon}" width=15 heigh=15 style="margin-right:6px">{$item.menutext}</a>
                    </li>
                    {/foreach}
                </ul>
            
        </li>
        {/foreach}
        <li id="admintab-edit">
            <a href="#" title="{gt text='Edit'}" data-placement="top"><span class="icon icon-lock icon-fixed-width"></span></a>
        </li>
        <li class="admintab-add">
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
