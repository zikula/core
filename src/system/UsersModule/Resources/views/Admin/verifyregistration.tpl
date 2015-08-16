{adminheader}
<h3>
    <span class="fa fa-user"></span>
    {gt text='Confirm verification code is to be sent to \'%1$s\'' tag1=$reginfo.uname}
</h3>

{include file='Admin/includeregistration.tpl'}

<form id="users_verifyregistration" class="form-horizontal" role="form" action="{route name='zikulausersmodule_admin_verifyregistration'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uid" name="uid" value="{$reginfo.uid}" />
        <input type="hidden" id="users_force" name="force" value="{$force}" />
        <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview}" />
        <input type="hidden" id="users_confirmed" name="confirmed" value="true" />
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
            {strip}
            {gt assign='titleIfSent' text='Resend verification code'}
            {gt assign='titleIfNotSent' text='Send verification code'}
            {if !empty($reginfo.verifycode)}
            {assign var='actionTitle' value=$titleIfSent}
            {else}
            {assign var='actionTitle' value=$titleIfNotSent}
            {/if}
            {/strip}
                {button class="btn btn-success" id='confirm' type='submit' alt=$actionTitle title=$actionTitle text=$actionTitle}
                <a class="btn btn-default" href="{$cancelurl|safetext}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
