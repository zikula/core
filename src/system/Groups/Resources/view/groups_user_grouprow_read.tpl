<td><strong>{$name}</strong></td>
<td>{$description|safehtml}</td>
<td>{$typelbl}</td>
<td>{$statelbl}</td>
<td>{$nbuser}</td>
{if $nbumax eq false or $nbumax eq 0}
{gt text="Unlimited" assign=nbumax}
{/if}
<td>{$nbumax}</td>
{if $state eq 0}
<td>[ {gt text="Unavailable"} ]</td>
{else}
{if $status eq true}
<td><strong>{gt text="Pending"}</strong> | <a href="{modurl modname="Groups" type="user" func="membership" action="cancel" gid=$gid}" title="{gt text="Cancel"}">{gt text="Cancel"}</a></td>
{else}
{if $ismember eq true}
<td><a href="{modurl modname='Groups' type='user' func='membership' action='unsubscribe' gid=$gid}" title="{gt text="Resign"}">{gt text="Resign"}</a></td>
{else}
{if $nbumax == 0 OR $nbumax gt $nbuser}
<td><a href="{modurl modname='Groups' type='user' func='membership' action='subscribe' gid=$gid}" title="{gt text="Apply"}">{gt text="Apply"}</a></td>
{else}
<td>{gt text='Group has reached its maximum capacity'}</td>
{/if}
{/if}
{/if}
{if $canview eq true}
<td><a href="{modurl modname="Groups" type="user" func="memberslist" gid=$gid}" title="{gt text="Members list"}">{gt text="Members list"}</a></td>
{else}
<td>&nbsp;</td>
{/if}
{/if}
