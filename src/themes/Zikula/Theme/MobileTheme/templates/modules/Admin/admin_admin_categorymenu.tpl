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
{include file='admin_admin_securityanalyzer.tpl'}
{include file='admin_admin_developernotices.tpl'}
{include file='admin_admin_updatechecker.tpl'}
</div>

{insert name="getstatusmsg"}

<div data-role="controlgroup" data-type="horizontal">
{foreach from=$menuoptions name='menuoption' item='menuoption'}
    <a id="C{$menuoption.cid}" href="{$menuoption.url|safetext}" title="{$menuoption.description|safetext}" data-role="button">{$menuoption.title|safetext}</a>
{/foreach}
</div>


<div class="z-hide" id="admintabs-none"></div>