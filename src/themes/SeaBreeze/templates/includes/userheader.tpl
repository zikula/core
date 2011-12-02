<div id="topline" class="z-clearfix">
    <div class="z-floatleft">{userwelcome|ucwords}</div>
    <div class="z-floatright">{blockposition name=search}</div>
</div>
<div id="header" class="z-clearfix">
    <h1><a href="{homepage}" title="{$modvars.ZConfig.slogan}">{$modvars.ZConfig.sitename}</a></h1>
    {blockposition name='topnav' assign='topnavblock'}
    {if empty($topnavblock)}
    <div id="navi" class="z-clearer">
        <ul id="nav">
            <li class="page_item"><a href="{homepage}" title="{gt text="Go to the site's home page"}">{gt text='Home'}</a></li>
            <li class="page_item"><a href="{modurl modname='Users' type='user' func='main'}" title="{gt text='Go to your account panel'}">{gt text="My Account"}</a></li>
            <li class="page_item"><a href="{modurl modname='Search' type='user' func='main'}" title="{gt text='Search this site'}">{gt text="Site search"}</a></li>
        </ul>
    </div>
    {else}
    {$topnavblock}
    {/if}
</div>
