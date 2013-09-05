{pagesetvar name='title' value=$templatetitle}
<h2 class="userheader">{gt text="Groups"}</h2>
{insert name="getstatusmsg"}
{if $mainpage eq false}
<ul class="navbar navbar-default">
    <li><a class="smallicon smallicon-view" href="{modurl modname="Groups" type="user" func="main"}" title="{gt text="Go to groups index page"}">{gt text="Go to groups index page"}</a></li>
</ul>
{/if}
