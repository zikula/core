<p>
    {if $guestcount eq 0}
    {gt text='%s registered user' plural='%s registered users' count=$userscount tag1=$userscount domain='zikula' assign='blockstring'}
    {gt text='%s on-line.' tag1=$blockstring domain='zikula'}
    {elseif $userscount eq 0}
    {gt text='%s anonymous guest' plural='%s anonymous guests' count=$guestcount tag1=$guestcount domain='zikula' assign='blockstring'}
    {gt text='%s on-line.' tag1=$blockstring domain='zikula'}
    {else}
    {gt text='%s registered user' plural='%s registered users' count=$userscount tag1=$userscount domain='zikula' assign='nummeb'}
    {gt text='%s anonymous guest' plural='%s anonymous guests' count=$guestcount tag1=$guestcount domain='zikula' assign='numanon'}
    {gt text='%1$s and %2$s online.' tag1=$nummeb tag2=$numanon domain='zikula'}
    {/if}
</p>
{if $coredata.logged_in eq 1}
    {if $msgmodule}
    {modurl modname=$msgmodule type='user' func="inbox" assign="messageslink"}
    <p>{if $messages.unread eq 0}{gt text="You have no new messages." domain='zikula'}{else}{gt text='You have <a href="%1$s">%2$s</a> new message.' plural='You have <a href="%1$s">%2$s</a> new messages.' count=$messages.unread tag1=$messageslink tag2=$messages.unread domain='zikula'}{/if}</p>
    {/if}
    <p>{gt text="You are logged-in as <strong>%s</strong>." tag1=$coredata.user.uname|profilelinkbyuname domain='zikula'}</p>

{else}
    {route name='zikulausersmodule_registration_register' assign='url'}
    <p>{gt text="You are an anonymous guest." domain='zikula'}
    {if $registerallowed eq 1}
    {gt text='You can <a href="%s">register here</a>.' tag1=$url|safetext domain='zikula'}
    {/if}
    </p>
{/if}
