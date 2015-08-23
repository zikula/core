{* purpose of this template: routes index view *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{assign var='lctUc' value=$lct|ucfirst}
{include file="`$lctUc`/header.tpl"}
<p>{gt text='Welcome to the route section of the Routes application.'}</p>
{include file="`$lctUc`/footer.tpl"}
