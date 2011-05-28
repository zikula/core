<div class="z-fullerror">
    <h1>{gt text="Error on %s" tag1=$modvars.ZConfig.sitename}</h1>
    <ul>
        {foreach from=$messages item=message}
        <li>{$message|safehtml}</li>
        {/foreach}
    </ul>
    {if $trace}
    <ul>
        <h2>{gt text="Exception Trace"}</h2>
        {foreach from=$trace item=t}
            <li>{$t|safehtml}</li>
        {/foreach}
    </ul>
    {/if}
    <p><a href="javascript:history.back(-1)">{gt text="Go back to previous page"}</a></p>
</div>
