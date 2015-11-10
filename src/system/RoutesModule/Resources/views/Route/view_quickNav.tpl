{* purpose of this template: routes view filter form *}
{checkpermissionblock component='ZikulaRoutesModule:Route:' instance='::' level='ACCESS_EDIT'}
{assign var='objectType' value='route'}
<form action="{route name='zikularoutesmodule_route_view'}" method="get" id="zikulaRoutesModuleRouteQuickNavForm" class="zikularoutesmodule-quicknav {*form-inline*}navbar-form" role="navigation">
    <fieldset>
        <h3>{gt text='Quick navigation'}</h3>
        <input type="hidden" name="lct" value="{$lct}" />
        <input type="hidden" name="all" value="{$all|default:0}" />
        <input type="hidden" name="own" value="{$own|default:0}" />
        {gt text='All' assign='lblDefault'}
        {if !isset($workflowStateFilter) || $workflowStateFilter eq true}
            <div class="form-group">
                <label for="workflowState">{gt text='Workflow state'}</label>
                <select id="workflowState" name="workflowState" class="form-control input-sm">
                    <option value="">{$lblDefault}</option>
                {foreach item='option' from=$workflowStateItems}
                <option value="{$option.value}"{if $option.title ne ''} title="{$option.title|safetext}"{/if}{if $option.value eq $workflowState} selected="selected"{/if}>{$option.text|safetext}</option>
                {/foreach}
                </select>
            </div>
        {/if}
        {if !isset($routeTypeFilter) || $routeTypeFilter eq true}
            <div class="form-group">
                <label for="routeType">{gt text='Route type'}</label>
                <select id="routeType" name="routeType" class="form-control input-sm">
                    <option value="">{$lblDefault}</option>
                {foreach item='option' from=$routeTypeItems}
                <option value="{$option.value}"{if $option.title ne ''} title="{$option.title|safetext}"{/if}{if $option.value eq $routeType} selected="selected"{/if}>{$option.text|safetext}</option>
                {/foreach}
                </select>
            </div>
        {/if}
        {if !isset($schemesFilter) || $schemesFilter eq true}
            <div class="form-group">
                <label for="schemes">{gt text='Schemes'}</label>
                <select id="schemes" name="schemes" class="form-control input-sm">
                    <option value="">{$lblDefault}</option>
                {foreach item='option' from=$schemesItems}
                <option value="%{$option.value}"{if $option.title ne ''} title="{$option.title|safetext}"{/if}{if "%`$option.value`" eq $formats} selected="selected"{/if}>{$option.text|safetext}</option>
                {/foreach}
                </select>
            </div>
        {/if}
        {if !isset($methodsFilter) || $methodsFilter eq true}
            <div class="form-group">
                <label for="methods">{gt text='Methods'}</label>
                <select id="methods" name="methods" class="form-control input-sm">
                    <option value="">{$lblDefault}</option>
                {foreach item='option' from=$methodsItems}
                <option value="%{$option.value}"{if $option.title ne ''} title="{$option.title|safetext}"{/if}{if "%`$option.value`" eq $formats} selected="selected"{/if}>{$option.text|safetext}</option>
                {/foreach}
                </select>
            </div>
        {/if}
        {if !isset($searchFilter) || $searchFilter eq true}
            <div class="form-group">
                <label for="searchTerm">{gt text='Search'}</label>
                <input type="text" id="searchTerm" name="q" value="{$q}" class="form-control input-sm" />
            </div>
        {/if}
        {if !isset($sorting) || $sorting eq true}
            <div class="form-group">
                <label for="sortBy">{gt text='Sort by'}</label>
                &nbsp;
                <select id="sortBy" name="sort" class="form-control input-sm">
                    <option value="id"{if $sort eq 'id'} selected="selected"{/if}>{gt text='Id'}</option>
                    <option value="routeType"{if $sort eq 'routeType'} selected="selected"{/if}>{gt text='Route type'}</option>
                    <option value="replacedRouteName"{if $sort eq 'replacedRouteName'} selected="selected"{/if}>{gt text='Replaced route name'}</option>
                    <option value="bundle"{if $sort eq 'bundle'} selected="selected"{/if}>{gt text='Bundle'}</option>
                    <option value="controller"{if $sort eq 'controller'} selected="selected"{/if}>{gt text='Controller'}</option>
                    <option value="action"{if $sort eq 'action'} selected="selected"{/if}>{gt text='Action'}</option>
                    <option value="path"{if $sort eq 'path'} selected="selected"{/if}>{gt text='Path'}</option>
                    <option value="host"{if $sort eq 'host'} selected="selected"{/if}>{gt text='Host'}</option>
                    <option value="schemes"{if $sort eq 'schemes'} selected="selected"{/if}>{gt text='Schemes'}</option>
                    <option value="methods"{if $sort eq 'methods'} selected="selected"{/if}>{gt text='Methods'}</option>
                    <option value="prependBundlePrefix"{if $sort eq 'prependBundlePrefix'} selected="selected"{/if}>{gt text='Prepend bundle prefix'}</option>
                    <option value="translatable"{if $sort eq 'translatable'} selected="selected"{/if}>{gt text='Translatable'}</option>
                    <option value="translationPrefix"{if $sort eq 'translationPrefix'} selected="selected"{/if}>{gt text='Translation prefix'}</option>
                    <option value="defaults"{if $sort eq 'defaults'} selected="selected"{/if}>{gt text='Defaults'}</option>
                    <option value="requirements"{if $sort eq 'requirements'} selected="selected"{/if}>{gt text='Requirements'}</option>
                    <option value="condition"{if $sort eq 'condition'} selected="selected"{/if}>{gt text='Condition'}</option>
                    <option value="description"{if $sort eq 'description'} selected="selected"{/if}>{gt text='Description'}</option>
                    <option value="sort"{if $sort eq 'sort'} selected="selected"{/if}>{gt text='Sort'}</option>
                    <option value="group"{if $sort eq 'group'} selected="selected"{/if}>{gt text='Group'}</option>
                    <option value="createdDate"{if $sort eq 'createdDate'} selected="selected"{/if}>{gt text='Creation date'}</option>
                    <option value="createdUserId"{if $sort eq 'createdUserId'} selected="selected"{/if}>{gt text='Creator'}</option>
                    <option value="updatedDate"{if $sort eq 'updatedDate'} selected="selected"{/if}>{gt text='Update date'}</option>
                </select>
            </div>
            <div class="form-group">
                <select id="sortDir" name="sortdir" class="form-control input-sm">
                    <option value="asc"{if $sdir eq 'asc'} selected="selected"{/if}>{gt text='ascending'}</option>
                    <option value="desc"{if $sdir eq 'desc'} selected="selected"{/if}>{gt text='descending'}</option>
                </select>
            </div>
        {else}
            <input type="hidden" name="sort" value="{$sort}" />
            <input type="hidden" name="sdir" value="{if $sdir eq 'desc'}asc{else}desc{/if}" />
        {/if}
        {if !isset($pageSizeSelector) || $pageSizeSelector eq true}
            <div class="form-group">
                <label for="num">{gt text='Page size'}</label>
                <select id="num" name="num" class="form-control input-sm" style="min-width: 70px">
                    <option value="5"{if $pageSize eq 5} selected="selected"{/if}>5</option>
                    <option value="10"{if $pageSize eq 10} selected="selected"{/if}>10</option>
                    <option value="15"{if $pageSize eq 15} selected="selected"{/if}>15</option>
                    <option value="20"{if $pageSize eq 20} selected="selected"{/if}>20</option>
                    <option value="30"{if $pageSize eq 30} selected="selected"{/if}>30</option>
                    <option value="50"{if $pageSize eq 50} selected="selected"{/if}>50</option>
                    <option value="100"{if $pageSize eq 100} selected="selected"{/if}>100</option>
                </select>
            </div>
        {/if}
        {if !isset($prependBundlePrefixFilter) || $prependBundlePrefixFilter eq true}
            <div class="form-group">
                <label for="prependBundlePrefix">{gt text='Prepend bundle prefix'}</label>
                <select id="prependBundlePrefix" name="prependBundlePrefix" class="form-control input-sm">
                    <option value="">{$lblDefault}</option>
                {foreach item='option' from=$prependBundlePrefixItems}
                    <option value="{$option.value}"{if $option.value eq $prependBundlePrefix} selected="selected"{/if}>{$option.text|safetext}</option>
                {/foreach}
                </select>
            </div>
        {/if}
        {if !isset($translatableFilter) || $translatableFilter eq true}
            <div class="form-group">
                <label for="translatable">{gt text='Translatable'}</label>
                <select id="translatable" name="translatable" class="form-control input-sm">
                    <option value="">{$lblDefault}</option>
                {foreach item='option' from=$translatableItems}
                    <option value="{$option.value}"{if $option.value eq $translatable} selected="selected"{/if}>{$option.text|safetext}</option>
                {/foreach}
                </select>
            </div>
        {/if}
        <input type="submit" name="updateview" id="quicknavSubmit" value="{gt text='OK'}" class="btn btn-default btn-sm" />
    </fieldset>
</form>

<script type="text/javascript">
/* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            zikulaRoutesInitQuickNavigation('route');
            {{if isset($searchFilter) && $searchFilter eq false}}
                {{* we can hide the submit button if we have no quick search field *}}
                $('#quicknavSubmit').addClass('hidden');
            {{/if}}
        });
    })(jQuery);
/* ]]> */
</script>
{/checkpermissionblock}
