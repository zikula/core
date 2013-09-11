{include file='includes/header.tpl'}
<div id="theme_navigation_bar" class="clearfix">
    {blockposition name=topnav assign=topnavblock}
    {if empty($topnavblock)}
    <ul class="pull-left">
        <li><a href="{homepage}" title="{gt text="Go to the site's home page"}">{gt text='Home'}</a></li>
        <li><a href="{modurl modname='ZikulaUsersModule' type='user' func='main'}" title="{gt text='Go to your account panel'}">{gt text="My Account"}</a></li>
        <li><a href="{modurl modname='ZikulaSearchModule' type='user' func='main'}" title="{gt text='Search this site'}">{gt text="Site search"}</a></li>
    </ul>
    {else}
    {$topnavblock}
    {/if}
    {blockposition name=search}
</div>
{include file="body/$master.tpl"}
{include file='includes/footer.tpl'}