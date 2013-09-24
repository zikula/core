{adminheader}
<h3>
    {icon type="remove" size="small"}
    {gt text='Deny registration request of \'%1$s\'' tag1=$reginfo.uname}
</h3>

<p class="alert alert-warning">{gt text="Warning! This will delete the registration from the database. It cannot be undone."}</p>

{include file='Admin/includeregistration.tpl'}

<form id="users_denyregistration" class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='admin' func='denyRegistration'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uid" name="uid" value="{$reginfo.uid}" />
        <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview}" />
        <input type="hidden" id="users_confirmed" name="confirmed" value="true" />
        <fieldset>
            <legend>{gt text='Applicant notification'}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_usernotify">{gt text="Notify the applicant via e-mail?"}</label>
                <div class="col-lg-9">
                <div id="users_usernotify">
                    <input id="users_usernotifyyes" type="radio" name="usernotify" value="1" />
                    <label for="users_usernotifyyes">{gt text="Yes"}</label>
                    <input id="users_usernotifyno" type="radio" name="usernotify" value="0" checked="checked" />
                    <label for="users_usernotifyno">{gt text="No"}</label>
                </div>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_reason">{gt text="Reason"}</label>
                <div class="col-lg-9">
                <textarea id="users_reason" name="reason" cols="50" rows="6"></textarea>
                <div class="help-block">{gt text='Note: The reason is sent in the user notification e-mail. All formatting, including extra spaces and blank lines are ignored.'}</div>
            </div>
        </div>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button id='confirm' type='submit' src='delete_user.png' set='icons/extrasmall' __alt='Delete registration' __title='Delete registration' __text='Delete registration'}
                <a class="btn btn-default" href="{$cancelurl|safetext}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall'  __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
