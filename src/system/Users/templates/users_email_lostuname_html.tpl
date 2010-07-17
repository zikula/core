{gt text='\'%1$s\' account information' tag1=$sitename assign='subject'}

<p>{gt text='Hello!'}</p>

{if $adminRequested}
<p>{gt text='The administrator at %s requested that you receive your user name via e-mail.' tag1=$sitename}</p>
{else}
<p>{gt text='Someone with the IP address %1$s has just requested the user name of the account at %2$s associated with this e-mail address.' tag1=$hostname tag2=$sitename}</p>
{/if}

<p>{gt text='The user name for your account is: %1$s' tag1=$uname}</p>

<p>{gt text='You can use this user name to log into your account here:'} <a href="{$url}">{gt text='Log into your account'}</a></p>

<p>{gt text='(If you cannot click on the link, you can copy this URL and paste it into your browser: %s )' tag1=$url}</p>

<p>{if !$adminRequested}{gt text='If the request was not made by you then you don\'t need to take any action.'} {/if}{gt text='You are the only recipient of this message, and your user name has not been sent to any other e-mail address.'}{if !$adminRequested} {gt text='You can just delete this message.'}{/if}</p>
