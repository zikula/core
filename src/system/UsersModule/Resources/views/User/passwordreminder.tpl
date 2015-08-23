{gt text='Lost password recovery' assign='templatetitle'}
{include file='User/menu.tpl'}

{if !empty($passreminder)}
<div class="form-horizontal" role="form">
    <fieldset>
        <legend>{gt text='Reminder'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="users_uname">{gt text='User name'}</label>
            <div class="col-sm-9">
                <div class="form-control-static" id="users_uname">{$uname}</div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="users_passreminder">{gt text='Password reminder'}</label>
            <div class="col-sm-9">
                <div class="form-control-static" id="users_passreminder">{$passreminder}</div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <p class="alert alert-success">{gt text="I remember my password now."}</p>
                <a class="btn btn-success" href="{route name='zikulausersmodule_user_login'}"><i class="fa fa-arrow-right"></i> {gt text="Go to log-in screen"}</a>
            </div>
        </div>
    </fieldset>
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

<form class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_lostpasswordcode'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_uname" name="uname" value="{$uname}" />
        <input type="hidden" id="users_setpass" name="setpass" value="1" />
        <fieldset>
            <legend>{gt text='Reset your password'}</legend>
            {if !empty($passreminder)}<p class="alert alert-info">{gt text='If you still do not remember your password, you can create a new one here.'}</p>{/if}
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_newpass">{gt text='Password'}</label>
                <div class="col-sm-9">
                    <input class="form-control" id="users_newpass" type="password" name="newpass" maxlength="60" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_newpassagain">{gt text='Password (repeat for verification)'}</label>
                <div class="col-sm-9">
                    <input class="form-control" id="users_newpassagain" type="password" name="newpassagain" maxlength="60" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_newpassreminder">{gt text='Password reminder'}</label>
                <div class="col-sm-9">
                    <input class="form-control" id="users_newpassreminder" type="text" name="newpassreminder" maxlength="128" value="{$newpassreminder}" />
                    <em class="help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</em>
                    <div class="alert alert-info">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
                </div>
            </div>
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" type="submit" name="Save">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{route name='zikulausersmodule_user_index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>
