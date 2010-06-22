{gt text='Welcome to %1$s, %2$s!' tag1=$sitename tag2=$reginfo.uname assign='subject'}

{gt text='Welcome to %1$s!' tag1=$sitename}

{gt text='Hello!'}

{gt text='This e-mail address (\'%1$s\') has been used to register an account on \'%2$s\' (%3$s).' tag1=$reginfo.email tag2=$sitename tag3=$siteurl}
{gt text="The information that was registered is as follows:"}

{gt text="User name"}: {$reginfo.uname}
{if !empty($createdpassword)}{gt text="Password"}: {$createdpassword}{else}{gt text="Password reminder"}: {$reginfo.passreminder}{/if}

{if !empty($createdpassword)}{gt text="(This is the only time you will receive your password. Please keep it in a safe place.)"}{/if}

{if !$reginfo.isapproved}{gt text="Thank you for your application for a new account.  Your application has been forwarded to the site administrator for review. Please expect a message once the review process is complete."}
{elseif !$admincreated}{gt text="Your account application has been approved. Thank you for your patience during the new account application review process."}
{elseif $admincreated}{gt text="The web site administrator has created this new account for you."}{/if}

{if $reginfo.isapproved}{gt text="You may now log into the web site with your user name and password."}{/if}