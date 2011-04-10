<div class="z-fullerror">
    <h2>{gt text="Access denied (error 403)"}</h2>
    <hr />
    <p>
        {gt text="Sorry! You don't have authorisation for the page you wanted."}
        {if $reportlevel neq 0}
        {if $reportlevel eq 2 or $localreferer}
        {gt text="Details have been automatically e-mailed to the site administrator."}
        {/if}
        {/if}
    </p>

    {userloggedin assign=loggedin}
    {if $loggedin eq false}
    <h2>{gt text="Log-in"}</h2>
    {modurl modname='Users' type='user' func='login' assign='url'}
    <p>{gt text='You are not logged-in. You might have access if you <a href="%s">log in</a>.' tag1=$url|safetext}</p>
    {/if}

    <h2>{gt text="Additional information"}</h2>
    <ul>
        {foreach from=$messages item=message}
        <li>{$message|safehtml}</li>
        {/foreach}
    </ul>

    <p><a href="javascript:history.back(-1)">{gt text="Go back to previous page"}</a></p>

</div>
