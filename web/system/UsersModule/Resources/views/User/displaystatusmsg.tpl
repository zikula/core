{insert name='getstatusmsg'}

{if isset($regErrors) && count($regErrors) > 0}
<div class="z-errormsg">
{foreach from=$regErrors item="regError"}
    <p>{$regError}</p>
{/foreach}
</div>
{/if}

<p style="text-align: center;">
    <a href="{$baseurl}">{gt text="Go back to the homepage"}</a>
</p>
