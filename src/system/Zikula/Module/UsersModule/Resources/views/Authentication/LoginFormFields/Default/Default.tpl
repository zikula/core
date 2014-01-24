{pageaddvar name='javascript' value='jQuery'}
{strip}
    {gt text="User account" assign='legend_text'}
    {if isset($change_password) && ($change_password == 1) && ($modvars.ZikulaUsersModule.use_password_strength_meter == 1)}
        {pageaddvar name='javascript' value='prototype'}
        {pageaddvar name='javascript' value='system/Zikula/Module/UsersModule/Resources/public/js/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                var passmeter = null;
                document.observe("dom:loaded", function() {
                    passmeter = new Zikula.Users.PassMeter('users_login_newpass', 'users_login_passmeter', {
                        username:'users_login_login_id',
                        minLength: '{{$modvars.ZikulaUsersModule.minpass}}'
                    });
                });
            </script>
        {/pageaddvarblock}
    {/if}
{/strip}

{if isset($change_password) && ($change_password == 1)}
<p class="alert alert-warning">{gt text="Important: For security reasons, you must change your password before you can log in. Thank you for your understanding."}</p>
{/if}

<div class="form-group">
    <label class="col-lg-3 control-label" for="users_login_login_id">{strip}
        {if $authentication_method == 'email'}
            {gt text='Email address'}
        {elseif $authentication_method == 'uname'}
            {gt text='User name'}
        {elseif $authentication_method == 'unameoremail'}
            {gt text='User name or e-mail address'}
        {/if}
    {/strip}</label>
    <div class="col-lg-9">
        <div class="input-group">
            {if $authentication_method == 'email'}
                <i class="fa fa-fw fa-envelope input-group-addon"></i>
            {elseif $authentication_method == 'uname' || $authentication_method == 'unameoremail'}
                <i class="fa fa-fw fa-user input-group-addon"></i>
            {/if}
            <input id="users_login_login_id" class="form-control"  type="text" name="authentication_info[login_id]" maxlength="64" value="{if isset($authentication_info.login_id)}{$authentication_info.login_id}{/if}" placeholder="{if $authentication_method == 'email'}{gt text='Email address'}{elseif $authentication_method == 'uname'}{gt text='User name'}{elseif $authentication_method == 'unameoremail'}{gt text='User name or e-mail address'}{/if}" />
        </div>
    </div>
</div>
{* @todo move this into a js file once these are refactored to javascript!*}
<script type="text/javascript">
    (function($) {
        $(function() {
            ZikulaUsersUtilCapsLock.capsLockChecker('#users_login_pass', '#capsLok');
        });
    })(jQuery)
</script>

<div class="form-group">
    <label class="col-lg-3 control-label" for="users_login_pass">{if isset($change_password) && $change_password}{gt text='Current password'}{else}{gt text='Password'}{/if}</label>
    <div class="col-lg-9">
        <div class="input-group">
            <i class="fa fa-fw fa-asterisk input-group-addon"></i>
            <input id="users_login_pass" class="form-control" type="password" name="authentication_info[pass]" maxlength="25" placeholder="{if isset($change_password) && $change_password}{gt text='Current password'}{else}{gt text='Password'}{/if}" />
            <i id="capsLok" class="fa fa-fw fa-arrow-circle-up input-group-addon hide"> {gt text='Caps Lock is on!'}</i>
        </div>
    </div>
</div>

{if isset($change_password) && $change_password}
<div class="form-group">
    <label class="col-lg-3 control-label" for="users_newpass">{gt text="New password"}</label>
    <div class="col-lg-9">
        <input type="password" class="form-control" id="users_login_newpass" name="authentication_info[new_pass]" size="20" maxlength="20" value="" />
    </div>
</div>
{if $modvars.ZikulaUsersModule.use_password_strength_meter eq 1}
<div id="users_login_passmeter">
</div>
{/if}

<div class="form-group">
    <label class="col-lg-3 control-label" for="users_login_confirm_new_pass">{gt text="New password (repeat for verification)"}</label>
    <div class="col-lg-9">
        <input id="users_login_confirm_new_pass" class="form-control"  type="password" name="authentication_info[confirm_new_pass]" size="20" maxlength="20" />
    </div>
</div>

<div class="form-group">
    <label class="col-lg-3 control-label" for="users_login_pass_reminder">{gt text="New password reminder"}</label>
    <div class="col-lg-9">
        <input type="text" class="form-control" id="users_login_pass_reminder" name="authentication_info[pass_reminder]" value="" size="25" maxlength="128" />
        <div class="sub help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
        <div class="help-block alert alert-warning">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
    </div>
</div>
{/if}