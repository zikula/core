{gt text='Verify your request to change your e-mail address at \'%1$s\'' tag1=$sitename assign='subject'}
<p>{gt text='Hello! You requested to change your e-mail address for user account \'%1$s\' at %2$s from %3$s to %4$s.' tag1=$uname tag2=$sitename tag3=$email tag4=$newemail}
   {gt text='You must confirm this change before it will take effect.'}{if $modvars.Users.chgemail_expiredays > 0} {gt text='If you do not confirm this change within the next day, the request will be deleted from our system.' plural='If you do not confirm this change within the next %1$s days, the request will be deleted from our system.' tag1=modvars.Users.chgemail_expiredays count=$modvars.Users.chgemail_expiredays}{/if}</p>

<p>{gt text="You can confirm the e-mail address change by clicking on this link: "} <a href="{$url}">{gt text='Verify my e-mail address change.'}</a></p>

<p>{gt text="(If you cannot click on the link above, you can copy this URL into your browser:"} {$url}</p>

<p>{gt text="If you did not make this request then you do not need to take any action."}
   {gt text="The e-mail address wil not be changed unless the confirmation code is used, and you are the only recipient of this message. You can just delete this message."}</p>