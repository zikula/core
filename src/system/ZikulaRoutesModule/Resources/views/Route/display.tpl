{* purpose of this template: routes display view *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{assign var='lctUc' value=$lct|ucfirst}
{include file="`$lctUc`/header.tpl"}
<div class="zikularoutesmodule-route zikularoutesmodule-display">
    {gt text='Route' assign='templateTitle'}
    {assign var='templateTitle' value=$route->getTitleFromDisplayPattern()|default:$templateTitle}
    {pagesetvar name='title' value=$templateTitle|@html_entity_decode}
    {if $lct eq 'admin'}
    <h3>
        <span class="fa fa-eye"></span>
        {$templateTitle|notifyfilters:'zikularoutesmodule.filter_hooks.routes.filter'} <small>({$route.workflowState|zikularoutesmoduleObjectState:false|lower})</small>{icon id='itemActionsTrigger' type='options' size='extrasmall' __alt='Actions' class='cursor-pointer hidden'}
    </h3>
    {else}
        <h2>{$templateTitle|notifyfilters:'zikularoutesmodule.filter_hooks.routes.filter'} <small>({$route.workflowState|zikularoutesmoduleObjectState:false|lower})</small>{icon id='itemActionsTrigger' type='options' size='extrasmall' __alt='Actions' class='cursor-pointer hidden'}</h2>
    {/if}

    <dl>
        <dt>{gt text='State'}</dt>
        <dd>{$route.workflowState|zikularoutesmoduleGetListEntry:'route':'workflowState'|safetext}</dd>
        <dt>{gt text='Name'}</dt>
        <dd>{$route.name}</dd>
        <dt>{gt text='Bundle'}</dt>
        <dd>{$route.bundle}</dd>
        <dt>{gt text='Controller'}</dt>
        <dd>{$route.controller}</dd>
        <dt>{gt text='Action'}</dt>
        <dd>{$route.action}</dd>
        <dt>{gt text='Path'}</dt>
        <dd>{$route.path}</dd>
        <dt>{gt text='Host'}</dt>
        <dd>{$route.host}</dd>
        <dt>{gt text='Schemes'}</dt>
        <dd>{$route.schemes|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Methods'}</dt>
        <dd>{$route.methods|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Defaults'}</dt>
        <dd>{$route.defaults|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Requirements'}</dt>
        <dd>{$route.requirements|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Options'}</dt>
        <dd>{$route.options|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Condition'}</dt>
        <dd>{$route.condition}</dd>
        <dt>{gt text='Description'}</dt>
        <dd>{$route.description}</dd>
        <dt>{gt text='User route'}</dt>
        <dd>{$route.userRoute|yesno:true}</dd>
        <dt>{gt text='Sort'}</dt>
        <dd>{$route.sort}</dd>
        <dt>{gt text='Group'}</dt>
        <dd>{$route.group}</dd>

    </dl>
    {include file='Helper/include_standardfields_display.tpl' obj=$route}

    {if !isset($smarty.get.theme) || $smarty.get.theme ne 'Printer'}
        {* include display hooks *}
        {notifydisplayhooks eventname='zikularoutesmodule.ui_hooks.routes.display_view' id=$route.id urlobject=$currentUrlObject assign='hooks'}
        {foreach key='providerArea' item='hook' from=$hooks}
            {$hook}
        {/foreach}
        {if count($route._actions) gt 0}
            <p id="itemActions">
            {foreach item='option' from=$route._actions}
                <a href="{$option.url.type|zikularoutesmoduleActionUrl:$option.url.func:$option.url.arguments}" title="{$option.linkTitle|safetext}" class="fa fa-{$option.icon}">{$option.linkText|safetext}</a>
            {/foreach}
            </p>
            <script type="text/javascript">
            /* <![CDATA[ */
                document.observe('dom:loaded', function() {
                    routesInitItemActions('route', 'display', 'itemActions');
                });
            /* ]]> */
            </script>
        {/if}
    {/if}
</div>
{include file="`$lctUc`/footer.tpl"}
