<p>{gt text='Hello %s' tag=$recipient.name},</p>

<p>{gt text='Your route "%s" has been changed.' tag=$mailData.name}</p>

<p>{gt text='It\'s new state is: %s' tag=$mailData.newState}</p>

{if $mailData.remarks ne ''}
    <p>{gt text='Additional remarks:'} {$mailData.remarks|safetext}</p>
{/if}


<p>{gt text='This mail has been sent automatically by %s.' tag=$modvars.ZConfig.sitename}</p>
