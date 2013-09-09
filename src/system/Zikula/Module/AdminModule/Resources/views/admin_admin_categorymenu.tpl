{ajaxheader modname='ZikulaAdminModule' filename='admin_admin_ajax.js' ui=true}

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
<input type="hidden" name="admintabs-menuoptions" id="admintabs-menuoptions" value="{$menuoptions|@json_encode|escape}" />
<div class="admintabs-container" id="admintabs-container">
    <ul id="admintabs" class="clearfix">
        {foreach from=$menuoptions name='menuoption' item='menuoption'}
        <li id="admintab_{$menuoption.cid}" class="admintab {if $currentcat eq $menuoption.cid} active{/if}" style="z-index:0;">
            <a href="{$menuoption.url|safetext}" title="{$menuoption.description|safetext}">{$menuoption.title|safetext}</a>
            <span class="z-admindrop">&nbsp;</span>
        </li>
        {/foreach}
        <li id="addcat"> 
            <a id="addcatlink" class="icon icon-plus" href="{modurl modname=ZikulaAdminModule type=admin func=new}" title="{gt text='New module category'}">&nbsp;</a>
            {include file='admin_admin_ajaxAddCategory.tpl'}
        </li>
    </ul>

    {helplink}
</div>

<div class="hide" id="admintabs-none"></div>