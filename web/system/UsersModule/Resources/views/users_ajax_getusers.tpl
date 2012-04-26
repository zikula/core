<ul>
{if isset($results) and is_array($results) and count($results) gt 0}
{foreach from=$results item='result'}
<li>{$result.uname|safetext}<input type="hidden" id="{$result.uname|safetext}" value="{$result.uid}" /></li>
{/foreach}
{/if}
</ul>