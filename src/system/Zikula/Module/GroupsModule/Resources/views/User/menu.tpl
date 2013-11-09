{pagesetvar name='title' value=$templatetitle}
<h2 class="userheader">{gt text="Groups"}</h2>
{insert name="getstatusmsg"}
{if $mainpage eq false}
<ul class="navbar navbar-default navbar-modulelinks">
    <li>
        <a href="{modurl modname="Groups" type="user" func="main"}" title="{gt text="Go to groups index page"}"><span class="fa fa-list"></span> {gt text="Go to groups index page"}</a>
    </li>
</ul>
{/if}
