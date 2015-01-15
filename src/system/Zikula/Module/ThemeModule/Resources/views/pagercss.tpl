<div class="text-center">
    <ul class="pagination">
        {if $pagerPluginArray.currentPage gt 1}
        <li><a href="{$pagerPluginArray.firstUrl}" title="{gt text='First page'}">&laquo;</a></li>
        <li><a href="{$pagerPluginArray.prevUrl}" title="{gt text='Previous page'}">&lsaquo;</a></li>
        {else}
        <li class="disabled"><span title="{gt text='First page'}">&laquo;</span></li>
        <li class="disabled"><span title="{gt text='Previous page'}">&lsaquo;</span></li>
        {/if}

        {foreach name='pages' item='currentPage' key='currentItem' from=$pagerPluginArray.pages}
        {if $currentPage.isCurrentPage}
        <li class="active"><span>{$currentItem}</span></li>
        {else}
        <li><a href="{$currentPage.url}">{$currentItem}</a></li>
        {/if}
        {/foreach}

        {if $pagerPluginArray.currentPage lt $pagerPluginArray.countPages}
        <li><a href="{$pagerPluginArray.nextUrl}" title="{gt text='Next page'}">&rsaquo;</a></li>
        <li><a href="{$pagerPluginArray.lastUrl}" title="{gt text='Last page'}">&raquo;</a></li>
        {else}
        <li class="disabled"><span  title="{gt text='Next page'}">&rsaquo;</span></li>
        <li class="disabled"><span title="{gt text='Last page'}">&raquo;</span></li>
        {/if}
    </ul>
 </div>