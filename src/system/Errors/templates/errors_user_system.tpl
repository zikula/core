<div style="float:right">{img modname=core set=icons/large src=error.png}</div>
<h1>{gt text="System error"}</h1>
<h2>{$type|errortype}</h2>
<p>{$type|errortext}</p>
<p>
    {$message}
    {checkpermissionblock component='::' instance='::' level=ACCESS_ADMIN}
    {gt text='(Check file \'%1$s\' at line %2$s.)' tag1=$file tag2=$line}
    {/checkpermissionblock}
</p>
<p><a href="javascript:history.back(-1)">{gt text="Go back to previous page"}</a></p>
{checkpermissionblock component='::' instance='::' level=ACCESS_ADMIN}
<h2>{gt text="Additional information"}</h2>
{debug_backtrace}
{/checkpermissionblock}
