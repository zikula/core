{nocache}{include file='user/menu.tpl'}{/nocache}
{insert name='getstatusmsg'}

{section name='newsview' loop=$newsitems}
    {$newsitems[newsview]}
    {if $smarty.section.newsview.last neq true}
    {/if}
{/section}

{if $newsitems}
{pager display='page' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='page' maxpages='10'}
{/if}