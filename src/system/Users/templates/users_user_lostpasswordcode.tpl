{gt text='Enter confirmation code' assign='templatetitle'}
{modulelinks modname='Users' type='user'}
{include file='users_user_menu.tpl'}

<p class="z-informationmsg">{gt text="Please enter and EITHER your user name OR your e-mail address, and also enter the confirmation code you received. Once you enter this information and click the 'Submit' button you will receive a new password via e-mail."}</p>

<form class="z-form" action="{modurl modname='Users' type='user' func='lostPasswordCode'}" method="post">
    <div>
        <input type="hidden" id="lostpasswordcsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_lostpassword_setpass" name="setpass" value="0" />
        <fieldset>
            <div class="z-formrow">
                <label for="users_uname">{gt text='User name'}</label>
                <input id="users_uname" type="text" name="uname" size="25" maxlength="25" value="{$uname}" />
            </div>
            <div class="z-formrow">
                <span class="z-label">{gt text='or'}</span>
            </div>
            <div class="z-formrow">
                <label for="users_email">{gt text='E-mail address'}</label>
                <input id="users_email" type="text" name="email" size="40" maxlength="60" value="{$email}" />
            </div>
        </fieldset>
        <fieldset>
            <div class="z-formrow">
                <label for="users_code">{gt text='Confirmation code'}</label>
                <input id="users_code" type="text" name="code" size="5" value="{$code}" />
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Submit' __title='Submit' __text='Submit'}
        </div>
    </div>
</form>
