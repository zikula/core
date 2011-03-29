{strip}{gt text='New account registration' assign='templatetitle'}
{ajaxheader modname='Users' filename='Zikula.Users.NewUser.js'}
{if $modvars.Users.use_password_strength_meter}
    {* TODO - Using ajaxheader here causes an error when the PassMeter is initialized. *}
    {pageaddvar name='javascript' value='prototype'}
    {pageaddvar name='javascript' value='system/Users/javascript/Zikula.Users.PassMeter.js'}
{/if}
{/strip}

{include file="users_user_menu.tpl"}

<p id="users_formtop">
    {gt text='Registering for a user account is easy. Registration can give you access to content and to features of the site that are not available to anonymous guests.'}
    {gt text='During your visits, you are recommended to set your browser to accept cookies from this site. Various features of the site use cookies, and may not function properly or completely if cookies are disabled.'}
</p>

{if $modvars.Users.moderation && ($modvars.Users.reg_verifyemail != 'Users_Constant::VERIFY_NO'|constant)}
    {if $modvars.Users.moderation_order == 'Users_Constant::APPROVAL_BEFORE'|constant}
    <p class="z-informationmsg">{gt text="Before you will be able to log in, an administrator must approve your registration request and you must verify your e-mail address. You will receive an e-mail asking to verify your e-mail address after an administrator has approved your request."}</p>
    {else}
    <p class="z-informationmsg">{gt text="Before you will be able to log in, you must verify your e-mail address and an administrator must approve your registration request. You will receive an e-mail asking to verify your e-mail address after submitting the information below."}{if $modvars.Users.moderation_order == 'Users_Constant::APPROVAL_AFTER'|constant} {gt text="You must verify your e-mail address before an administrator will approve your registration request."}{/if}</p>
    {/if}
{elseif $modvars.Users.moderation}
    <p class="z-informationmsg">{gt text="Before you will be able to log in, an administrator must approve your registration request. You will receive an e-mail after an administrator has reviewed the information you submit below."}</p>
{elseif $modvars.Users.reg_verifyemail != 'Users_Constant::VERIFY_NO'|constant}
    <p class="z-informationmsg">{gt text="Before you will be able to log in, you must verify your e-mail address. You will receive an e-mail asking to verify your e-mail address after submitting the information below."}</p>
{/if}

<div id="{$formData->getFormId()}_errormsgs" class="z-errormsg{if !isset($errorMessages) || empty($errorMessages)} z-hide{/if}">
    {if isset($errorMessages)}
    {foreach from=$errorMessages item='message'}
        {$message|safetext}
        {if !$smarty.foreach.errorMessages.last}<hr />{/if}
    {/foreach}
    {/if}
</div>

<p class="z-warningmsg">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

<form id="{$formData->getFormId()}" class="z-form" action="{modurl modname='Users' type='user' func='register'}" method="post">
    <div>
        <input id="{$formData->getFormId()}_csrftoken" type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Choose a user name"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='uname'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text="User name"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" size="25" maxlength="25" value="{$formData->getFieldData($fieldName)|safetext}" />
                <em class="z-formnote z-sub">{gt text='User names can contain letters, numbers, underscores, periods and/or dashes.'}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Set a password and enter your e-mail address"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='pass'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text="Password"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" type="password"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} size="25" maxlength="60" />
                <em class="z-formnote z-sub">{gt text="The minimum length for user passwords is %s characters." tag1=$modvars.Users.minpass}</em>
                <div id="{$formData->getFormId()}_passmeter">
                </div>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='passagain'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text="Repeat your Password for verification"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="password" size="25" maxlength="60" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='passreminder'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text="Password reminder"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" size="25" maxlength="128" value="{$formData->getFieldData($fieldName)|safetext}" />
                <div class="z-sub z-formnote">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="z-formnote z-informationmsg">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='email'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text="E-mail address"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" size="25" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='emailagain'}
                <label for="{$formData->getFieldId($fieldName)}">{gt text="Repeat your E-mail address for verification"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" size="25" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </fieldset>

        {notifydisplayhooks eventname='users.hook.user.ui.edit' area='modulehook_area.users.registration' subject=null id=null}

        {if !empty($modvars.Users.reg_question)}
        <fieldset>
            <legend>{gt text="Answer the security question"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='antispamanswer'}
                <label for="{$formData->getFieldId($fieldName)}">{$modvars.Users.reg_question|safehtml}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" size="25" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <em class="z-formnote z-sub">{gt text="Asking this question helps us prevent automated scripts from accessing private areas of the site."}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="z-formnote z-errormsg{if !isset($errorFields.$fieldName)} z-hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </fieldset>
        {/if}

        <fieldset>
            <legend>{gt text="Check your entries and submit your registration"}</legend>
            <p id="{$formData->getFormId()}_checkmessage" class="z-sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Submit registration' to continue."}</p>
            <p id="{$formData->getFormId()}_validmessage" class="z-hide">{gt text="Your entries seem to be OK. Please click on 'Submit registration' when you are ready to continue."}</p>
            <div class="z-center z-buttons">
                {img id=$formData->getFormId()|cat:'_ajax_indicator' style='display: none;' modname=core set='ajax' src='indicator_circle.gif' alt=''}
                {button id=$formData->getFormId()|cat:'_submitnewuser' type='submit' src='button_ok.png' set='icons/extrasmall' __alt='Submit registration' __title='Submit registration' __text='Submit registration'}
                {button id=$formData->getFormId()|cat:'_checkuserajax' type='button' class='z-hide' src='help.png' set='icons/extrasmall' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
            </div>
        </fieldset>
    </div>
</form>
{if $modvars.Users.use_password_strength_meter}
<script type="text/javascript">
    var passmeter = new Zikula.Users.PassMeter('{{$formData->getFieldId('pass')}}', '{{$formData->getFormId()}}_passmeter',{
        username:'{{$formData->getFieldId('uname')}}',
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
    passmeter.start();
</script>
{/if}
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
</script>
