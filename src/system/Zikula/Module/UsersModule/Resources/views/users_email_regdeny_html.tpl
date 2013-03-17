{gt text='Your recent request at %1$s.' tag1=$sitename tag2=$reginfo.uname assign='subject'}

<h3>{gt text='A message from %1$s...' tag1=$sitename}</h3>

<p>{gt text='Recently, this e-mail address (\'%1$s\') was used to request an account on \'%2$s\' (%3$s).' tag1=$reginfo.email tag2=$sitename tag3=$siteurl}
{gt text="The information that was registered is as follows:"}</p>

<p>{gt text="User name"}: {$reginfo.uname}<br />

<p>{gt text="Thank you for your application for a new account. At this time we are unable to approve your application."}</p>

{if !empty($reason)}<p>{$reason}</p>{/if}
