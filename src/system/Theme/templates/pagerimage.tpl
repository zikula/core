{assign var="separator" value=" "}
<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage > 1}
        {gt text="First Page" assign="firstpage"}
        <a href="{$pagerPluginArray.firstUrl}" title="{$firstpage}">
            {img modname=core set=icons/extrasmall src="2leftarrow.png" title=$firstpage alt=$firstpage}
        </a>
        {$separator}
        {gt text="Previous Page" assign="previouspage"}
        <a href="{$pagerPluginArray.prevUrl}" title="{$previouspage}">
            {img modname=core set=icons/extrasmall src="1leftarrow.png" title=$previouspage alt=$previouspage}
        </a> {$separator}
    {/if}

    <span>
    {gt text="Page"} {$pagerPluginArray.currentPage} / {$pagerPluginArray.countPages} ({$pagerPluginArray.itemStart} - {$pagerPluginArray.itemEnd} {gt text="of"} {$pagerPluginArray.total} {gt text="Total"})
    </span>

    {if $pagerPluginArray.currentPage < $pagerPluginArray.countPages}
        {$separator}
        {gt text="Next Page" assign="nextpage"}
        <a href="{$pagerPluginArray.nextUrl}" title="{$nextpage}">
            {img modname=core set=icons/extrasmall src="1rightarrow.png" title=$nextpage alt=$nextpage}
        </a>
        {$separator}
        {gt text="Last Page" assign="lastpage"}
        <a href="{$pagerPluginArray.lastUrl}" title="{$lastpage}">
            {img modname=core set=icons/extrasmall src="2rightarrow.png" title=$lastpage alt=$lastpage}
        </a>
    {/if}
</div>
