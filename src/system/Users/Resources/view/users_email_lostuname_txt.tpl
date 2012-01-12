{gt text='\'%1$s\' account information' tag1=$sitename assign='subject'}

{gt text='Hello!'}

{if $adminRequested}
{gt text='The administrator at %s requested that you receive your user name via e-mail.' tag1=$sitename}
{else}
{gt text='Someone with the IP address %1$s has just requested the user name of the account at %2$s associated with this e-mail address.' tag1=$hostname tag2=$sitename}
{/if}

{gt text='The user name for your account is: %1$s' tag1=$uname}

{if !empty($authentication_methods)}
{gt text='You may use the following information to log into your account at:'} {$url}

{foreach from=$authentication_methods item='authentication_method' name='authentication_methods'}
{$authentication_method.short_description}: {$authentication_method.uname}{if !empty($authentication_method.link)} ({$authentication_method.link}){/if}

{/foreach}
{else}
{gt text='You do not have any active methods available to log into our site. Please contact a site administrator.'}

{/if}
{if !$adminRequested}{gt text='If the request was not made by you then you don\'t need to take any action.'} {/if}{gt text='You are the only recipient of this message, and your user name has not been sent to any other e-mail address.'}{if !$adminRequested} {gt text='You can just delete this message.'}{/if}
