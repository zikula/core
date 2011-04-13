{pageaddvarblock}
    <script type="text/javascript">
        function PagerChangeClass(id, class1, class2) {
            var myobj = document.getElementById(id);
            if (myobj.className == class1) {
                myobj.className = class2;
            }
            else {
                myobj.className = class1;
            }
        }

        function CheckPageLink(linkid) {
            PagerChangeClass('showlink' + linkid + 'one', 'z-hide', 'z-show');
            PagerChangeClass('showlink' + linkid + 'two', 'z-hide', 'z-show');
            PagerChangeClass('showpages' + linkid, 'z-hide', 'z-show');
        }
    </script>
{/pageaddvarblock}

{assign var="separator" value=" | "}

<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage > 1}
        <a href="{$pagerPluginArray.firstUrl}" title="{gt text="First page"}">&lt;&lt;</a>
        <span>{$separator}</span>

        <a href="{$pagerPluginArray.prevUrl}" title="{gt text="Previous page"}">&lt;</a>
        <span>{$separator}</span>
    {/if}

        {if $pagerPluginArray.maxPages > 0}
            {assign var="hiddenPageBoxOpened" value="0"}
            {assign var="hiddenPageBoxClosed" value="0"}
        {/if}

        {foreach name="pages" item="currentPage" key="currentItem" from=$pagerPluginArray.pages}
            {if $currentPage.isVisible eq 1 && $hiddenPageBoxOpened eq 1 && $hiddenPageBoxClosed eq 0}
                </span>
                {assign var="hiddenPageBoxClosed" value="1"}
                {assign var="hiddenPageBoxOpened" value="0"}
            {/if}

            {if $currentPage.isVisible eq 0 && $hiddenPageBoxOpened eq 0}
                <a href="javascript:void(0);" onclick="javascript:CheckPageLink('{$currentPage.pagenr}')" onkeypress="javascript:CheckPageLink('{$currentPage.pagenr}')">
                    <span id="showlink{$currentPage.pagenr}one">... +</span>
                    <span id="showlink{$currentPage.pagenr}two" class="z-hide">... -</span>
                </a>
                <span id="showpages{$currentPage.pagenr}" class="z-hide">
                {assign var="hiddenPageBoxOpened" value="1"}
                {assign var="hiddenPageBoxClosed" value="0"}
            {/if}

            {if $currentPage.isCurrentPage}
                <strong>{$currentPage.pagenr}</strong>
            {else}
                <a href="{$currentPage.url}">{$currentPage.pagenr}</a>
            {/if}

            <span>{$separator}</span>
        {/foreach}

    {if $pagerPluginArray.currentPage < $pagerPluginArray.countPages}
        <a href="{$pagerPluginArray.nextUrl}" title="{gt text="Next page"}">&gt;</a>
        <span>{$separator}</span>

        <a href="{$pagerPluginArray.lastUrl}" title="{gt text="Last page"}">&gt;&gt;</a>
    {/if}
</div>
