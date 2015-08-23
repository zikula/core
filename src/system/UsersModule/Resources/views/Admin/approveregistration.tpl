{adminheader}
<h3>
    <span class="fa fa-plus"></span>
    {gt text='Approve registration of \'%1$s\'' tag1=$reginfo.uname}
</h3>

{if !$reginfo.isverified}
{if $force}
<p class="alert alert-warning">{gt text="Warning! The e-mail address for this registration has not been verified. Approving this registration will create a new user record without completing the e-mail verification process."}</p>
{elseif isset($modvars.ZikulaUsersModule.moderation_order) && ($modvars.ZikulaUsersModule.moderation_order == 'Zikula\UsersModule\Constant::APPROVAL_AFTER'|const)}
<p class="alert alert-warning">{gt text="Warning! The e-mail address for this registration has not been verified. You are pre-approving this registration, and a new user record will be created upon completion of the e-mail verification process."}</p>
{/if}
{/if}

<form id="users_approveregistration" class="form-horizontal" role="form" action="{route name='zikulausersmodule_admin_approveregistration'}" method="post">
    {include file='Admin/includeregistration.tpl'}
    
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uid" name="uid" value="{$reginfo.uid}" />
        <input type="hidden" id="users_force" name="force" value="{$force}" />
        <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview}" />
        <input type="hidden" id="users_confirmed" name="confirmed" value="true" />
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
            {strip}
            {if !$reginfo.isverified && $force}
            {gt text='Skip Verification' assign='actionTitle'}
            {else}
            {gt text='Approve' assign='actionTitle'}
            {/if}
            {/strip}
            <button id='confirm' class="btn btn-success" type='submit' title="{$actionTitle}">
                {gt text=$actionTitle}
            </button>
            <a class="btn btn-default" href="{$cancelurl|safetext}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}