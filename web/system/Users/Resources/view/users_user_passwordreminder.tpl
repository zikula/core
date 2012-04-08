{gt text='Lost password recovery' assign='templatetitle'}
{modulelinks modname='Users' type='user'}
{include file='users_user_menu.tpl'}

{if !empty($passreminder)}
<div class="z-form">
    <div class="z-fieldset">
        <div class="z-formrow">
            <p class="z-label">{gt text='User name'}</p>
            <div class="z-text z-bold">{$uname}</div>
        </div>
        <div class="z-formrow">
            <p class="z-label">{gt text='Password reminder'}</p>
            <div class="z-text z-bold">{$passreminder}</div>
        </div>
        <div class="z-formbuttons z-buttons">
            <p>{gt text="I remember my password now."}</p>
            <a href="{modurl modname='Users' type='user' func='login'}">{img id='users_cancel' modname='core' set='icons/extrasmall' src='1rightarrow.png' __alt='Go to log-in screen' __title='Go to log-in screen'} {gt text="Go to log-in screen"}</a>
        </div>
    </div>
</div>
{/if}

{if !empty($errormessages)}
<div id="users_errormessages_div" class="z-errormsg">
    <p>Please correct the following items:</p>
    <ul id="users_errormessages">
        {foreach from=$errormessages item='message'}
        <li>{$message}</li>
        {/foreach}
    </ul>
</div>
{/if}

<form class="z-form" action="{modurl modname='Users' type='user' func='lostPasswordCode'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uname" name="uname" value="{$uname}" />
        <input type="hidden" id="users_setpass" name="setpass" value="1" />
        <fieldset>
            <legend>{gt text='Reset your password'}</legend>
            {if !empty($passreminder)}<p class="z-informationmsg">{gt text='If you still do not remember your password, you can create a new one here.'}</p>{/if}
            <div class="z-formrow">
                <label for="users_newpass">{gt text='Password'}</label>
                <input id="users_newpass" type="password" name="newpass" size="25" maxlength="60" value="" />
            </div>
            <div class="z-formrow">
                <label for="users_newpassagain">{gt text='Password (repeat for verification)'}</label>
                <input id="users_newpassagain" type="password" name="newpassagain" size="25" maxlength="60" value="" />
            </div>
            <div class="z-formrow">
                <label for="users_newpassreminder">{gt text='Password reminder'}</label>
                <input id="users_newpassreminder" type="text" name="newpassreminder" size="25" maxlength="128" value="{$newpassreminder}" />
                <div class="z-sub z-formnote">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="z-formnote z-informationmsg">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
            </div>
            <div class="z-formbuttons z-buttons">
                {button src='button_ok.png' set='icons/extrasmall' __alt='Submit' __title='Submit' __text='Submit'}
                <a href="{homepage|safetext}" title="{gt text='Cancel'}">{img id='users_cancel' modname='core' set='icons/extrasmall' src='button_cancel.png' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </fieldset>
    </div>
</form>
