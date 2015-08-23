{pageaddvar name='javascript' value='jquery'}
{strip}
    {gt text="User account" assign='legend_text'}
    {if isset($change_password) && $change_password eq 1 && $modvars.ZikulaUsersModule.use_password_strength_meter eq 1}
        {pageaddvar name='javascript' value='system/UsersModule/Resources/public/js/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                ( function($) {
                    $(document).ready(function() {
                        ZikulaUsersPassMeter.init('users_login_newpass', 'users_login_passmeter', {
                            username: 'users_login_login_id',
                            minLength: '{{$modvars.ZikulaUsersModule.minpass}}'
                        });
                    });
                })(jQuery);
            </script>
        {/pageaddvarblock}
    {/if}
{/strip}

{if isset($change_password) && $change_password eq 1}
<p class="alert alert-warning">{gt text="Important: For security reasons, you must change your password before you can log in. Thank you for your understanding."}</p>
{/if}

<div class="form-group">
    <label class="col-sm-3 control-label required" for="users_login_login_id">{strip}
        {if $authentication_method eq 'email'}
            {gt text='Email address'}
        {elseif $authentication_method eq 'uname'}
            {gt text='User name'}
        {elseif $authentication_method eq 'unameoremail'}
            {gt text='User name or e-mail address'}
        {/if}
    {/strip}</label>
    <div class="col-sm-9">
        <div class="input-group">
            {if $authentication_method eq 'email'}
                <span class="input-group-addon"><i class="fa fa-fw fa-envelope"></i></span>
            {elseif $authentication_method eq 'uname' || $authentication_method eq 'unameoremail'}
                <span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
            {/if}
            <input id="users_login_login_id" class="form-control"  type="text" name="authentication_info[login_id]" maxlength="64" value="{if isset($authentication_info.login_id)}{$authentication_info.login_id}{/if}" required="required" />
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label required" for="users_login_pass">{if isset($change_password) && $change_password}{gt text='Current password'}{else}{gt text='Password'}{/if}</label>
    <div class="col-sm-9">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-fw fa-asterisk"></i></span>
            <input id="users_login_pass" class="form-control" type="password" name="authentication_info[pass]" size="25" maxlength="60" required="required" />
            <span id="capsLok" class="input-group-addon hide"><i class="fa fa-fw fa-arrow-circle-up"> {gt text='Caps Lock is on!'}</i></span>
        </div>
    </div>
</div>

{if isset($change_password) && $change_password}
<div class="form-group">
    <label class="col-sm-3 control-label" for="users_newpass">{gt text="New password"}</label>
    <div class="col-sm-9">
        <input type="password" class="form-control" id="users_login_newpass" name="authentication_info[new_pass]" size="20" maxlength="20" value="" />
    </div>
</div>
{if $modvars.ZikulaUsersModule.use_password_strength_meter eq 1}
<div id="users_login_passmeter">
</div>
{/if}

<div class="form-group">
    <label class="col-sm-3 control-label" for="users_login_confirm_new_pass">{gt text="New password (repeat for verification)"}</label>
    <div class="col-sm-9">
        <input id="users_login_confirm_new_pass" class="form-control"  type="password" name="authentication_info[confirm_new_pass]" size="20" maxlength="20" />
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label" for="users_login_pass_reminder">{gt text="New password reminder"}</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="users_login_pass_reminder" name="authentication_info[pass_reminder]" value="" size="25" maxlength="128" />
        <div class="sub help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
        <div class="help-block alert alert-warning">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
    </div>
</div>
{/if}