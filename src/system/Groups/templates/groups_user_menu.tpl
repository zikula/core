{pagesetvar name=title value=$templatetitle}
<h2 class="userheader">{gt text="Groups manager"}</h2>
{insert name="getstatusmsg"}
{if $mainpage eq false}
<ul class="z-menulinks">
    <li><a class="z-icon-es-view" href="{modurl modname="Groups" type="user" func="main"}" title="{gt text="Go to groups index page"}">{gt text="Go to groups index page"}</a></li>
</ul>
{/if}
