{gt text='Welcome to %1$s, %2$s!' tag1=$sitename tag2=$reginfo.uname assign='subject'}

<h3>{gt text='Welcome to %1$s!' tag1=$sitename}</h3>

<p>{gt text='Hello!'}</p>

<p>{gt text='This e-mail address (\'%1$s\') has been used to register an account on \'%2$s\' (%3$s).' tag1=$reginfo.email tag2=$sitename tag3=$siteurl}
{gt text="The information that was registered is as follows:"}</p>

<p>{gt text="User name"}: {$reginfo.uname}<br />
{if !empty($createdpassword)}{gt text="Password"}: {$createdpassword}{else}{gt text="Password reminder"}: {$reginfo.passreminder}{/if}</p>

{if !empty($createdpassword)}<p>{gt text="(This is the only time you will receive your password. Please keep it in a safe place.)"}</p>{/if}

{if !$reginfo.isapproved}<p>{gt text="Thank you for your application for a new account.  Your application has been forwarded to the site administrator for review. Please expect a message once the review process is complete."}</p>
{elseif !$admincreated}<p>{gt text="Your account application has been approved. Thank you for your patience during the new account application review process."}</p>
{elseif $admincreated}<p>{gt text="The web site administrator has created this new account for you."}</p>{/if}

{if $reginfo.isapproved}<p>{gt text="You may now log into the web site with your user name and password."}</p>{/if}