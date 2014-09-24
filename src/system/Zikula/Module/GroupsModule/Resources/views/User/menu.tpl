{pagesetvar name='title' value=$templatetitle}
<h2 class="userheader">{gt text="Groups"}</h2>
{insert name="getstatusmsg"}
{if $mainpage eq false}
<ul class="navbar navbar-default navbar-modulelinks">
    <li>
        <a href="{route name='zikulagroupsmodule_user_index'}" title="{gt text="Go to groups index page"}"><span class="fa fa-list"></span> {gt text="Go to groups index page"}</a>
    </li>
</ul>
{/if}
