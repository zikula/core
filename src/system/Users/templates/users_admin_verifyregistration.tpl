{gt text='Confirm verification code is to be sent to \'%1$s\'' tag1=$reginfo.uname assign='templatetitle'}
{include file='users_admin_menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='Users' src='admin.png' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {include file='users_admin_includeregistration.tpl'}

    <form id="users_verifyregistration" class="z-form" action="{modurl modname='Users' type='admin' func='verifyRegistration'}" method="post">
        <div>
            <input type="hidden" id="users_authid" name="authid" value="{insert name='generateauthkey' module='Users'}" />
            <input type="hidden" id="users_uid" name="uid" value="{$reginfo.uid}" />
            <input type="hidden" id="users_force" name="force" value="{$force}" />
            <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview}" />
            <input type="hidden" id="users_confirmed" name="confirmed" value="true" />
            <div class="z-formbuttons z-buttons">
                {strip}
                {gt assign='titleIfSent' text='Resend verification code'}
                {gt assign='titleIfNotSent' text='Send verification code'}
                {if !empty($reginfo.verifycode)}
                {assign var='actionTitle' value=$titleIfSent}
                {else}
                {assign var='actionTitle' value=$titleIfNotSent}
                {/if}
                {/strip}
                {button id='confirm' type='submit' src='button_ok.png' set='icons/extrasmall' alt=$actionTitle title=$actionTitle text=$actionTitle}
                <a href="{$cancelurl|safetext}">{img modname='core' src='button_cancel.png' set='icons/extrasmall'  __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
</div>
