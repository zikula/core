{ajaxheader modname='Admin' ui=true filename='adminpanel.js'}
{pageaddvar name='javascript' value='zikula.contextmenu'}
{pageaddvar name='javascript' value='zikula.template'}

<div class="z-admin-breadcrumbs">
    <span class="z-sub">{gt text='You are in:'}</span>
    <span class="z-breadcrumb"><a href="{modurl modname='Admin' type='admin' func='adminpanel'}">{gt text='Administration'}</a></span>

    <span class="z-sub">&raquo;</span>
    {if $func neq 'adminpanel'}
        <span class="z-breadcrumb"><a href="{modurl modname='Admin' type='admin' func='adminpanel' acid=$currentcat}">{$menuoptions.$currentcat.title|safetext}</a></span>
    {else}
        <span class="z-breadcrumb">{$menuoptions.$currentcat.title|safetext}</span>
    {/if}

    {if $func neq 'adminpanel'}
        <span class="z-sub">&raquo;</span>
        {foreach from=$menuoptions.$currentcat.items item='moditem'}
            {if $toplevelmodule eq $moditem.modname}
                <span class="z-breadcrumb"><a href="{modurl modname=$toplevelmodule type='admin' func='main'}" class="z-admin-pagemodule">{$moditem.menutext|safetext}</a></span>
                {break}
            {/if}
        {/foreach}

        {if $func neq 'main'}
            <span class="z-sub">&raquo;</span>
            <span class="z-breadcrumb z-admin-pagefunc">{$func|safetext}</span>
        {/if}
    {/if}
</div>

<div id="admin-systemnotices">
{include file='includes/securityanalyzer.tpl'}
{include file='includes/developernotices.tpl'}
{include file='includes/updatechecker.tpl'}
</div>

{insert name="getstatusmsg"}
<div class="admintabs-container" id="admintabs-container">
    <ul id="admintabs" class="z-clearfix">
        {foreach from=$categoriesData name='category' item='category'}
        <li id="admintab_{$category.cid}" class="admintab {if $currentcat eq $category.cid} active{/if}" data-cid="{$category.cid}" data-menuitems="{$category.items|@json_encode|safetext}">
            <a href="{$category.url|safetext}" title="{$category.description|safetext}">{$category.title|safetext}</a>
            <span class="z-admindrop">&nbsp;</span>
        </li>
        {/foreach}
        <li id="addcat">
            <a id="addcatlink" href="{modurl modname='Admin' type='admin' func='newcat'}" title="{gt text='New module category'}">&nbsp;</a>
            {include file='ajax/add_category.tpl'}
        </li>
    </ul>

    {helplink}
</div>
<script type="text/template" id="admintab-template">
    <li id="admintab_<%= vars.id %>" class="admintab" data-cid="<%= vars.id %>" data-menuitems="<%= vars.menuitems %>">
        <a href="<%= vars.url %>" title="<%= vars.description %>"><%= vars.title %></a>
        <span class="z-admindrop">&nbsp;</span>
    </li>
</script>
<script type="text/template" id="admintab-edit">
    <form id="inplaceeditor-form-<%= vars.id %>" class="inplaceeditor-form">
        <input type="text" name="title" class="editor_field" value="<%= vars.title %>"/>
    </form>
</script>
