{gt text='Account information recovery' assign='templatetitle'}
{modulelinks modname='ZikulaUsersModule' type='user'}
{include file='User/menu.tpl'}

<p class="alert alert-info">{gt text="Please enter your e-mail address below and click the 'Submit' button. You will be sent an e-mail with your account information."}</p>

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='user' func='lostUname'}" method="post">
    <input type="hidden" id="lostunamecsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
    <fieldset>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="users_email">{gt text='E-mail address'}</label>
            <div class="col-lg-9">
                <input id="users_email" type="text" class="form-control" name="email" size="40" maxlength="60" value="{$email|safetext}" />
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Submit' __title='Submit' __text='Submit'}
        </div>
    </div>
</form>
