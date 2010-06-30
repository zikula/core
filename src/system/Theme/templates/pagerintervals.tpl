<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage > 1}
        {strip}[
        <a href="{$pagerPluginArray.firstUrl}" title="{gt text="First page"}">
            {gt text="First page"}
        </a>
        ]{/strip}

        {strip}[
        <a href="{$pagerPluginArray.prevUrl}" title="{gt text="Previous page"}">
            {gt text="Previous page"}
        </a>
        ]{/strip}
    {/if}

        {foreach name="pages" item="currentPage" key="currentItem" from=$pagerPluginArray.pages}
            {assign var="interval" value="`$currentItem` - `$currentItem+$pagerPluginArray.perpage-1`"}
            {strip}[
            {if $currentPage.isCurrentPage}
                <span>{$interval}</span>
            {else}
                <a href="{$currentPage.url}">{$interval}</a>
            {/if}
            ]{/strip}
        {/foreach}

    {if $pagerPluginArray.currentPage < $pagerPluginArray.countPages}
        {strip}[
        <a href="{$pagerPluginArray.nextUrl}" title="{gt text="Next page"}">
            {gt text="Next page"}
        </a>
        ]{/strip}

        {strip}[
        <a href="{$pagerPluginArray.lastUrl}" title="{gt text="Last page"}">
            {gt text="Last page"}
        </a>
        ]{/strip}
    {/if}
</div>
