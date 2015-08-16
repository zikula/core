{gt text='Reset your password at \'%1$s\'' tag1=$sitename assign='subject'}

{gt text='Hello!'}

{gt text='The user account \'%1$s\' at %2$s has this e-mail address associated with it.' tag1=$uname tag2=$sitename}

{if $adminRequested}
{gt text='The administrator at %s requested that you receive a confirmation code that will allow you to reset your password.' tag1=$sitename}
{else}
{gt text='Someone with the IP address %s has just requested a confirmation code to allow the password for your account to be reset.' tag1=$hostname}
{/if}

{gt text='The confirmation code is: %s' tag1=$code}

{gt text='With this confirmation code, you can now create a new password by clicking on this link: %s' tag1=$url}
{gt text='(If you cannot click on the link, you can copy the URL and paste it into your browser.)'}

{if !$adminRequested}{gt text='If the request was not made by yourself then you don\'t need to take any action.'} {/if}{gt text='The password won\'t be changed unless the confirmation code is used, and you are the only recipient of this message.'}{if !$adminRequested} {gt text='You can just delete the message and log-in with your existing password next time you visit the site.'}{/if}
