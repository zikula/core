{gt text="User account" assign='legend_text'}
{if isset($change_password) && ($change_password == 1)}
<p class="z-warningmsg">{gt text="Important: For security reasons, you must change your password before you can log in. Thank you for your understanding."}</p>
{/if}

<div class="z-formrow">
    <label for="users_login_login_id">{strip}
        {if $authentication_method == 'email'}
            {gt text='E-mail address'}
        {else}
            {gt text='User name'}
        {/if}
    {/strip}</label>
    <input id="users_login_login_id" type="text" name="authentication_info[login_id]" maxlength="64" value="{if isset($authentication_info.login_id)}{$authentication_info.login_id}{/if}" />
</div>

<div class="z-formrow">
    <label for="users_login_pass">{if isset($change_password) && $change_password}{gt text='Current password'}{else}{gt text='Password'}{/if}</label>
    <input id="users_login_pass" type="password" name="authentication_info[pass]" maxlength="25" />
</div>

{if isset($change_password) && $change_password}
<div class="z-formrow">
    <label for="users_newpass">{gt text="New password"}</label>
    <input type="password" id="users_login_newpass" name="authentication_info[new_pass]" size="20" maxlength="20" value="" />
</div>
<div id="users_login_passmeter">
</div>
{if $modvars.Users.use_password_strength_meter eq 1}
    {pageaddvar name='javascript' value='prototype'}
    {pageaddvar name='javascript' value='system/Users/javascript/Zikula.Users.PassMeter.js'}

    <script type="text/javascript">
        var passmeter = new Zikula.Users.PassMeter('users_login_newpass', 'users_login_passmeter', {
            username:'users_login_login_id',
            minLength: '{{$modvars.Users.minpass}}'
        });
    </script>
{/if}

<div class="z-formrow">
    <label for="users_login_confirm_new_pass">{gt text="New password (repeat for verification)"}</label>
    <input id="users_login_confirm_new_pass" type="password" name="authentication_info[confirm_new_pass]" size="20" maxlength="20" />
</div>

<div class="z-formrow">
    <label for="users_login_pass_reminder">{gt text="New password reminder"}</label>
    <input type="text" id="users_login_pass_reminder" name="authentication_info[pass_reminder]" value="" size="25" maxlength="128" />
    <div class="z-sub z-formnote">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
    <div class="z-formnote z-warningmsg">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
</div>
{/if}