{assign var='separator' value='&ndash;'}

<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage gt 1}
        <a href="{$pagerPluginArray.firstUrl}" title="{gt text='First page'}">{gt text='First page'}</a> {$separator}
        <a href="{$pagerPluginArray.prevUrl}" title="{gt text='Previous page'}">{gt text='Previous page'}</a> {$separator}
    {/if}

        {if $pagerPluginArray.maxPages > 0}
            {assign var='hiddenPageBoxOpened' value='0'}
            {assign var='hiddenPageBoxClosed' value='0'}
        {/if}

        {foreach name='pages' item='currentPage' key='currentItem' from=$pagerPluginArray.pages}
            {if $currentItem gt $pagerPluginArray.perpage} {$separator} {/if}

            {if $currentPage.isVisible eq 1 && $hiddenPageBoxOpened eq 1 && $hiddenPageBoxClosed eq 0}
                </span>
                {assign var='hiddenPageBoxClosed' value='1'}
                {assign var='hiddenPageBoxOpened' value='0'}
            {/if}

            {if $currentPage.isVisible eq 0 && $hiddenPageBoxOpened eq 0}
                 ... <span class="hide">
                {assign var='hiddenPageBoxOpened' value='1'}
                {assign var='hiddenPageBoxClosed' value='0'}
            {/if}

            {if $currentPage.isCurrentPage}
                <span>{$currentPage.pagenr}</span>
            {else}
                <a href="{$currentPage.url}">{$currentPage.pagenr}</a>
            {/if}
        {/foreach}

    {if $pagerPluginArray.currentPage lt $pagerPluginArray.countPages}
         {$separator}
        <a href="{$pagerPluginArray.nextUrl}" title="{gt text='Next page'}">{gt text='Next page'}</a>
         {$separator}
        <a href="{$pagerPluginArray.lastUrl}" title="{gt text='Last page'}">{gt text='Last page'}</a>
    {/if}
</div>
