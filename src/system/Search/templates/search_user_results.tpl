{gt text="Search results" assign=templatetitle domain='zikula'}
{include file='search_user_menu.tpl'}

<h3>{$templatetitle}</h3>

<p>{gt text="Number of hits: %s" tag1=$resultcount domain='zikula'}</p>

{if $resultcount gt 0}
<dl class="search_results">
    {foreach from=$results item=result}
    <dt class="search_hit">
        {if isset($result.url) && $result.url neq ''}
        <a href="{$result.url|safetext}">{$result.title|google_highlight:$q:$limitsummary}</a>
        &nbsp;&nbsp;<span class="z-sub">(<a href="{modurl modname=$result.module type='user' func='main'}">{$result.displayname}</a>)</span>
        {else}
        {$result.title|google_highlight:$q:$limitsummary}
        &nbsp;&nbsp;<span class="z-sub">(<a href="{modurl modname=$result.module type='user' func='main'}">{$result.displayname}</a>)</span>
        {/if}

    </dt>
    <dd>
        {$result.text|google_highlight:$q:$limitsummary|truncate:$limitsummary:'&hellip;'}
        {if !empty($result.created)}
        <div class="search_created">{gt text="Created on %s." tag1=$result.created|dateformat:'datelong' domain='zikula'}</div>
        {/if}
    </dd>
    {/foreach}
</dl>
{pager rowcount=$resultcount limit=$numlimit posvar='page' display='page'}<br/>

{else}

<p>{gt text="No search results found. You can try the following:" domain='zikula'}</p>
<ul>
    <li>{gt text="Check that you spelled all words correctly." domain='zikula'}</li>
    <li>{gt text="Use different keywords." domain='zikula'}</li>
    <li>{gt text="Use keywords that are more general." domain='zikula'}</li>
    <li>{gt text="Use fewer words." domain='zikula'}</li>
</ul>
{modfunc modname='Search' func='form' titles=false}
{/if}
