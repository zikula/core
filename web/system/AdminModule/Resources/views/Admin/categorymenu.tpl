{ajaxheader modname='AdminModule' filename='Admin/ajax.js' ui=true}

<script type="text/javascript">
    /* <![CDATA[ */
    var lblclickToEdit = "{{gt text='Right-click down arrows to edit tab name'}}";
    var lblEdit = "{{gt text='Edit category'}}";
    var lblDelete = "{{gt text='Delete category'}}";
    var lblMakeDefault = "{{gt text='Make default category'}}";
    var lblSaving = "{{gt text='Saving'}}";
    /* ]]> */
</script>

<div class="z-admin-breadcrumbs">
    <span class="z-sub">{gt text='You are in:'}</span>
    <span class="z-breadcrumb"><a href="{modurl modname='AdminModule' type='admin' func='adminpanel'}">{gt text='Administration'}</a></span>

    <span class="z-sub">&raquo;</span>
    {if $func neq 'adminpanel'}
        <span class="z-breadcrumb"><a href="{modurl modname='AdminModule' type='admin' func='adminpanel' acid=$currentcat}">{$menuoptions.$currentcat.title|safetext}</a></span>
    {else}
        <span class="z-breadcrumb">{$menuoptions.$currentcat.title|safetext}</span>
    {/if}

    {if $func neq 'adminpanel'}
        <span class="z-sub">&raquo;</span>
        {foreach from=$menuoptions.$currentcat.items item='moditem'}
            {if $toplevelmodule eq $moditem.modname}
                <span class="z-breadcrumb"><a href="{modurl modname=$toplevelmodule type='admin' func='index'}" class="z-admin-pagemodule">{$moditem.menutext|safetext}</a></span>
                {break}
            {/if}
        {/foreach}

        {if $func neq 'index'}
            <span class="z-sub">&raquo;</span>
            <span class="z-breadcrumb z-admin-pagefunc">{$func|safetext}</span>
        {/if}
    {/if}
</div>

<div id="admin-systemnotices">
{include file='Admin/securityanalyzer.tpl'}
{include file='Admin/developernotices.tpl'}
{include file='Admin/updatechecker.tpl'}
</div>

{insert name="getstatusmsg"}

<div class="admintabs-container" id="admintabs-container">
    <ul id="admintabs" class="z-clearfix">
        {foreach from=$menuoptions name='menuoption' item='menuoption'}
        <li id="admintab_{$menuoption.cid}" class="admintab {if $currentcat eq $menuoption.cid} active{/if}" style="z-index:0;">
            <a id="C{$menuoption.cid}" href="{$menuoption.url|safetext}" title="{$menuoption.description|safetext}">{$menuoption.title|safetext}</a>
            <span id="catcontext{$menuoption.cid}" class="z-admindrop">&nbsp;</span>

            <script type="text/javascript">
            /* <![CDATA[ */
                var context_catcontext{{$menuoption.cid}} = new Control.ContextMenu('catcontext{{$menuoption.cid}}',{
                    leftClick: true,
                    animation: false
                });

                {{foreach from=$menuoption.items item=item}}
                    context_catcontext{{$menuoption.cid}}.addItem({
                        label: '{{$item.menutext|safetext}}',
                        callback: function(){window.location = '{{$item.menutexturl}}';}
                    });
                {{/foreach}}

            /* ]]> */
            </script>

        </li>
        {/foreach}
        <li id="addcat">
            <a id="addcatlink" href="{modurl modname=AdminModule type=admin func=new}" title="{gt text='New module category'}" onclick='return Admin.Category.New(this);'>&nbsp;</a>
        </li>
    </ul>

    {helplink}
    {include file='Admin/ajaxAddCategory.tpl'}
</div>

<div class="z-hide" id="admintabs-none"></div>