{if (!$allowusercatedit)}
<p class="alert alert-warning">{gt text="Sorry! User-owned category editing has not been enabled. This feature can be enabled by the site administrator."}</p>
{else}
{modfunc modname="ZikulaCategoriesModule" type="user" func="edituser"}
{/if}