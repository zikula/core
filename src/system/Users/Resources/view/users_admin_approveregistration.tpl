{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="add" size="small"}
    <h3>{gt text='Approve registration of \'%1$s\'' tag1=$reginfo.uname}</h3>
</div>

{if !$reginfo.isverified}
{if $force}
<p class="z-warningmsg">{gt text="Warning! The e-mail address for this registration has not been verified. Approving this registration will create a new user record without completing the e-mail verification process."}</p>
{elseif isset($modvars.Users.moderation_order) && ($modvars.Users.moderation_order == 'Users_Constant::APPROVAL_AFTER'|const)}
<p class="z-warningmsg">{gt text="Warning! The e-mail address for this registration has not been verified. You are pre-approving this registration, and a new user record will be created upon completion of the e-mail verification process."}</p>
{/if}
{/if}

{include file='users_admin_includeregistration.tpl'}

<form id="users_approveregistration" class="z-form" action="{modurl modname='Users' type='admin' func='approveRegistration'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uid" name="uid" value="{$reginfo.uid}" />
        <input type="hidden" id="users_force" name="force" value="{$force}" />
        <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview}" />
        <input type="hidden" id="users_confirmed" name="confirmed" value="true" />
        <div class="z-formbuttons z-buttons">
            {strip}
            {if !$reginfo.isverified && $force}
            {gt text='Skip Verification' assign='actionTitle'}
            {else}
            {gt text='Approve' assign='actionTitle'}
            {/if}
            {/strip}
            {button id='confirm' type='submit' src='button_ok.png' set='icons/extrasmall' alt=$actionTitle title=$actionTitle text=$actionTitle}
            <a href="{$cancelurl|safetext}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall'  __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}