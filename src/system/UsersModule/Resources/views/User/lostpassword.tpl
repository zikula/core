{gt text='Lost password recovery' assign='templatetitle'}

{include file='User/menu.tpl'}
<p class="alert alert-info">{gt text="Please enter EITHER your user name OR your e-mail address below and click the 'Submit' button. You will be e-mailed a confirmation code. Check your e-mail, and follow the given instructions."}</p>
<form class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_lostpassword'}" method="post">
    <div>
        <input type="hidden" id="lostpasswordcsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_uname">{gt text='User name'}</label>
                <div class="col-sm-9">
                    <input id="users_uname" type="text" class="form-control" name="uname" size="25" maxlength="25" value="{$uname|safetext}" />
                </div>
            </div>
            {if ($modvars.ZikulaUsersModule.reg_uniemail|default:1 == 1)}
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='or'}</label>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_email">{gt text='E-mail address'}</label>
                <div class="col-sm-9">
                    <input id="users_email" type="email" class="form-control" name="email" size="40" maxlength="60" value="{$email|safetext}" />
                </div>
            </div>
            {/if}
        </fieldset>
        <div class="form-group">
             <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Submit'}">
                    {gt text='Submit'}
                </button>
            </div>
        </div>
    </div>
</form>