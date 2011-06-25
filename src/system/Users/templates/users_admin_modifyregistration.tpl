{strip}
{ajaxheader modname=$modinfo.name filename='Zikula.Users.NewUser.js' noscriptaculous=true effects=true}
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

<div id="{$formData->getFormId()}_errormsgs" class="z-errormsg{if empty($errorMessages)} z-hide{/if}">
    {if isset($errorMessages)}
    {foreach from=$errorMessages item='message' name='errorMessages'}
    {$message|safetext}
    {if !$smarty.foreach.errorMessages.last}<hr />{/if}
    {/foreach}
    {/if}
</div>

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{gt text='Edit registration of %s' tag1=$user_attributes.realname|default:$formData->getFieldData('uname')}</h3>
</div>

<p class="z-warningmsg">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

<form id="{$formData->getFormId()}" class="z-form" action="{modurl modname='Users' type='admin' func='modifyRegistration'}" method="post">
    <div>
        <input type="hidden" id="{$formData->getFormId()}_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="{$formData->getFormId()}_event_type" type="hidden" name="event_type" value="modify_registration" />
        <input id="{$formData->getFieldId('uid')}" type="hidden" name="uid" value="{$formData->getFieldData('uid')|safetext}" />
        <input id="{$formData->getFormId()}_restoreview" type="hidden" name="restoreview" value="{$restoreview|default:'view'}" />
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

        {notifyevent eventname='module.users.ui.form_edit.modify_registration' eventsubject=$formData->toUserArray() id=$formData->getFieldData('uid') assign="eventData"}
        {foreach item='eventDisplay' from=$eventData}
        {$eventDisplay}
        {/foreach}

        {notifydisplayhooks eventname='users.ui_hooks.user.form_edit' id=$formData->getFieldData('uid')}

        <fieldset>
            <legend>{gt text="Check your entries and submit your updates"}</legend>
            <p id="{$formData->getFormId()}_checkmessage" class="z-sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Save' to continue."}</p>
            <p id="{$formData->getFormId()}_validmessage" class="z-hide z-sub">{gt text="Your entries seem to be OK. Please click on 'Save' when you are ready to continue."}</p>
            <div class="z-formbuttons z-buttons">
                {img id=$formData->getFormId()|cat:'_ajax_indicator' class='z-hide z-center' modname='core' set='ajax' src='indicator_circle.gif' alt=''}
                {button id=$formData->getFormId()|cat:'_submitnewuser' type='submit' src='button_ok.png' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
                {button id=$formData->getFormId()|cat:'_checkuserajax' type='button' class='z-hide' src='quick_restart.png' set='icons/extrasmall' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
                <a href="{if $restoreview == 'view'}{modurl modname='Users' type='admin' func='viewRegistrations' restoreview=true}{else}{modurl modname='Users' type='admin' func='displayRegistration' uid=$formData->getFieldData('uid')}{/if}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}