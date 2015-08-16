{* purpose of this template: routes list view *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{assign var='lctUc' value=$lct|ucfirst}
{include file="`$lctUc`/header.tpl"}
{if count($items) > 1}
{pageaddvar value='jquery-ui' name='javascript'}
<script type="text/javascript">
    jQuery(function(){
        // Return a helper with preserved width of cells
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                jQuery(this).css({width: jQuery(this).width()});
            });
            return ui;
        };

        jQuery('#indicator').hide().removeClass('hidden');

        jQuery("#routesViewForm table tbody").sortable({
            helper: fixHelper,
            items: '> tr.sortable',
            update: function (event, ui) {
                function showIndicator() {
                    jQuery('#indicator').fadeIn();
                }
                function hideIndicator() {
                    jQuery('#indicator').fadeOut();
                }
                showIndicator();
                jQuery.ajax({
                    url: '{{modurl modname='ZikulaRoutesModule' type='ajax' func='sort' assign='url'}}{{$url}}',
                    type: 'POST',
                    data: {
                        ot: 'route',
                        sort: jQuery( "#routesViewForm table tbody" ).sortable( "toArray" )
                    }
                }).always(hideIndicator);
            }
        }).disableSelection();
    });
</script>
<style type="text/css">
    #indicator {
        position: fixed;
        right: 10px;
        top: 10px;
        z-index: 9999;
    }
    .zikularoutesmodule-view .ui-sortable .sortable {
        cursor: move;
    }
