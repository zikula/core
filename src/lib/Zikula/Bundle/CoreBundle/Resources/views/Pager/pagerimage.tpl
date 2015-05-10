{assign var='separator' value=' '}
<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage gt 1}
        {gt text='First page' assign='firstpage'}
        <a href="{$pagerPluginArray.firstUrl}" title="{$firstpage}">
            {img modname='core' set='icons/extrasmall' src='2leftarrow.png' title=$firstpage alt=$firstpage}
        </a>
        {$separator}
        {gt text='Previous page' assign='previouspage'}
        <a href="{$pagerPluginArray.prevUrl}" title="{$previouspage}">
            {img modname='core' set='icons/extrasmall' src='1leftarrow.png' title=$previouspage alt=$previouspage}
        </a> {$separator}
    {/if}

    <span>
    {gt text='Page %1$s / %2$s (%3$s - %4$s of %5$s total)' tag1=$pagerPluginArray.currentPage tag2=$pagerPluginArray.countPages tag3=$pagerPluginArray.itemStart tag4=$pagerPluginArray.itemEnd tag5=pagerPluginArray.total}
    </span>

    {if $pagerPluginArray.currentPage lt $pagerPluginArray.countPages}
        {$separator}
        {gt text='Next page' assign='nextpage'}
        <a href="{$pagerPluginArray.nextUrl}" title="{$nextpage}">
            {img modname='core' set='icons/extrasmall' src='1rightarrow.png' title=$nextpage alt=$nextpage}
        </a>
        {$separator}
        {gt text='Last page' assign='lastpage'}
        <a href="{$pagerPluginArray.lastUrl}" title="{$lastpage}">
            {img modname='core' set='icons/extrasmall' src='2rightarrow.png' title=$lastpage alt=$lastpage}
        </a>
    {/if}
</div>
