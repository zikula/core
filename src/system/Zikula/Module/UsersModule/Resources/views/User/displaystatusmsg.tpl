{insert name='getstatusmsg'}

{if isset($regErrors) && count($regErrors) > 0}
<div class="alert alert-danger">
{foreach from=$regErrors item="regError"}
    <p>{$regError}</p>
{/foreach}
</div>
{/if}

<p class="text-center">
    <a href="{$baseurl}">{gt text="Go back to the homepage"}</a>
</p>