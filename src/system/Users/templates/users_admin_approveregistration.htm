{gt text='Approve registration of \'%1$s\'' tag1=$reginfo.uname assign='templatetitle'}
{include file='users_admin_menu.htm'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='Users' src='admin.gif' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {if !$reginfo.isverified && $force}<div class="z-warningmsg">
        <p>{gt text="Warning! The e-mail address for this registration has not been verified. Approving this registration will create a new user record without completing the e-mail verification process."}</p>
    </div>{/if}

    {include file='users_admin_includeregistration.htm'}

    <form id="users_approveregistration" class="z-form" action="{modurl modname='Users' type='admin' func='approveRegistration'}" method="post">
        <input type="hidden" id="users_authid" name="authid" value="{insert name='generateauthkey' module='Users'}" />
        <input type="hidden" id="users_id" name="id" value="{$reginfo.id}" />
        <input type="hidden" id="users_force" name="force" value="{$force}" />
        <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview}" />
        <input type="hidden" id="users_confirmed" name="confirmed" value=true />
        <div class="z-formbuttons z-buttons">
            {strip}
            {if !$reginfo.isverified && $force}
                {gt text='Skip Verification' assign='actionTitle'}
            {else}
                {gt text='Approve' assign='actionTitle'}
            {/if}
            {/strip}
            {button id='confirm' type='submit' src='button_ok.gif' set='icons/extrasmall' alt=$actionTitle title=$actionTitle text=$actionTitle}
            <a href="{$cancelurl|safetext}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall'  __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </form>
</div>
