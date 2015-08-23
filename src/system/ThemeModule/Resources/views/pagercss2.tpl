{if $pagerPluginArray.includeStylesheet}
{pageaddvar name='stylesheet' value='system/ThemeModule/Resources/public/css/pagercssadvanced.css'}
{/if}
{assign var='separator' value=' | '}

<div class="{$pagerPluginArray.class}">
    {if $pagerPluginArray.currentPage gt 1}
        <a href="{$pagerPluginArray.prevUrl}" class="skip">
            {img modname='core' set='icons/extrasmall' src='previous.png' __alt='Previous page' __title='Previous page'}
        </a>
    {else}
        <span class="skip">
            {img modname='core' set='icons/extrasmall' src='1leftarrow_inactive.png' __alt='No previous pages' __title='No previous pages'}
        </span>
    {/if}
    <span>{$separator}</span>

    {foreach name='pages' item='currentPage' key='currentItem' from=$pagerPluginArray.pages}
        {if $currentPage.isVisible eq 1 && $hiddenPageBoxOpened eq 1 && $hiddenPageBoxClosed eq 0}
                    </span>
                </span>
            </span>
            {assign var='hiddenPageBoxClosed' value='1'}
            {assign var='hiddenPageBoxOpened' value='0'}
        {/if}

        {if $currentPage.isVisible eq 0 && $hiddenPageBoxOpened eq 0}
            <span class="select">
                <span class="option">
                    <span class="items">
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
        <a href="{$pagerPluginArray.nextUrl}" class="skip">
            {img modname='core' set='icons/extrasmall' src='forward.png' __alt='Next page' __title='Next page'}
        </a>
    {else}
        <span class="skip">
            {img modname='core' set='icons/extrasmall' src='1rightarrow_inactive.png' __alt='No further pages' __title='No further pages'}
        </span>
    {/if}
</div>