</style>
{img set='ajax' modname='core' src='zktimer_48px_white_rounded.gif' id='indicator' class="hidden"}
{/if}
<div class="zikularoutesmodule-route zikularoutesmodule-view">
    {gt text='Route list' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    {if $lct eq 'admin'}
        <h3>
            <span class="fa fa-list"></span>
            {$templateTitle}
        </h3>
    {else}
        <h2>{$templateTitle}</h2>
    {/if}


    <div class="alert alert-warning">
        {gt text='Below you see your current routing configuration (see %s for configuration reference).' tag1="<a href=\"http://jmsyst.com/bundles/JMSI18nRoutingBundle/master/configuration\">JMSI18nRoutingBundle</a>"}
        {gt text="Localisation settings" assign='title'}
        {modurl modname='ZikulaSettingsModule' type='admin' func='multilingual' assign='url'}
        {gt text='You can change your routing configuration in the Settings module: %s.' tag1="<a href=\"`$url`\">`$title`</a>"}
        {gt text='In case one or more installed languages are not listed below, click "Reload multilingual routing settings" in the menu above to reload installed languages.'}
        {$jms_i18n_routing}
    </div>


    {if $canBeCreated}
        {checkpermissionblock component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_COMMENT'}
            {gt text='Create route' assign='createTitle'}
            <a href="{route name='zikularoutesmodule_route_edit' lct=$lct}" title="{$createTitle}" class="fa fa-plus">{$createTitle}</a>
        {/checkpermissionblock}
    {/if}
    {*assign var='own' value=0}
    {if isset($showOwnEntries) && $showOwnEntries eq 1}
        {assign var='own' value=1}
    {/if}
    {assign var='all' value=0}
    {if isset($showAllEntries) && $showAllEntries eq 1}
        {gt text='Back to paginated view' assign='linkTitle'}
        <a href="{route name='zikularoutesmodule_route_view' lct=$lct}" title="{$linkTitle}" class="fa fa-table">{$linkTitle}</a>
        {assign var='all' value=1}
    {else}
        {gt text='Show all entries' assign='linkTitle'}
        <a href="{route name='zikularoutesmodule_route_view' lct=$lct all=1}" title="{$linkTitle}" class="fa fa-table">{$linkTitle}</a>
    {/if*}

    {*include file='Route/view_quickNav.tpl' all=$all own=$own workflowStateFilter=false*}{* see template file for available options *}

    {if $lct eq 'admin'}
    <form action="{route name='zikularoutesmodule_route_handleselectedentries' lct=$lct}" method="post" id="routesViewForm" class="form-horizontal" role="form">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    {/if}
        <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <colgroup>
                {if $lct eq 'admin'}
                    <col id="cSelect" />
                {/if}
                {*<col id="cName" />
                <col id="cBundle" />
                <col id="cController" />
                <col id="cAction" />*}
                <col id="cPath" />
                <col id="cHost" />
                <col id="cCondition" />
                <col id="cDescription" />
                <col id="cBundle" />
                <col id="cUserRoute" />
                {*<col id="cSort" />
                <col id="cGroup" />*}
                <col id="cItemActions" />
            </colgroup>
            <thead>
            <tr>
                {if $lct eq 'admin'}
                    <th id="hSelect" scope="col" align="center" valign="middle">
                        <input type="checkbox" id="toggleRoutes" />
                    </th>
                {/if}
                    <th id="hPath" scope="col" class="text-left">
                    {gt text='Path'}{*sortlink __linktext='Path' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='path' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct*}
                    </th>
                    <th id="hHost" scope="col" class="text-left">
                    {gt text='Host'}{*sortlink __linktext='Host' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='host' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct*}
                    </th>
                    {*<th id="hCondition" scope="col" class="text-left">
                    {sortlink __linktext='Condition' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='condition' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct}
                    </th>*}
                    <th id="hDescription" scope="col" class="text-left">
                    {gt text='Description'}{*sortlink __linktext='Description' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='description' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct*}
                    </th>
                    <th id="hBundle" scope="col" class="text-left">
                    {gt text='Bundle'}{*sortlink __linktext='Bundle' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='bundle' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct*}
                    </th>
                    <th id="hUserRoute" scope="col" class="text-center">
                    {gt text='User route'}{*sortlink __linktext='User route' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='userRoute' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct*}
                    </th>
                    {*<th id="hSort" scope="col" class="text-right">
                        {sortlink __linktext='Sort' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='sort' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct}
                    </th>
                    <th id="hGroup" scope="col" class="text-left">
                        {sortlink __linktext='Group' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='group' sortdir=$sdir all=$all own=$own workflowState=$workflowState searchterm=$searchterm pageSize=$pageSize userRoute=$userRoute lct=$lct}
                    </th>*}
                    <th id="hItemActions" scope="col" class="z-order-unsorted">{gt text='Actions'}</th>
                </tr>
                </thead>
                <tbody>

            {assign var='groupOld' value=null}
            {foreach item='route' from=$items}
                {if $route.group != $groupOld}
                    <tr>
                        <td class="text-left" colspan="{if $lct eq 'admin'}8{else}7{/if}">
                            {$groupMessages[$route.group]}
                        </td>
                    </tr>
                {/if}
                <tr id="row_{$route.id}" {if in_array($route.group, $sortableGroups)}class="sortable"{/if}>
                {if $lct eq 'admin'}
                    <td headers="hselect" align="center" valign="top">
                        <input type="checkbox" name="items[]" value="{$route.id}" class="routes-checkbox" />
                    </td>
                {/if}
                    <td headers="hPath" class="z-left" title="{$route.name}">
                        {$route.path|zikularoutesmodulePathToString:$route}{if count($route.methods) > 0} <span class="small">[{foreach from=$route.methods item='method' name='methods'}{$method}{if !$smarty.foreach.methods.last}, {/if}{/foreach}</span>]{/if}
                    </td>
                    <td headers="hHost" class="z-left">
                        {$route.host}
                    </td>
                    {*<td headers="hCondition" class="z-left">
                        {$route.condition}
                    </td>*}
                    <td headers="hDescription" class="z-left">
                        {$route.description}
                    </td>
                    <td headers="hBundle" class="z-left">
                        {$route.bundle}
                    </td>
                    <td headers="hUserRoute" class="z-center">
                        {$route.userRoute|yesno:true}
                    </td>
                    {*<td headers="hSort" class="z-right">
                        {$route.sort}
                    </td>
                    <td headers="hGroup" class="z-left">
                        {$route.group}
                    </td>*}
                    <td id="itemActions{$route.id}" headers="hItemActions" class="actions nowrap z-w02">
                        {if count($route._actions) gt 0}
                        <div class="dropdown">
                            <a id="itemActions{$route.id}DropDownToggle" role="button" data-toggle="dropdown" data-target="#" href="javascript:void(0);" class="dropdown-toggle"><i class="fa fa-tasks"></i> <span class="caret"></span></a>

                            <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="itemActions{$route.id}DropDownToggle">
                                {foreach item='option' from=$route._actions}
                                    <li role="presentation"><a href="{$option.url.type|zikularoutesmoduleActionUrl:$option.url.func:$option.url.arguments}" title="{$option.linkTitle|safetext}" role="menuitem" tabindex="-1" class="fa fa-{$option.icon}">{$option.linkText|safetext}</a></li>

                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </td>
            </tr>
                {assign var='groupOld' value=$route.group}
        {foreachelse}
            <tr class="z-{if $lct eq 'admin'}admin{else}data{/if}tableempty">
              <td class="text-left" colspan="{if $lct eq 'admin'}7{else}6{/if}">
            {gt text='No routes found.'}
              </td>
            </tr>
        {/foreach}

            </tbody>
        </table>
        </div>

        {if !isset($showAllEntries) || $showAllEntries ne 1}
            {pager rowcount=$pager.numitems limit=$pager.itemsperpage display='page' lct=$lct route='zikularoutesmodule_route_view'}
        {/if}
    {if $lct eq 'admin'}
            <fieldset>
                <label for="zikulaRoutesModuleAction" class="col-lg-3 control-label">{gt text='With selected routes'}</label>
                <div class="col-lg-9">
                <select id="zikulaRoutesModuleAction" name="action" class="form-control">
                    <option value="">{gt text='Choose action'}</option>
                <option value="approve" title="{gt text='Update content and approve for immediate publishing.'}">{gt text='Approve'}</option>
                    <option value="delete" title="{gt text='Delete content permanently.'}">{gt text='Delete'}</option>
                </select>
                </div>
                <input type="submit" value="{gt text='Submit'}" />
            </fieldset>
        </div>
    </form>
    {/if}


    {* here you can activate calling display hooks for the view page if you need it *}
    {if $lct ne 'admin'}
        {notifydisplayhooks eventname='zikularoutesmodule.ui_hooks.routes.display_view' urlobject=$currentUrlObject assign='hooks'}
        {foreach key='providerArea' item='hook' from=$hooks}
            {$hook}
        {/foreach}
    {/if}
</div>
{include file="`$lctUc`/footer.tpl"}

<script type="text/javascript">
    /* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
            $('a.fa-zoom-in').attr('target', '_blank');
        });
    })(jQuery);
    /* ]]> */
</script>
<script type="text/javascript">
/* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            {{if $lct eq 'admin'}}
                {{* init the "toggle all" functionality *}}
                if ($('#toggleRoutes') != undefined) {
                    $('#toggleRoutes').on('click', function (e) {
                        Zikula.toggleInput('routesViewForm');
                        e.stop()
                    });
                }
            {{/if}}
        });
    })(jQuery);
/* ]]> */
</script>
