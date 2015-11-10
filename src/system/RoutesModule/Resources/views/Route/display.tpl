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
            {$templateTitle|notifyfilters:'zikularoutesmodule.filter_hooks.routes.filter'}
            {if count($route._actions) gt 0}
                <div class="dropdown">
                    <a id="itemActions{$route.id}DropDownToggle" role="button" data-toggle="dropdown" data-target="#" href="javascript:void(0);" class="dropdown-toggle"><i class="fa fa-tasks"></i> {gt text='Actions'} <span class="caret"></span></a>

                    <ul class="dropdown-menu" role="menu" aria-labelledby="itemActions{$route.id}DropDownToggle">
                        {foreach item='option' from=$route._actions}
                            <li role="presentation"><a href="{$option.url.type|zikularoutesmoduleActionUrl:$option.url.func:$option.url.arguments}" title="{$option.linkTitle|safetext}" role="menuitem" tabindex="-1" class="fa fa-{$option.icon}">{$option.linkText|safetext}</a></li>

                        {/foreach}
                    </ul>
                </div>
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
            {/if}
        </h3>
    {else}
        <h2>
            {$templateTitle|notifyfilters:'zikularoutesmodule.filter_hooks.routes.filter'}
            {if count($route._actions) gt 0}
                <div class="dropdown">
                    <a id="itemActions{$route.id}DropDownToggle" role="button" data-toggle="dropdown" data-target="#" href="javascript:void(0);" class="dropdown-toggle"><i class="fa fa-tasks"></i> {gt text='Actions'} <span class="caret"></span></a>

                    <ul class="dropdown-menu" role="menu" aria-labelledby="itemActions{$route.id}DropDownToggle">
                        {foreach item='option' from=$route._actions}
                            <li role="presentation"><a href="{$option.url.type|zikularoutesmoduleActionUrl:$option.url.func:$option.url.arguments}" title="{$option.linkTitle|safetext}" role="menuitem" tabindex="-1" class="fa fa-{$option.icon}">{$option.linkText|safetext}</a></li>

                        {/foreach}
                    </ul>
                </div>
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
            {/if}
        </h2>
    {/if}

    <dl>
        <dt>{gt text='Route type'}</dt>
        <dd>{$route.routeType|zikularoutesmoduleGetListEntry:'route':'routeType'|safetext}</dd>
        <dt>{gt text='Replaced route name'}</dt>
        <dd>{$route.replacedRouteName}</dd>
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
        <dd>{$route.schemes|zikularoutesmoduleGetListEntry:'route':'schemes'|safetext}</dd>
        <dt>{gt text='Methods'}</dt>
        <dd>{$route.methods|zikularoutesmoduleGetListEntry:'route':'methods'|safetext}</dd>
        <dt>{gt text='Prepend bundle prefix'}</dt>
        <dd>{$route.prependBundlePrefix|yesno:true}</dd>
        <dt>{gt text='Translatable'}</dt>
        <dd>{$route.translatable|yesno:true}</dd>
        <dt>{gt text='Translation prefix'}</dt>
        <dd>{$route.translationPrefix}</dd>
        <dt>{gt text='Defaults'}</dt>
        <dd>{$route.defaults|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Requirements'}</dt>
        <dd>{$route.requirements|@zikularoutesmoduleToString}</dd>
        <dt>{gt text='Condition'}</dt>
        <dd>{$route.condition}</dd>
        <dt>{gt text='Description'}</dt>
        <dd>{$route.description}</dd>
        <dt>{gt text='Sort'}</dt>
        <dd>{$route.sort}</dd>
        <dt>{gt text='Group'}</dt>
        <dd>{$route.group}</dd>

    </dl>
    {include file='Helper/include_standardfields_display.tpl' obj=$route}

    {if !isset($smarty.get.theme) || $smarty.get.theme ne 'Printer'}
        {* include display hooks *}
        {notifydisplayhooks eventname='zikularoutesmodule.ui_hooks.routes.display_view' id=$route.id urlobject=$currentUrlObject assign='hooks'}
        {foreach name='hookLoop' key='providerArea' item='hook' from=$hooks}
            {if $providerArea ne 'provider.scribite.ui_hooks.editor'}{* fix for #664 *}
                {$hook}
            {/if}
        {/foreach}
    {/if}
</div>
{include file="`$lctUc`/footer.tpl"}
