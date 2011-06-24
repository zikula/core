{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="display" size="small"}
    <h3>{gt text='Registration for \'%1$s\'' tag1=$reginfo.uname}</h3>
</div>

{include file='users_admin_includeregistration.tpl'}

<div class="z-buttons z-center" style="margin:0 0 1em; padding:1em 0;">
    {strip}
    {assign var="regactions" value=$actions.list[$reginfo.uid]}
    {gt assign='titleIfSent' text='Resend verification code'}
    {gt assign='titleIfNotSent' text='Send verification code'}
    {* For the following, (isset($regactions.optname) == true) means that the current user can, in general, perform the operation; *}
    {* ($regactions.optname == true) means that the operation can be performed for that individual registration record. *}
    {if isset($regactions.approve) && $regactions.approve && !$reginfo.isverified}
    <a href="{$regactions.approve|safetext}">{img src='button_ok.png' modname='core' set='icons/extrasmall' __title='Approve' __alt='Approve'} {gt text='Approve'}</a>
    {elseif isset($regactions.approve) && $regactions.approve && $reginfo.isverified}
    <a href="{$regactions.approve|safetext}">{img src='add_user.png' modname='core' set='icons/extrasmall' __title='Approve (creates a new user account)' __alt='Approve (creates a new user account)'} {gt text='Approve and Add User'}</a>
    {/if}
    {if isset($regactions.deny) && $regactions.deny}
    <a href="{$regactions.deny|safetext}">{img src='delete_user.png' modname='core' set='icons/extrasmall' __title='Deny (deletes registration)' __alt='Deny (deletes registration)'} {gt text='Deny'}</a>
    {/if}
    {if isset($regactions.modify) && $regactions.modify}
    <a href="{$regactions.modify|safetext}">{img src='xedit.png' modname='core' set='icons/extrasmall' __title='Edit registration details' __alt='Edit registration details'} {gt text='Edit'}</a>
    {/if}

    {if isset($regactions.verify) && $regactions.verify}
    {if !empty($reginfo.verifycode)}
    {assign var='actionTitle' value=$titleIfSent}
    {else}
    {assign var='actionTitle' value=$titleIfNotSent}
    {/if}
    <a href="{$regactions.verify|safetext}">{img src='mail_send.png' modname='core' set='icons/extrasmall' title=$actionTitle alt=$actionTitle}{$actionTitle}</a>
    {/if}

    {if isset($regactions.approveForce) && $regactions.approveForce && !$reginfo.isverified}
    <a href="{$regactions.approveForce|safetext}">{img src='db_update.png' modname='core' set='icons/extrasmall' __title='Skip verification (approves, and creates a new user account)' __alt='Skip verification (approves, and creates a new user account)'} {gt text='Add user without verification'}</a>
    {/if}

    <a href="{modurl modname='Users' type='admin' func='viewRegistrations' restoreview='true'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall'  __alt="Return to registrations list" __title="Return to registrations list"} {gt text='Return to registrations'}</a>
    {/strip}
</div>
{adminfooter}