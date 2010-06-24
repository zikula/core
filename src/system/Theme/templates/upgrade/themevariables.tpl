[variables]
{if isset($variables)}
{foreach from=$variables key=name item=value}
{$name} = {if isset($value)}{$value}{/if} 
{/foreach}
{/if}