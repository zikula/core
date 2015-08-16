{pageaddvar name='javascript' value='polyfill'}

{gt text='Enter confirmation code' assign='templatetitle'}

{include file='User/menu.tpl'}
<p class="alert alert-info">{gt text="Please enter EITHER your user name OR your e-mail address, and also enter the confirmation code you received. Once you enter this information and click the 'Submit' button you will receive a new password via e-mail."}</p>
<form class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_lostpasswordcode'}" method="post">
    <div>
        <input type="hidden" id="lostpasswordcsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_lostpassword_setpass" name="setpass" value="0" />
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_uname">{gt text='User name'}</label>
                <div class="col-sm-9">
                    <input id="users_uname" type="text" class="form-control" name="uname" size="25" maxlength="25" value="{$uname}" />
                </div>
            </div>
            {if ($modvars.ZikulaUsersModule.reg_uniemail|default:true)}
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='or'}</label>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_email">{gt text='E-mail address'}</label>
                <div class="col-sm-9">
                    <input id="users_email" type="email" class="form-control" name="email" size="40" maxlength="60" value="{$email}" />
                </div>
            </div>
            {/if}
        </fieldset>
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_code">{gt text='Confirmation code'}</label>
                <div class="col-sm-9">
                    <input id="users_code" type="text" class="form-control" name="code" size="5" value="{$code}" required="required" />
                </div>
            </div>
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