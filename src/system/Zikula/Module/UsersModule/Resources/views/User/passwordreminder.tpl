{gt text='Lost password recovery' assign='templatetitle'}
{modulelinks modname='ZikulaUsersModule' type='user'}
{include file='User/menu.tpl'}

{if !empty($passreminder)}
<div class="form-horizontal" role="form">
    <div class="z-fieldset">
        <div class="form-group">
            <p class="z-label">{gt text='User name'}</p>
            <div class="z-text z-bold">{$uname}</div>
        </div>
        </div>
        <div class="form-group">
            <p class="z-label">{gt text='Password reminder'}</p>
            <div class="z-text z-bold">{$passreminder}</div>
        </div>
        <div class="z-formbuttons z-buttons">
            <p>{gt text="I remember my password now."}</p>
            <a href="{modurl modname='ZikulaUsersModule' type='user' func='login'}">{img id='users_cancel' modname='core' set='icons/extrasmall' src='1rightarrow.png' __alt='Go to log-in screen' __title='Go to log-in screen'} {gt text="Go to log-in screen"}</a>
        </div>
    </div>
</div>
{/if}

{if !empty($errormessages)}
<div id="users_errormessages_div" class="alert alert-danger">
    <p>Please correct the following items:</p>
    <ul id="users_errormessages">
        {foreach from=$errormessages item='message'}
        <li>{$message}</li>
        {/foreach}
    </ul>
</div>
{/if}

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='user' func='lostPasswordCode'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uname" name="uname" value="{$uname}" />
        <input type="hidden" id="users_setpass" name="setpass" value="1" />
        <fieldset>
            <legend>{gt text='Reset your password'}</legend>
            {if !empty($passreminder)}<p class="alert alert-info">{gt text='If you still do not remember your password, you can create a new one here.'}</p>{/if}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_newpass">{gt text='Password'}</label>
            <p class="z-label">{gt text='User name'}</p>
            <div class="z-text z-bold">{$uname}</div>
        </div>
        </div>
        <div class="form-group">
            <p class="z-label">{gt text='Password reminder'}</p>
            <div class="z-text z-bold">{$passreminder}</div>
        </div>
        <div class="z-formbuttons z-buttons">
            <p>{gt text="I remember my password now."}</p>
            <a href="{modurl modname='ZikulaUsersModule' type='user' func='login'}">{img id='users_cancel' modname='core' set='icons/extrasmall' src='1rightarrow.png' __alt='Go to log-in screen' __title='Go to log-in screen'} {gt text="Go to log-in screen"}</a>
        </div>
    </div>
</div>
{/if}

{if !empty($errormessages)}
<div id="users_errormessages_div" class="alert alert-danger">
    <p>Please correct the following items:</p>
    <ul id="users_errormessages">
        {foreach from=$errormessages item='message'}
        <li>{$message}</li>
        {/foreach}
    </ul>
</div>
{/if}

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='user' func='lostPasswordCode'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uname" name="uname" value="{$uname}" />
        <input type="hidden" id="users_setpass" name="setpass" value="1" />
        <fieldset>
            <legend>{gt text='Reset your password'}</legend>
            {if !empty($passreminder)}<p class="alert alert-info">{gt text='If you still do not remember your password, you can create a new one here.'}</p>{/if}
            <div class="form-group">
                <div class="col-lg-9">
                <input id="users_newpass" type="text" class="form-control" name="newpass" size="25" maxlength="60" value="" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_newpassagain">{gt text='Password (repeat for verification)'}</label>
                <div class="col-lg-9">
                <input id="users_newpassagain" type="text" class="form-control" name="newpassagain" size="25" maxlength="60" value="" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_newpassreminder">{gt text='Password reminder'}</label>
                <div class="col-lg-9">
                <input id="users_newpassreminder" type="text" class="form-control" name="newpassreminder" size="25" maxlength="128" value="{$newpassreminder}" />
                <div class="z-sub help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="help-block alert alert-info">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
            </div>
            <div class="z-formbuttons z-buttons">
                {button src='button_ok.png' set='icons/extrasmall' __alt='Submit' __title='Submit' __text='Submit'}
                <a href="{homepage|safetext}" title="{gt text='Cancel'}">{img id='users_cancel' modname='core' set='icons/extrasmall' src='button_cancel.png' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
        </fieldset>
    </div>
</form>
