{if $pagerPluginArray.includeStylesheet}
{pageaddvar name="stylesheet" value="system/Theme/style/pagercss.css"}
{/if}
{assign var="separator" value="&ndash;"}

<div class="{$pagerPluginArray.class} z-pagercss">
    {if $pagerPluginArray.currentPage > 1}
    <a href="{$pagerPluginArray.firstUrl}" title="{gt text="First page"}" class="z-pagercss-first">&laquo;</a>
    <a href="{$pagerPluginArray.prevUrl}" title="{gt text="Previous page"}" class="z-pagercss-prev">&lsaquo;</a>
    {else}
    <span class="z-pagercss-first" title="{gt text="First page"}">&laquo;</span>
    <span class="z-pagercss-prev" title="{gt text="Previous page"}">&lsaquo;</span>
    {/if}

    {foreach name="pages" item="currentPage" key="currentItem" from=$pagerPluginArray.pages}
    {if $currentPage.isCurrentPage}
    <span class="z-pagercss-current">{$currentItem}</span>
    {else}
    <a href="{$currentPage.url}" class="z-pagercss-item">{$currentItem}</a>
    {/if}
    {/foreach}

    {if $pagerPluginArray.currentPage < $pagerPluginArray.countPages}
    <a href="{$pagerPluginArray.nextUrl}" title="{gt text="Next page"}" class="z-pagercss-next">&rsaquo;</a>
    <a href="{$pagerPluginArray.lastUrl}" title="{gt text="Last page"}" class="z-pagercss-last">&raquo;</a>
    {else}
    <span class="z-pagercss-next" title="{gt text="Next page"}">&rsaquo;</span>
    <span class="z-pagercss-last" title="{gt text="Last page"}">&raquo;</span>
    {/if}
</div>
