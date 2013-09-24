{strip}
    {gt text='New account registration' assign='templatetitle'}
    {ajaxheader modname='ZikulaUsersModule' filename='Zikula.Users.NewUser.js'}
    {if $modvars.ZikulaUsersModule.use_password_strength_meter && ($authentication_method.modname == 'ZikulaUsersModule')}
        {pageaddvar name='javascript' value='prototype'}
        {pageaddvar name='javascript' value='system/Zikula/Module/UsersModule/Resources/public/js/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                var passmeter = null;
                document.observe("dom:loaded", function() {
                    passmeter = new Zikula.Users.PassMeter('{{$formData->getFieldId('pass')}}', '{{$formData->getFormId()}}_passmeter',{
                        username:'{{$formData->getFieldId('uname')}}',
                        minLength: '{{$modvars.ZikulaUsersModule.minpass}}'
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
        </script>
    {/pageaddvarblock}
{/strip}

{include file="User/menu.tpl"}

<p id="users_formtop">
    {gt text='Registering for a user account is easy. Registration can give you access to content and to features of this site that are not available to unregistered guests.'}
    {gt text='During your visits, we recommended that you set your browser to accept cookies from this site. Various features of the site use cookies, and may not function properly (or may not function at all) if cookies are disabled.'}
</p>

{if $modvars.ZikulaUsersModule.moderation && ($modvars.ZikulaUsersModule.reg_verifyemail != 'Zikula\Module\UsersModule\Constant::VERIFY_NO'|const)}
    {if $modvars.ZikulaUsersModule.moderation_order == 'Zikula\Module\UsersModule\Constant::APPROVAL_BEFORE'|const}
    <p class="alert alert-info">{gt text="Before you will be able to log in, an administrator must approve your registration request and you must verify your e-mail address. You will receive an e-mail asking to verify your e-mail address after an administrator has approved your request."}</p>
    {else}
    <p class="alert alert-info">{gt text="Before you will be able to log in, you must verify your e-mail address and an administrator must approve your registration request. You will receive an e-mail asking to verify your e-mail address after submitting the information below."}{if $modvars.ZikulaUsersModule.moderation_order == 'Zikula\Module\UsersModule\Constant::APPROVAL_AFTER'|const} {gt text="You must verify your e-mail address before an administrator will approve your registration request."}{/if}</p>
    {/if}
{elseif $modvars.ZikulaUsersModule.moderation}
    <p class="alert alert-info">{gt text="Before you will be able to log in, an administrator must approve your registration request. You will receive an e-mail after an administrator has reviewed the information you submit below."}</p>
{elseif $modvars.ZikulaUsersModule.reg_verifyemail != 'Zikula\Module\UsersModule\Constant::VERIFY_NO'|const}
    <p class="alert alert-info">{gt text="Before you will be able to log in, you must verify your e-mail address. You will receive an e-mail asking to verify your e-mail address after submitting the information below."}</p>
{/if}

<div id="{$formData->getFormId()}_errormsgs" class="alert alert-danger{if !isset($errorMessages) || empty($errorMessages)} hide{/if}">
    {if isset($errorMessages)}
    {foreach from=$errorMessages item='message'}
        {$message|safetext}
        {if !$smarty.foreach.errorMessages.last}<hr />{/if}
    {/foreach}
    {/if}
</div>

<p class="alert alert-warning">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

<form id="{$formData->getFormId()}" class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='user' func='register'}" method="post">
    <fieldset>
        <legend>{gt text="Choose a user name"}</legend>
        <input id="{$formData->getFormId()}_csrftoken" type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <input id="{$formData->getFormId()}_event_type" type="hidden" name="event_type" value="new_registration" />
    <input id="{$formData->getFormId()}_registration_info" type="hidden" name="registration_info" value="1" />
    <input id="{$formData->getFormId()}_authentication_method" type="hidden" name="authentication_method_ser" value="{$authentication_method|@serialize|safetext}" />
    <input id="{$formData->getFormId()}_authentication_info" type="hidden" name="authentication_info_ser" value="{$authentication_info|@serialize|safetext}" />
{capture name='uname'}
        <div class="form-group {if isset ($fieldName) && isset($errorFields.$fieldName)} has-error{/if}">
            {assign var='fieldName' value='uname'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text="User name"}<span class="required"></span></label>
            {assign var='fieldName' value='uname'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" class="form-control" type="text" size="25" maxlength="25" value="{$formData->getFieldData($fieldName)|safetext}" />
                <em class="help-block sub">{gt text='User names can contain letters, numbers, underscores, periods and/or dashes.'}</em>

                {if ($authentication_method.modname != 'ZikulaUsersModule') || (($authentication_method.modname == 'ZikulaUsersModule') && ($modvars.ZikulaUsersModule.loginviaoption == 'Zikula\Module\UsersModule\Constant::LOGIN_METHOD_EMAIL'|const))}
                <em class="help-block sub">{gt text='Your user name is used to identify you to other users on the site. You still need to set one up, even though you will not be using it to log in.'}</em>
                {/if}
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
    </fieldset>
{/capture}
{capture name='pass'}
    {if $authentication_method.modname == 'ZikulaUsersModule'}
    <fieldset>
        <legend>{gt text="Set a password"}</legend>
        <div class="form-group {if isset ($fieldName) && isset($errorFields.$fieldName)} has-error{/if}">
            {assign var='fieldName' value='pass'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text="Password"}<span class="required"></span></label>
            {assign var='fieldName' value='pass'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" type="password" class="form-control" size="25" maxlength="60" />
                <em class="help-block sub">{gt text="The minimum length for user passwords is %s characters." tag1=$modvars.ZikulaUsersModule.minpass}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                <div id="{$formData->getFormId()}_passmeter"></div>
            </div>
        </div>
        <div class="form-group {if isset ($fieldName) && isset($errorFields.$fieldName)} has-error{/if}">
            {assign var='fieldName' value='passagain'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text="Repeat your Password for verification"}<span class="required"></span></label>
            {assign var='fieldName' value='passagain'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" class="form-control" type="password" size="25" maxlength="60" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        {assign var='modvar_password_reminder_mandatory' value='Zikula\Module\UsersModule\Constant::MODVAR_PASSWORD_REMINDER_MANDATORY'|const}
        <div class="form-group{if isset ($fieldName) && isset($errorFields.$fieldName)} has-error{/if}">
            {assign var='fieldName' value='passreminder'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text="Password reminder"}{if $modvars.ZikulaUsersModule.$modvar_password_reminder_mandatory}<span class="required"></span>{/if}</label>
            {assign var='fieldName' value='passreminder'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" class="form-control" type="text" size="25" maxlength="128" value="{$formData->getFieldData($fieldName)|safetext}" />
                <div class="sub help-block">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="help-block alert alert-info">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
    </fieldset>
    {else}
        <input id="{$formData->getFieldId('pass')}" name="pass" type="hidden" value="{'Zikula\Module\UsersModule\Constant::PWD_NO_USERS_AUTHENTICATION'|const}" />
        <input id="{$formData->getFieldId('passagain')}" name="passagain" type="hidden" value="{'Zikula\Module\UsersModule\Constant::PWD_NO_USERS_AUTHENTICATION'|const}" />
        <input id="{$formData->getFieldId('passreminder')}" name="passreminder" type="hidden" value="{$formData->getFieldData('passreminder')|safetext}" />
    {/if}
{/capture}
{capture name='email'}
    <fieldset>
        <legend>{gt text="Enter your e-mail address"}</legend>
        <div class="form-group{if isset ($fieldName) && isset($errorFields.$fieldName)} has-error{/if}">
            {assign var='fieldName' value='email'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text="E-mail address"}<span class="required"></span></label>
            {assign var='fieldName' value='email'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" class="form-control" type="text" size="25" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />

                {if (($authentication_method.modname == 'ZikulaUsersModule') && ($modvars.ZikulaUsersModule.loginviaoption == 'Zikula\Module\UsersModule\Constant::LOGIN_METHOD_EMAIL'|const))}
                <em class="help-block sub">{gt text='You will use your e-mail address to identify yourself when you log in.'}</em>
                {/if}
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        <div class="form-group{if isset ($fieldName) &&  isset($errorFields.$fieldName)} has-error{/if}">
            {assign var='fieldName' value='emailagain'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text="Repeat your E-mail address for verification"}<span class="required"></span></label>
            {assign var='fieldName' value='emailagain'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}" class="form-control" type="text" size="25" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
    </fieldset>
{/capture}

    {* Order the fieldsets based on whether e-mail is the exclusive log-in id or not. *}
    {if (($authentication_method.modname == 'ZikulaUsersModule') && ($modvars.ZikulaUsersModule.loginviaoption == 'Zikula\Module\UsersModule\Constant::LOGIN_METHOD_EMAIL'|const))}
        {$smarty.capture.email}
        {$smarty.capture.pass}
        {$smarty.capture.uname}
    {else}
        {$smarty.capture.uname}
        {$smarty.capture.pass}
        {$smarty.capture.email}
    {/if}

    {notifyevent eventname='module.users.ui.form_edit.new_registration' eventsubject=null id=null assign='eventData'}
    {foreach item='eventDisplay' from=$eventData}
        {$eventDisplay}
    {/foreach}

    <fieldset>
        <legend>{gt text='Further information'}</legend>
        {notifydisplayhooks eventname='users.ui_hooks.registration.form_edit' id=null assign='hooks'}
        {if is_array($hooks) && count($hooks)}
            {foreach key='providerArea' item='hook' from=$hooks}
                {$hook}
                <div class="clearfix"></div>
            {/foreach}
        {/if}
    </fieldset>

    {if !empty($modvars.ZikulaUsersModule.reg_question)}
    <fieldset>
        <legend>{gt text="Answer the security question"}</legend>
        <div class="form-group">
            {assign var='fieldName' value='antispamanswer'}
            <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{$modvars.ZikulaUsersModule.reg_question|safehtml}<span class="required"></span></label>
            {assign var='fieldName' value='antispamanswer'}
            <div class="col-lg-9">
                <input id="{$formData->getFieldId($fieldName)}" name="{$fieldName}"{if isset($errorFields.$fieldName)} class="has-error"{/if} type="text" size="25" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                <em class="help-block sub">{gt text="Asking this question helps us prevent automated scripts from accessing private areas of the site."}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
    </fieldset>
    {/if}

    <fieldset>
        <legend>{gt text="Check your entries and submit your registration"}</legend>
        <p id="{$formData->getFormId()}_checkmessage" class="sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Submit registration' to continue."}</p>
        <p id="{$formData->getFormId()}_validmessage" class="hide">{gt text="Your entries seem to be OK. Please click on 'Submit registration' when you are ready to continue."}</p>
        <div class="text-center z-buttons">
            {img id=$formData->getFormId()|cat:'_ajax_indicator' class='hide' modname=core set='ajax' src='indicator_circle.gif' alt=''}
            <button id="{$formData->getFormId()|cat:'_submitnewuser'}" type="submit" title="{gt text='Submit registration'}">
                {gt text='Submit registration'}
            <button id="{$formData->getFormId()|cat:'_checkuserajax'}" type="button" class="hide" title="{gt text='Check your entries'}">
                {gt text='Check your entries'}
            </button>
        </div>
    </fieldset>
</form>
