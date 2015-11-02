{* purpose of this template: routes list view *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{assign var='lctUc' value=$lct|ucfirst}
{include file="`$lctUc`/header.tpl"}
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

    {if $canBeCreated}
        {checkpermissionblock component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_EDIT'}
            {gt text='Create route' assign='createTitle'}
            <a href="{route name='zikularoutesmodule_route_edit' lct=$lct}" title="{$createTitle}" class="fa fa-plus">{$createTitle}</a>
        {/checkpermissionblock}
    {/if}
    {assign var='own' value=0}
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
    {/if}

    {include file='Route/view_quickNav.tpl' all=$all own=$own workflowStateFilter=false}{* see template file for available options *}

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
                <col id="cRouteType" />
                <col id="cReplacedRouteName" />
                <col id="cBundle" />
                <col id="cController" />
                <col id="cAction" />
                <col id="cPath" />
                <col id="cHost" />
                <col id="cSchemes" />
                <col id="cMethods" />
                <col id="cPrependBundlePrefix" />
                <col id="cTranslatable" />
                <col id="cTranslationPrefix" />
                <col id="cCondition" />
                <col id="cDescription" />
                <col id="cSort" />
                <col id="cGroup" />
                <col id="cItemActions" />
            </colgroup>
            <thead>
            <tr>
                {if $lct eq 'admin'}
                    <th id="hSelect" scope="col" align="center" valign="middle">
                        <input type="checkbox" id="toggleRoutes" />
                    </th>
                {/if}
                <th id="hRouteType" scope="col" class="text-left">
                    {sortlink __linktext='Route type' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='routeType' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hReplacedRouteName" scope="col" class="text-left">
                    {sortlink __linktext='Replaced route name' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='replacedRouteName' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hBundle" scope="col" class="text-left">
                    {sortlink __linktext='Bundle' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='bundle' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hController" scope="col" class="text-left">
                    {sortlink __linktext='Controller' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='controller' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hAction" scope="col" class="text-left">
                    {sortlink __linktext='Action' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='action' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hPath" scope="col" class="text-left">
                    {sortlink __linktext='Path' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='path' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hHost" scope="col" class="text-left">
                    {sortlink __linktext='Host' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='host' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hSchemes" scope="col" class="text-left">
                    {sortlink __linktext='Schemes' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='schemes' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hMethods" scope="col" class="text-left">
                    {sortlink __linktext='Methods' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='methods' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hPrependBundlePrefix" scope="col" class="text-center">
                    {sortlink __linktext='Prepend bundle prefix' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='prependBundlePrefix' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hTranslatable" scope="col" class="text-center">
                    {sortlink __linktext='Translatable' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='translatable' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hTranslationPrefix" scope="col" class="text-left">
                    {sortlink __linktext='Translation prefix' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='translationPrefix' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hCondition" scope="col" class="text-left">
                    {sortlink __linktext='Condition' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='condition' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hDescription" scope="col" class="text-left">
                    {sortlink __linktext='Description' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='description' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hSort" scope="col" class="text-right">
                    {sortlink __linktext='Sort' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='sort' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hGroup" scope="col" class="text-left">
                    {sortlink __linktext='Group' currentsort=$sort modname='ZikulaRoutesModule' type='route' func='view' sort='group' sortdir=$sdir all=$all own=$own workflowState=$workflowState routeType=$routeType schemes=$schemes methods=$methods q=$q pageSize=$pageSize prependBundlePrefix=$prependBundlePrefix translatable=$translatable lct=$lct}
                </th>
                <th id="hItemActions" scope="col" class="z-order-unsorted">{gt text='Actions'}</th>
            </tr>
            </thead>
            <tbody>
        
        {foreach item='route' from=$items}
            <tr>
                {if $lct eq 'admin'}
                    <td headers="hselect" align="center" valign="top">
                        <input type="checkbox" name="items[]" value="{$route.id}" class="routes-checkbox" />
                    </td>
                {/if}
                <td headers="hRouteType" class="z-left">
                    {$route.routeType|zikularoutesmoduleGetListEntry:'route':'routeType'|safetext}
                </td>
                <td headers="hReplacedRouteName" class="z-left">
                    {$route.replacedRouteName}
                </td>
                <td headers="hBundle" class="z-left">
                    {$route.bundle}
                </td>
                <td headers="hController" class="z-left">
                    {$route.controller}
                </td>
                <td headers="hAction" class="z-left">
                    {$route.action}
                </td>
                <td headers="hPath" class="z-left">
                    {$route.path}
                </td>
                <td headers="hHost" class="z-left">
                    {$route.host}
                </td>
                <td headers="hSchemes" class="z-left">
                    {$route.schemes|zikularoutesmoduleGetListEntry:'route':'schemes'|safetext}
                </td>
                <td headers="hMethods" class="z-left">
                    {$route.methods|zikularoutesmoduleGetListEntry:'route':'methods'|safetext}
                </td>
                <td headers="hPrependBundlePrefix" class="z-center">
                    {$route.prependBundlePrefix|yesno:true}
                </td>
                <td headers="hTranslatable" class="z-center">
                    {$route.translatable|yesno:true}
                </td>
                <td headers="hTranslationPrefix" class="z-left">
                    {$route.translationPrefix}
                </td>
                <td headers="hCondition" class="z-left">
                    {$route.condition}
                </td>
                <td headers="hDescription" class="z-left">
                    {$route.description}
                </td>
                <td headers="hSort" class="z-right">
                    {$route.sort}
                </td>
                <td headers="hGroup" class="z-left">
                    {$route.group}
                </td>
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
        {foreachelse}
            <tr class="z-{if $lct eq 'admin'}admin{else}data{/if}tableempty">
              <td class="text-left" colspan="{if $lct eq 'admin'}18{else}17{/if}">
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
                <label for="zikulaRoutesModuleAction" class="col-sm-3 control-label">{gt text='With selected routes'}</label>
                <div class="col-sm-6">
                <select id="zikulaRoutesModuleAction" name="action" class="form-control input-sm">
                    <option value="">{gt text='Choose action'}</option>
                    <option value="delete" title="{gt text='Delete content permanently.'}">{gt text='Delete'}</option>
                </select>
                </div>
                <div class="col-sm-3">
                    <input type="submit" value="{gt text='Submit'}" class="btn btn-default btn-sm" />
                </div>
            </fieldset>
        </div>
    </form>
    {/if}

    
    {* here you can activate calling display hooks for the view page if you need it *}
    {*if $lct ne 'admin'}
        {notifydisplayhooks eventname='zikularoutesmodule.ui_hooks.routes.display_view' urlobject=$currentUrlObject assign='hooks'}
        {foreach key='providerArea' item='hook' from=$hooks}
            {$hook}
        {/foreach}
    {/if*}
</div>
{include file="`$lctUc`/footer.tpl"}

<script type="text/javascript">
/* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
            $('a.fa-zoom-in').attr('target', '_blank');
            {{if $lct eq 'admin'}}
                {{* init the "toggle all" functionality *}}
                if ($('#toggleRoutes').length > 0) {
                    $('#toggleRoutes').on('click', function (e) {
                        Zikula.toggleInput('routesViewForm');
                        e.preventDefault();
                    });
                }
            {{/if}}
        });
    })(jQuery);
/* ]]> */
</script>
