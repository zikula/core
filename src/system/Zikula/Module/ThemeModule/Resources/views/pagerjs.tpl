{pageaddvarblock}
    <script type="text/javascript">
        ( function($) {
            PagerChangeClass = function(id, class1, class2)
            {
                var myobj = $('#' + id);
                if (myobj.hasClass(class1)) {
                    myobj.removeClass(class1);
                    myobj.addClass(class2);
                } else {
                    myobj.removeClass(class2);
                    myobj.addClass(class1);
                }
            }

            CheckPageLink = function(linkid)
            {
                PagerChangeClass('showlink' + linkid + 'one', 'hide', 'show');
                PagerChangeClass('showlink' + linkid + 'two', 'hide', 'show');
                PagerChangeClass('showpages' + linkid, 'hide', 'show');
            }
        })(jQuery);
    </script>
{/pageaddvarblock}

{assign var='separator' value=' | '}

<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage gt 1}
        <a href="{$pagerPluginArray.firstUrl}" title="{gt text='First page'}">&lt;&lt;</a>
        <span>{$separator}</span>

        <a href="{$pagerPluginArray.prevUrl}" title="{gt text='Previous page'}">&lt;</a>
        <span>{$separator}</span>
    {/if}

        {if $pagerPluginArray.maxPages gt 0}
            {assign var='hiddenPageBoxOpened' value='0'}
            {assign var='hiddenPageBoxClosed' value='0'}
        {/if}

        {foreach name='pages' item='currentPage' key='currentItem' from=$pagerPluginArray.pages}
            {if $currentPage.isVisible eq 1 && $hiddenPageBoxOpened eq 1 && $hiddenPageBoxClosed eq 0}
                </span>
                {assign var='hiddenPageBoxClosed' value='1'}
                {assign var='hiddenPageBoxOpened' value='0'}
            {/if}

            {if $currentPage.isVisible eq 0 && $hiddenPageBoxOpened eq 0}
                <a href="javascript:void(0);" onclick="javascript:CheckPageLink('{$currentPage.pagenr}')" onkeypress="javascript:CheckPageLink('{$currentPage.pagenr}')">
                    <span id="showlink{$currentPage.pagenr}one">... +</span>
                    <span id="showlink{$currentPage.pagenr}two" class="hide">... -</span>
                </a>
                <span id="showpages{$currentPage.pagenr}" class="hide">
                {assign var='hiddenPageBoxOpened' value='1'}
                {assign var='hiddenPageBoxClosed' value='0'}
            {/if}

            {if $currentPage.isCurrentPage}
                <strong>{$currentPage.pagenr}</strong>
            {else}
                <a href="{$currentPage.url}">{$currentPage.pagenr}</a>
            {/if}

            <span>{$separator}</span>
        {/foreach}

    {if $pagerPluginArray.currentPage lt $pagerPluginArray.countPages}
        <a href="{$pagerPluginArray.nextUrl}" title="{gt text='Next page'}">&gt;</a>
        <span>{$separator}</span>

        <a href="{$pagerPluginArray.lastUrl}" title="{gt text='Last page'}">&gt;&gt;</a>
    {/if}
</div>
