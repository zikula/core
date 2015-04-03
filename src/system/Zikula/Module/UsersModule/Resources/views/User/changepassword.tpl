{strip}
    {gt text='Password changer' assign='templatetitle'}
    {if $modvars.ZikulaUsersModule.use_password_strength_meter eq 1}
        {pageaddvar name='javascript' value='jquery'}
        {pageaddvar name='javascript' value='system/Zikula/Module/UsersModule/Resources/public/js/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                (function($) {
                    $(document).ready(function() {
                        ZikulaUsersPassMeter.init('newpassword', 'users_user_changepassword_passmeter',{
                            username: 'usernamehidden',
                            minLength: '{{$modvars.ZikulaUsersModule.minpass}}'
                        });
                    });
                })(jQuery);
            </script>
        {/pageaddvarblock}
    {/if}
{/strip}
{include file='User/menu.tpl'}

{if $login}
<div class="alert alert-warning">
    <p>{gt text="Before logging in, the site administrator has asked that you change the password for your account."}</p>
    {if $authentication_method.modname != 'ZikulaUsersModule'}<p>{gt text='Note: This changes the password for your account with the user name of \'%1$s\', here on this web site. It does not affect the password for any other method of logging in, such as the method you just used.'  tag1=$user_obj.uname}</p>{/if}
    <p>{gt text="If you leave this page without successfully changing your password, then you will not be logged in."}</p>
</div>
{/if}

<div class="alert alert-info">
    <p>{gt text='To change your password, please enter your current password, and then enter a new password (you must enter the new password twice to ensure that you have typed it correctly).'}</p>
    {if $login}<p>{gt text='Once you have successfully changed your password, the log-in process will continue.'}</p>{/if}
</div>

<form id="users_user_changepassword" class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_updatepassword'}" method="post">
    <fieldset>
        <legend>{gt text="Change password"}</legend>
        <input type="hidden" id="changepassword_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" id="usernamehidden" name="usernamehidden" value="{if $login}{$user_obj.uname}{else}{user}{/if}" />
        <div class="form-group">
            <label class="col-sm-3 control-label" for="oldpassword">{gt text="Current password"}</label>
            <div class="col-sm-9">
                <input type="password" id="oldpassword" name="oldpassword" class="form-control{if isset($password_errors.oldpass) && !empty($password_errors.oldpass)} form-error{/if}" value="" />
                {if isset($password_errors.oldpass) && !empty($password_errors.oldpass)}
                <div class="help-block alert alert-danger">
                    {foreach from=$password_errors.oldpass item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="newpassword">{gt text="New password"}</label>
            <div class="col-sm-9">
                <input name="newpassword" id="newpassword" type="password" class="form-control{if isset($password_errors.reginfo_pass) && !empty($password_errors.reginfo_pass)} form-error{/if}" value="" />
                {if isset($password_errors.reginfo_pass) && !empty($password_errors.reginfo_pass)}
                <div class="help-block alert alert-danger">
                    {foreach from=$password_errors.reginfo_pass item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
                {if isset($password_errors.pass) && !empty($password_errors.pass)}
                <div class="help-block alert alert-danger">
                    {foreach from=$password_errors.pass item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
                {if $modvars.ZikulaUsersModule.use_password_strength_meter == 1}
                <div id="users_user_changepassword_passmeter"></div>
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="newpasswordconfirm">{gt text="New password (repeat for verification)"}</label>
            <div class="col-sm-9">
                <input type="password" id="newpasswordconfirm" name="newpasswordconfirm" class="form-control {if isset($password_errors.passagain) && !empty($password_errors.passagain)} form-error{/if}" value="" />
                {if isset($password_errors.passagain) && !empty($password_errors.passagain)}
                <div class="help-block alert alert-danger">
                    {foreach from=$password_errors.passagain item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="passreminder">{gt text="New password reminder"}</label>
            <div class="col-sm-9">
                <input type="text" id="passreminder" name="passreminder" value="" class="form-control {if isset($password_errors.passreminder) && !empty($password_errors.passreminder)} form-error{/if}" size="25" maxlength="128" />
                {if isset($password_errors.passreminder) && !empty($password_errors.passreminder)}
                <div class="help-block alert alert-danger">
                    {foreach from=$password_errors.passreminder item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
                <div class="sub help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="help-block alert alert-warning">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            {if $login}
            <button class='btn btn-success' title="{gt text='Save and continue logging in'}">
                {gt text='Save and continue logging in'}
            </button>
            {else}
            <button class='btn btn-success' title="{gt text='Save'}">
                {gt text='Save'}
            </button>
            {/if}
            <a href="{route name='zikulausersmodule_user_index'}" title="{gt text='Cancel'}" class="btn btn-danger">{gt text='Cancel'}</a>
        </div>
    </div>
</form>