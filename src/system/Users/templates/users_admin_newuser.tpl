{ajaxheader modname='Users' filename='users_newuser.js' noscriptaculous=true effects=true}
{ajaxheader modname='Users' filename='users_admin_newuser.js' noscriptaculous=true effects=true}
{gt text='Create new user' assign='templatetitle'}

{include file='users_admin_menu.tpl'}
<a id="users_formtop"></a>
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=filenew.gif set=icons/large alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>
    <p class="z-warningmsg">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

    <div id="users_errormsgs_div" class="z-errormsg{if empty($errormsgs)} z-hide{/if}">
        <p>Please correct the following items:</p>
        <ul id="users_errormsgs">
            {foreach from=$errormsgs item='message'}
            <li>{$message}</li>
            {/foreach}
        </ul>
    </div>

    <form id="users_newuser" class="z-form" action="{modurl modname='Users' type='admin' func='registerNewUser'}" method="post">
        <div>
            <input type="hidden" id="users_authid" name="authid" value="{insert name='generateauthkey' module='Users'}" />
            <input type="hidden" id="users_checkmode" name="checkmode" value="new" />
            <input type="hidden" id="users_reginfo_agreetoterms" name="reginfo[agreetoterms]" value="{if !$usermustaccept}1{else}0{/if}" />
            <fieldset>
                <legend>{gt text='New user account'}</legend>
                <div class="z-formrow">
                    <label for="users_reginfo_uname">{gt text='User name'}<span class="z-mandatorysym">{gt text="*"}</span></label>
                    <input id="users_reginfo_uname"{if isset($errorflds.reginfo_uname)} class="errorrequired"{/if} type="text" name="reginfo[uname]" size="21" maxlength="25" value="{$reginfo.uname|default:''}" />
                    <em class="z-formnote z-sub">{gt text='User names can contain letters, numbers, underscores, and/or periods.'}</em>
                </div>
                <div class="z-formrow">
                    <label for="users_reginfo_email">{gt text='E-mail address'}<span class="z-mandatorysym">{gt text="*"}</span></label>
                    <input id="users_reginfo_email"{if isset($errorflds.reginfo_email) || isset($errorflds.emailagain)} class="errorrequired"{/if} type="text" name="reginfo[email]" size="21" maxlength="60" value="{$reginfo.email|default:''}" />
                </div>
                <div class="z-formrow">
                    <label for="users_emailagain">{gt text='E-mail address (repeat for verification)'}<span class="z-mandatorysym">{gt text="*"}</span></label>
                    <input id="users_emailagain"{if isset($errorflds.emailagain)} class="errorrequired"{/if} type="text" name="emailagain" size="21" maxlength="60" value="{$emailagain|default:''}" />
                </div>
                <div id="users_setpass_container" class="z-formrow{if !empty($reginfo.pass) || isset($errorflds.reginfo_pass) || isset($errorflds.passagain)} z-hide{/if}">
                    <label for="users_setpass">{gt text="Set the user's password now?"}</label>
                    <div id="users_setpass">
                        <input id="users_setpass_yes" type="radio" name="setpass" value="1" {if !empty($reginfo.pass) || isset($errorflds.reginfo_pass) || isset($errorflds.passagain)} checked="checked"{/if} />
                        <label for="users_setpass_yes">{gt text="Yes"}</label>
                        <input id="users_setpass_no" type="radio" name="setpass" value="0" {if empty($reginfo.pass) && !isset($errorflds.reginfo_pass) && !isset($errorflds.passagain)} checked="checked"{/if} />
                        <label for="users_setpass_no">{gt text="No"}</label>
                    </div>
                </div>
                <div id="users_setpass_yes_wrap">
                    <div class="z-formrow">
                        <label for="users_reginfo_pass">{gt text='Password'}<span class="z-mandatorysym">{gt text="*"}</span></label>
                        <input id="users_reginfo_pass"{if isset($errorflds.reginfo_pass) || isset($errorflds.passagain)} class="errorrequired"{/if} type="password" name="reginfo[pass]" size="21" maxlength="20" />
                        <em class="z-sub z-formnote">{gt text='Notice: The minimum length for user passwords is %s characters.' tag1=$modvars.Users.minpass}</em>
                    </div>
                    {if $modvars.Users.use_password_strength_meter eq 1}
                    {pageaddvar name='javascript' value='prototype'}
                    {pageaddvar name='javascript' value='system/Users/javascript/Zikula.Users.PassMeter.js'}

                    <script type="text/javascript">
                        var passmeter = new Zikula.Users.PassMeter('users_reginfo_pass',{
                            username:'users_reginfo_uname',
                            minLength: '{{$modvars.Users.minpass}}',
                            messages: {
                                username: '{{gt text="Password can not match the username, choose a different password."}}',
                                minLength: '{{gt text="The minimum length for user passwords is %s characters." tag1=$modvars.Users.minpass}}'
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
                    <div class="z-formrow">
                        <label for="users_passagain">{gt text='Password (repeat for verification)'}<span class="z-mandatorysym">{gt text="*"}</span></label>
                        <input id="users_passagain"{if isset($errorflds.passagain)} class="errorrequired"{/if} type="password" name="passagain" size="21" maxlength="20" />
                    </div>
                    <div id="users_sendpass_container" class="z-formrow">
                        <label for="users_sendpass">{gt text="Send password via e-mail?"}</label>
                        <div id="users_sendpass">
                            <input id="users_sendpass_yes" type="radio" name="sendpass" value="1" {if !empty($sendpass)} checked="checked"{/if} />
                            <label for="users_sendpass_yes">{gt text="Yes"}</label>
                            <input id="users_sendpass_no" type="radio" name="sendpass" value="0" {if empty($sendpass)} checked="checked"{/if} />
                            <label for="users_sendpass_no">{gt text="No"}</label>
                        </div>
                        <p class="z-formnote z-warningmsg">{gt text="Sending a password via e-mail is considered unsafe. It is recommended that you provide the password to the user using a secure method of communication."}</p>
                    </div>
                </div>
                <div id="users_setpass_no_wrap" class="z-formrow z-hide">
                    {if $modvars.Users.reg_verifyemail == 'UserUtil::VERIFY_NO'|constant}
                    <p class="z-formnote z-warningmsg">{gt text="The user's e-mail address will be verified, even though e-mail address verification is turned off in 'Settings'. This is necessary because the user will create a password during the verification process."}</p>
                    {else}
                    <p class="z-formnote z-informationmsg">{gt text="The user's e-mail address will be verified. The user will create a password at that time."}</p>
                    {/if}
                </div>
                <div id="users_usermustverify_wrap" class="z-formrow">
                    <label for="users_usermustverify">{gt text="User's e-mail address must be verified (recommended)"}</label>
                    <input id="users_usermustverify" type="checkbox" name="usermustverify"{if $usermustverify} checked="checked"{/if} />
                    <em class="z-sub z-formnote">{gt text="Notice: This overrides the 'Verify e-mail address during registration' setting in 'Settings'."}</em>
                </div>
            </fieldset>

            {if isset($showProps)}
            {modfunc modname=$profileModName type='form' func='edit' dynadata=$reginfo.dynadata}
            {/if}

            <fieldset>
                <legend>{gt text="Check your entries and submit your registration"}</legend>
                <p id="users_checkmessage" class="z-sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Submit new user' to continue."}</p>
                <p id="users_validmessage" class="z-hide z-sub">{gt text="Your entries seem to be OK. Please click on 'Submit registration' when you are ready to continue."}</p>
                <div class="z-formbuttons z-buttons">
                    {button id='submitnewuser' type='submit' src='button_ok.gif' set='icons/extrasmall' __alt='Submit new user' __title='Submit new user' __text='Submit new user'}
                    {button id='checkuserajax' type='button' class='z-hide' src='help.gif' set='icons/extrasmall' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
                    <a href="{modurl modname='Users' type='admin' func='view'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
                    {img id='ajax_indicator' style='display: none;' modname='core' set='icons/extrasmall' src='indicator_circle.gif' alt=''}
                </div>
            </fieldset>
        </div>
    </form>
</div>
