{gt text="Search results" assign=templatetitle domain='zikula'}
{include file='User/menu.tpl'}

<h3>{$templatetitle}</h3>

{if !empty($errors)}
    <div class="alert alert-danger">
    {foreach item='error' from=$errors}
        <p>{$error}</p>
    {/foreach}
    </div>
{/if}

<p>{gt text='Number of hits: %s' tag1=$resultcount domain='zikula'}</p>

<dl class="search_results">
    {foreach item='result' from=$results}
    <dt class="search_hit">
        {if isset($result.url) && $result.url ne ''}
        <a href="{$result.url|safetext}">{$result.title|google_highlight:$q:$limitsummary}</a>
        &nbsp;&nbsp;<span class="sub">(<a href="{modurl modname=$result.module type='user' func='index'}">{$result.displayname}</a>)</span>
        {else}
        {$result.title|google_highlight:$q:$limitsummary}
        &nbsp;&nbsp;<span class="sub">(<a href="{modurl modname=$result.module type='user' func='index'}">{$result.displayname}</a>)</span>
        {/if}
    </dt>
    <dd>
        {$result.text|google_highlight:$q:$limitsummary|truncate:$limitsummary:'&hellip;'}
        {if !empty($result.created)}
            <div class="search_created">{gt text='Created on %s.' tag1=$result.created|dateformat:'datelong' domain='zikula'}</div>
        {/if}
    </dd>
    {/foreach}
</dl>
{pager rowcount=$resultcount limit=$numlimit posvar='page' display='page' includePostVars=false route='zikulasearchmodule_user_search'}<br/>
