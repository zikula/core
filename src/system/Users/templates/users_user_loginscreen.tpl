{ajaxheader modname='Users' filename='users.js'}
{gt text='User log-in' assign='templatetitle'}
{modulelinks modname='Users' type='user'}
{include file='users_user_menu.tpl'}

<form id="users_loginscreen_form" class="z-form" action="{modurl modname="Users" type="user" func="login"}" method="post">
    <div>
        <input id="users_authmodule" type="hidden" name="authmodule" value="{$authmodule}" />
        <input id="users_url" type="hidden" name="url" value="{$returnurl}" />
        <input id="users_authid" type="hidden" name="authid" value="{insert name="generateauthkey" module="Users"}" />
        <fieldset>
            <legend>{gt text="User account"}</legend>

            {if ($changepassword == 1) && ($authmodule == 'Users')}
            <p class="z-warningmsg">{gt text="Important: For security reasons, you must change your password before you can log in. Thank you for your understanding."}</p>
            {/if}

            {* In the future, somewhere around here we would choose an authmodule from $authmodules, storing it in $authmodule. *}
            {* The prompts for authinfo below would be appropriate for the authmodule, and the form would adjust if a different authmodule was selected. *}

            <div class="z-formrow">
                <label for="users_authinfo_loginid">{if $loginviaoption eq 1}{gt text="E-mail address"}{else}{gt text="User name"}{/if}</label>
                <input id="users_authinfo_loginid" type="text" name="authinfo[loginid]" size="20" maxlength="64" />
            </div>

            <div class="z-formrow">
                <label for="users_authinfo_pass">{$passwordtext}</label>
                <input id="users_authinfo_pass" type="password" name="authinfo[pass]" size="20" maxlength="25" />
            </div>

            {if ($changepassword == 1) && ($authmodule == 'Users')}
            <div class="z-formrow">
                <label for="users_newpass">{gt text="New password"}</label>
                <input type="password" id="users_newpass" name="newpass" size="20" maxlength="20" value="" />
                {if $use_password_strength_meter eq 1}
                    {pageaddvar name='javascript' value='prototype'}
                    {pageaddvar name='javascript' value='system/Users/javascript/Zikula.Users.PassMeter.js'}

                    <script type="text/javascript">
                        var passmeter = new Zikula.Users.PassMeter('users_newpass',{
                            username:'users_authinfo_loginid',
                            minLength: '{{$minpass}}',
                            messages: {
                                username: '{{gt text="Password can not match the username, choose a different password."}}',
                                minLength: '{{gt text="The minimum length for user passwords is %s characters." tag1=$minpass}}'
                            },
                            verdicts: [
                                '{{gt text="Weak"}}',
                                '{{gt text="Normal"}}',
                                '{{gt text="Strong"}}',
                                '{{gt text="Very Strong"}}'
                            ]
                        });
                    </script>
                {/if}
            </div>

            <div class="z-formrow">
                <label for="users_confirmnewpass">{gt text="New password (repeat for verification)"}</label>
                <input id="users_confirmnewpass" type="password" name="confirmnewpass" size="20" maxlength="20" />
            </div>

            <div class="z-formrow">
                <label for="passreminder">{gt text="New password reminder"}</label>
                <input type="text" id="passreminder" name="passreminder" value="" size="25" maxlength="128" />
                <div class="z-sub z-formnote">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="z-formnote z-warningmsg">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
            </div>
            {/if}

            {if $seclevel != "High"}
            <div class="z-formrow">
                <label for="users_rememberme">{gt text="Remember me"}</label>
                <input id="users_rememberme" type="checkbox" name="rememberme" />
            </div>
            {/if}
        </fieldset>

        {if ($confirmtou == 1) && ($tou_active || $pp_active) && ($authmodule == 'Users')}
        {gt text="terms of use" assign=touString}
        {gt text="privacy policy" assign=ppString}
        {gt text="Terms of Use" assign=touTitle}
        {gt text="Privacy Policy" assign=ppTitle}
        {modurl fqurl=true modname="Legal" type="user" func="main" assign=touUrl}
        {modurl fqurl=true modname="Legal" type="user" func="privacy" assign=ppUrl}
        {gt text='<a href="%1$s" onclick="window.open(\'%1$s\');return false;">\'Terms of use\'</a>' tag1=$touUrl|safetext assign=touURLString}
        {gt text='<a href="%1$s" onclick="window.open(\'%1$s\');return false;">\'Privacy policy\'</a>' tag1=$ppUrl|safetext assign=ppURLString}
        {if $tou_active && $pp_active}
        {gt text='%1$s and %2$s' tag1=$touURLString tag2=$ppURLString assign=touppURLString}
        {gt text='%1$s and %2$s' tag1=$touString tag2=$ppString assign=touppTextString}
        {gt text='%1$s and %2$s' tag1=$touTitle tag2=$ppTitle assign=touppTitle}
        {elseif  $tou_active}
        {assign var='touppURLString' value=$touURLString}
        {assign var='touppTextString' value=$touString}
        {assign var='touppTitle' value=$touTitle}
        {elseif $pp_active}
        {assign var='touppURLString' value=$ppURLString}
        {assign var='touppTextString' value=$ppString}
        {assign var='touppTitle' value=$ppTitle}
        {/if}

        <fieldset>
            <legend>{$touppTitle}</legend>
            <p class="z-warningmsg">{gt text='Notice: Please be informed that the site\'s %1$s have changed. You are asked to read the new %2$s, and to state your acceptance of them by activating the following checkbox. This is required before you can log in. Thank you for your understanding.' tag1=$touppURLString tag2=$touppTextString}</p>
            <div class="z-formrow">
                <label for="users_touaccepted">&nbsp;{gt text="I accept the %s" tag1=$touppURLString}</label>
                <input id="users_touaccepted" type="checkbox" name="touaccepted" value="1" />
            </div>
        </fieldset>
        {/if}

        <div class="z-formbuttons z-buttons">
            {button src=button_ok.gif set=icons/extrasmall __alt="Log in" __title="Log in" __text="Log in"}
        </div>
    </div>
</form>
