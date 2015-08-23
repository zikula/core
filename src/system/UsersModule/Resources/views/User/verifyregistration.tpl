{strip}
    {gt text='Enter verification code' assign='templatetitle'}
    {if $modvars.ZikulaUsersModule.use_password_strength_meter eq 1}
        {pageaddvar name='javascript' value='jquery'}
        {pageaddvar name='javascript' value='system/UsersModule/Resources/public/js/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                (function($) {
                    $(document).ready(function() {
                        ZikulaUsersPassMeter.init('users_newpass', 'users_verifyregistration_passmeter',{
                            username: 'users_uname',
                            minLength: '{{$modvars.ZikulaUsersModule.minpass}}'
                        });
                    });
                })(jQuery);
            </script>
        {/pageaddvarblock}
    {/if}
{/strip}
            
{include file='User/menu.tpl'}

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

<form class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_verifyregistration'}" method="post">
    <div>
        <input type="hidden" id="users_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_setpass" name="setpass" value="{$setpass}" />
        <fieldset>
            <legend>{gt text='Verification code'}</legend>
            <p class="alert alert-info">{gt text="Please enter your user name and the verification code you received."}</p>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_uname">{gt text='User name'}</label>
                <div class="col-sm-9">
                    <input id="users_uname" type="text" class="form-control" name="uname" size="25" maxlength="25" value="{$verify_uname}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_verifycode">{gt text='Verification code'}</label>
                <div class="col-sm-9">
                <input id="users_verifycode" type="text" class="form-control" name="verifycode" size="5" maxlength="6" value="{$verifycode}" />
                </div>
            </div>
        </fieldset>
        {if $setpass}
        <fieldset>
            <legend>{gt text='Create a password'}</legend>
            <p class="alert alert-info">{gt text='You must establish a password for your account before the verification process is complete.'}</p>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_newpass">{gt text='Password'}</label>
                <div class="col-sm-9">
                    <input id="users_newpass" type="password" class="form-control" name="newpass" size="25" maxlength="60" value="" />
                </div>
                {if $modvars.ZikulaUsersModule.use_password_strength_meter == 1}
                <div id="users_verifyregistration_passmeter">
                </div>
                {/if}
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_newpassagain">{gt text='Password (repeat for verification)'}</label>
                <div class="col-sm-9">
                    <input id="users_newpassagain" type="password" class="form-control" name="newpassagain" size="25" maxlength="60" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_newpassreminder">{gt text='Password reminder'}</label>
                <div class="col-sm-9">
                    <input id="users_newpassreminder" type="text" class="form-control" name="newpassreminder" size="25" maxlength="128" value="{$newpassreminder}" />
                    <div class="sub help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                    <div class="help-block alert alert-info">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
                </div>
            </div>
        </fieldset>
        {/if}
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                {button class="btn btn-success" __alt='Submit' __title='Submit' __text='Submit'}
                <a class="btn btn-default" href="{homepage|safetext}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
