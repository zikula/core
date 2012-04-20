{gt text='Lost password recovery' assign='templatetitle'}
{modulelinks modname='Users' type='user'}
{include file='users_user_menu.tpl'}

<p class="z-informationmsg">{gt text="Please enter EITHER your user name OR your e-mail address below and click the 'Submit' button. You will be e-mailed a confirmation code. Check your e-mail, and follow the given instructions."}</p>

<form class="z-form" action="{modurl modname='Users' type='user' func='lostpassword'}" method="post">
    <div>
        <input type="hidden" id="lostpasswordcsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <div class="z-formrow">
                <label for="users_uname">{gt text='User name'}</label>
                <input id="users_uname" type="text" name="uname" size="25" maxlength="25" value="{$uname|safetext}" />
            </div>
            <div class="z-formrow">
                <span class="z-label">{gt text='or'}</span>
            </div>
            <div class="z-formrow">
                <label for="users_email">{gt text='E-mail address'}</label>
                <input id="users_email" type="text" name="email" size="40" maxlength="60" value="{$email|safetext}" />
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Submit' __title='Submit' __text='Submit'}
        </div>
    </div>
</form>
