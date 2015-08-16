{if $setpagetitle && $title}
    {pagesetvar name='title' value=$title}
{/if}
{if $insertstatusmsg}
    {insert name='getstatusmsg'}
{/if}

{if $userthemename|strtolower|strpos:"printer" !== false}
    {if isset($image) && $image}<img src="{$image|safetext}" alt="{$title|safetext}" />{/if}
    {if $title}<h2>{$title|safetext}</h2>{/if}

{else}
    {if $menufirst}{modulelinks modname=$modname type=$type}{/if}
    <div class="{if $type == 'user'}z-modtitle{else}z-admin-content-modtitle{/if}">
        {if $image}<img src="{$image|safetext}" alt="{$title|safetext}" class="z-floatleft" />{/if}
        {if $title}<h2>{$title|safetext}</h2>{/if}
    </div>
    {if !$menufirst}{modulelinks modname=$modname type=$type}{/if}
{/if}
