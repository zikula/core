{adminheader}

{if $user_must_change_password}
{gt text='Cancel forced password change for %1$s' tag1=$user_obj.uname|safetext assign='templatetitle'}
{else}
{gt text='Force a change of password for %1$s' tag1=$user_obj.uname|safetext assign='templatetitle'}
{/if}

<h3>
    {img modname='core' set='icons/small' src='password_expire.png' alt=$templatetitle}
    {$templatetitle}
</h3>

{if $user_must_change_password}
<p class="alert alert-warning">{gt text='Do you want to cancel the forced password change for %1$s?' tag1=$user_obj.uname|safetext}</p>
{else}
<p class="alert alert-warning">{gt text='Do you want to force a password change during the next login attempt for %1$s?' tag1=$user_obj.uname|safetext}</p>
{/if}

<form class="form-horizontal" role="form" action="{route name='zikulausersmodule_admin_toggleforcedpasswordchange'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='Confirmation prompt'}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="userid" value="{$user_obj.uid|safetext}" />
        <input type="hidden" name="user_must_change_password" value="{if $user_must_change_password}0{else}1{/if}" />
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                {if $user_must_change_password}
                    <button class="btn btn-danger" title="{gt text='Yes, cancel the change of password'}">
                        {gt text='Yes, cancel the change of password'}
                    </button>
                {else}
                    <button class="btn btn-danger" title="{gt text='Yes, force the change of password'}">
                        {gt text='Yes, force the change of password'}
                    </button>
                {/if}
                <a class="btn btn-default" href="{route name='zikulausersmodule_admin_view'}" title="{gt text='No'}">{gt text='No'}</a>
            </div>
        </div>
    </fieldset>
</form>

{adminfooter}