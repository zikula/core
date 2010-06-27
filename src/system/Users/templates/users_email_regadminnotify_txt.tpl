{strip}
{if $reginfo.pendingApproval}
    {gt text='New registration pending approval' assign='heading'}
    {gt text='New registration pending approval: %s' tag1=$reginfo.uname assign='subject'}
{elseif $reginfo.pendingVerification}
    {gt text='New registration pending e-mail verification' assign='heading'}
    {gt text='New registration pending verification: %s' tag1=$reginfo.uname assign='subject'}
{else}
    {gt text='New user activated' assign='heading'}
    {gt text='New user activated: %s' tag1=$reginfo.uname assign='subject'}
{/if}
{/strip}{$heading}

{gt text='A new user account has been activated on %1$s.' tag1=$sitename}
{if $adminCreated}{gt text='It was created by an administrator or sub-administrator logged in as \'%1$s\'.' tag1=$adminUname}
{/if}{gt text='The account details are as follows:'}
    
{gt text='User name: \'%s\'.' tag1=$reginfo.uname}
