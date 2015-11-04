{* purpose of this template: show output of reload action in route area *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{assign var='lctUc' value=$lct|ucfirst}
{include file="`$lctUc`/header.tpl"}
<div class="zikularoutesmodule-reload zikularoutesmodule-reload">
    {gt text='Reload' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    {if $lct eq 'admin'}
        <h3>
            <span class="fa fa-square"></span>
            {$templateTitle}
        </h3>
    {else}
        <h2>{$templateTitle}</h2>
    {/if}

    <p>Please override this template by moving it from <em>/system/RoutesModule/Resources/views/Route/reload.tpl</em> to either your <em>/themes/YourTheme/templates/modules/ZikulaRoutesModule/Route/reload.tpl</em> or <em>/config/templates/ZikulaRoutesModule/Route/reload.tpl</em>.</p>
</div>
{include file="`$lctUc`/footer.tpl"}
