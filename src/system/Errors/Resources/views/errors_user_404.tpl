<div class="z-fullerror">
    <h2>{gt text="Page not found (error 404)"}</h2>
    <hr />
    <p>
        {gt text='Sorry! Could not find the page you wanted on %2$s: \'%1$s\'.' tag1=$currenturi|safetext tag2=$sitename}
        {if $reportlevel neq 0}
        {if $reportlevel eq 2 or $localreferer}
        {gt text="Details have been automatically e-mailed to the site administrator."}
        {/if}
        {/if}
    </p>

    {modavailable modname=Search assign=search}
    {if $search}
    <h2>{gt text="Search"}</h2>
    {modurl modname='Search' type='user' func='main' assign='url'}
    <p>{gt text='You could try a search from the <a href="%s">site search page</a>.' tag1=$url|safetext}</p>
    {/if}

    <h2>{gt text="Additional information"}</h2>
    <ul>
        {foreach from=$messages item=message}
        <li>{$message|safehtml}</li>
        {/foreach}
    </ul>

    <p><a href="javascript:history.back(-1)">{gt text="Go back to previous page"}</a></p>

</div>
