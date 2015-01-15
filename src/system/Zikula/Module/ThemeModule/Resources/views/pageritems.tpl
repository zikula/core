{assign var='separator' value='&ndash;'}
<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage gt 1}
        <a href="{$pagerPluginArray.firstUrl}" title="{gt text='First page'}">{gt text='First page'}</a> {$separator} 
        <a href="{$pagerPluginArray.prevUrl}" title="{gt text='Previous page'}">{gt text='Previous page'}</a> {$separator} 
    {/if}

        {foreach name='pages' item='currentPage' key='currentItem' from=$pagerPluginArray.pages}
            {if $currentPage.isCurrentPage}
                <span>{$currentItem}</span>
            {else}
                <a href="{$currentPage.url}">{$currentItem}</a>
            {/if}
            
            {if $pagerPluginArray.currentPage lt $pagerPluginArray.countPages}
                {$separator}
            {/if}
        {/foreach}

    {if $pagerPluginArray.currentPage lt $pagerPluginArray.countPages}
        <a href="{$pagerPluginArray.nextUrl}" title="{gt text='Next page'}">{gt text='Next page'}</a>

         {$separator} 
        <a href="{$pagerPluginArray.lastUrl}" title="{gt text='Last page'}">{gt text='Last page'}</a>
    {/if}
</div>
