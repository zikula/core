<td><strong>{$name}</strong></td>

<td>{$description|safehtml}</td>

<td>{gt text=$typelbl}</td>

<td>{gt text=$statelbl}</td>

<td>
    {$nbuser}
    
    {if $canview eq true}
        - <a href="{modurl modname="Groups" type="user" func="memberslist" gid=$gid}" title="{gt text="Members list"}">{gt text="Members list"}</a>
    {/if}
</td>

{if $nbumax eq 0}
    {gt text="Unlimited" assign=nbumax}
{/if}
<td>{$nbumax}</td>

{if $coredata.logged_in eq true}
    <td>{gt text='Private'}</td>
{else}
    <td><a href="{modurl modname='UsersModule' type='user' func='view'}" title="{gt text="Sorry! You must register for a user account on this site before you can apply for membership of a group."}"> {gt text="Log in or register"}</a></td>
{/if}
