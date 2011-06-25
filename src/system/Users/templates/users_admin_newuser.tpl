{strip}
    {gt text='Create new user' assign='templatetitle'}
    {ajaxheader modname=$modinfo.name filename='Zikula.Users.NewUser.js' noscriptaculous=true effects=true}
    {ajaxheader modname=$modinfo.name filename='Zikula.Users.Admin.NewUser.js' noscriptaculous=true effects=true}
    {if $modvars.Users.use_password_strength_meter == 1}
        {* TODO - Using ajaxheader here causes an error when the PassMeter is initialized. *}
        {pageaddvar name='javascript' value='prototype'}
        {pageaddvar name='javascript' value='system/Users/javascript/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                var passmeter = null;
                document.observe("dom:loaded", function() {
                    passmeter = new Zikula.Users.PassMeter('{{$formData->getFieldId('pass')}}', '{{$formData->getFormId()}}_passmeter',{
                        username:'{{$formData->getFieldId('uname')}}',
                        minLength: '{{$modvars.Users.minpass}}'
                    });
                });
            </script>
        {/pageaddvarblock}
    {/if}
    {pageaddvarblock}
        <script type="text/javascript">
            Zikula.Users.NewUser.setup = function() {
                Zikula.Users.NewUser.formId = '{{$formData->getFormId()}}';

                Zikula.Users.NewUser.fieldId = {
                    submit:         '{{$formData->getFormId()}}_submitnewuser',
                    checkUser:      '{{$formData->getFormId()}}_checkuserajax',
                    checkMessage:   '{{$formData->getFormId()}}_checkmessage',
                    validMessage:   '{{$formData->getFormId()}}_validmessage',

                    userName:       '{{$formData->getFieldId('uname')}}',
                    email:          '{{$formData->getFieldId('email')}}',
                };
            }

            Zikula.Users.Admin.NewUser.setup = function() {
                Zikula.Users.Admin.NewUser.fieldId = {
                    passwordIsSetWrap:  '{{$formData->getFormId()}}_password_is_set_wrap',
                    passwordNotSetWrap: '{{$formData->getFormId()}}_password_not_set_wrap',

                    setPass:            '{{$formData->getFieldId('setpass')}}',
                    setPassYes:         '{{$formData->getFieldId('setpass')}}_yes',
                    setPassNo:          '{{$formData->getFieldId('setpass')}}_no',
                    setPassWrap:        '{{$formData->getFieldId('setpass')}}_wrap',
                    passWrap:           '{{$formData->getFieldId('pass')}}_wrap',
                    email:              '{{$formData->getFieldId('email')}}',
                };
            }
        </script>
    {/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{gt text='Create new user'}</h3>
</div>

<div id="{$formData->getFormId()}_errormsgs" class="z-errormsg{if empty($errorMessages)} z-hide{/if}">
    {if isset($errorMessages)}
    {foreach from=$errorMessages item='message' name='errorMessages'}
    {$message|safetext}
    {if !$smarty.foreach.errorMessages.last}<hr />{/if}
    {/foreach}
    {/if}
</div>

<p class="z-warningmsg">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

<form id="{$formData->getFormId()}" class="z-form" action="{modurl modname='Users' type='admin' func='newUser'}" method="post">
    <div>
        <input type="hidden" id="{$formData->getFormId()}_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="{$formData->getFormId()}_event_type" type="hidden" name="event_type" value="new_user" />
        <fieldset>
            <legend>{gt text='Account information'}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='uname'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text='User name'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" size="30" maxlength="25" value="{$formData->getFieldData($fieldName)|safetext}" />
                <em class="z-formnote z-sub">{gt text='User names can contain letters, numbers, underscores, periods, or dashes.'}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='email'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text='E-mail address'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='emailagain'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text='Repeat e-mail address for verification'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='theme'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text='Theme'}</label>
                <select id="{$formData->getFieldId($fieldName)}" name="{$fieldName}">
                    <option value="">{gt text="Site's default theme"}</option>
                    {html_select_themes selected=$formData->getFieldData($fieldName) state=ThemeUtil::STATE_ACTIVE filter=ThemeUtil::FILTER_USER}
                </select>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text='Log-in information'}</legend>
            {assign var='fieldName' value='setpass'}
            <div id="{$formData->getFieldId($fieldName)}_wrap" class="z-formrow">
                <label>{gt text="Set the user's password now?"}</label>
                <div id="{$formData->getFieldId($fieldName)}">
                    <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            {assign var='fieldName' value='pass'}
            <div id="{$formData->getFieldId($fieldName)}_wrap">
                <div class="z-formrow">
                    <label for="{$formData->getFieldId($fieldName)}">{gt text='Password'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                    <input id="{$formData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="password" name="{$fieldName}" size="30" maxlength="20" />
                    <em class="z-sub z-formnote">{gt text='Notice: The minimum length for user passwords is %s characters.' tag1=$modvars.Users.minpass}</em>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
                <div id="{$formData->getFormId()}_passmeter">
                </div>
                <div class="z-formrow">
                    {assign var='fieldName' value='passagain'}
                    <label for="{$formData->getFieldId($fieldName)}">{gt text='Repeat password for verification'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                    <input id="{$formData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="password" name="{$fieldName}" size="30" maxlength="20" />
                    <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
                {assign var='fieldName' value='sendpass'}
                <div id="{$formData->getFieldId($fieldName)}_container" class="z-formrow">
                    <label>{gt text="Send password via e-mail?"}</label>
                    <div id="{$formData->getFieldId($fieldName)}">
                        <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                        <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    </div>
                    <p class="z-formnote z-warningmsg">{gt text="Sending a password via e-mail is considered unsafe. It is recommended that you provide the password to the user using a secure method of communication."}</p>
                    <p class="z-formnote z-informationmsg">{gt text="Even if you choose to not send the user's password via e-mail, other e-mail messages may be sent to the user as part of the registration process."}</p>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div id="{$formData->getFormId()}_password_not_set_wrap" class="z-formrow z-hide">
                {if $modvars.Users.reg_verifyemail == 'Users_Constant::VERIFY_NO'|constant}
                <p class="z-formnote z-warningmsg">{gt text="The user's e-mail address will be verified, even though e-mail address verification is turned off in 'Settings'. This is necessary because the user will create a password during the verification process."}</p>
                {else}
                <p class="z-formnote z-informationmsg">{gt text="The user's e-mail address will be verified. The user will create a password at that time."}</p>
                {/if}
            </div>
            <div id="{$formData->getFormId()}_password_is_set_wrap" class="z-formrow">
                {assign var='fieldName' value='usermustverify'}
                <label>{gt text="Verify user's e-mail address?"}</label>
                <div class="z-formlist">
                    <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes (recommended)"}</label>
                    <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Notice: This overrides the 'Verify e-mail address during registration' setting in 'Settings'."}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </fieldset>

        {notifyevent eventname='module.users.ui.form_edit.new_user' eventsubject=null id=null assign="eventData"}
        {foreach item='eventDisplay' from=$eventData}
            {$eventDisplay}
        {/foreach}
        
        {notifydisplayhooks eventname='users.ui_hooks.user.form_edit' id=null}

        <fieldset>
            <legend>{gt text="Check your entries and submit your registration"}</legend>
            <p id="{$formData->getFormId()}_checkmessage" class="z-sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Submit new user' to continue."}</p>
            <p id="{$formData->getFormId()}_validmessage" class="z-hide z-sub">{gt text="Your entries seem to be OK. Please click on 'Submit registration' when you are ready to continue."}</p>
            <div class="z-formbuttons z-buttons">
                {img id=$formData->getFormId()|cat:'_ajax_indicator' class='z-hide z-center' modname='core' set='ajax' src='indicator_circle.gif' alt=''}
                {button id=$formData->getFormId()|cat:'_submitnewuser' type='submit' src='button_ok.png' set='icons/extrasmall' __alt='Submit new user' __title='Submit new user' __text='Submit new user'}
                {button id=$formData->getFormId()|cat:'_checkuserajax' type='button' class='z-hide' src='quick_restart.png' set='icons/extrasmall' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
                <a href="{modurl modname='Users' type='admin' func='view'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}
{* Script blocks should remain at the end of the file so that it does not block progressive rendering of the page. *}
{if $modvars.Users.use_password_strength_meter == 1}
{/if}
