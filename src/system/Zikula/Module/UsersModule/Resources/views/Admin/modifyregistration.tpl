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

<div id="{$formData->getFormId()}_errormsgs" class="alert alert-danger{if empty($errorMessages)} hide{/if}">
    {if isset($errorMessages)}
    {foreach from=$errorMessages item='message' name='errorMessages'}
    {$message|safetext}
    {if !$smarty.foreach.errorMessages.last}<hr />{/if}
    {/foreach}
    {/if}
</div>

{adminheader}
<h3>
    <span class="fa fa-plus"></span>
    {gt text='Edit registration of %s' tag1=$user_attributes.realname|default:$formData->getFieldData('uname')}
</h3>

<p class="alert alert-warning">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

<form id="{$formData->getFormId()}" class="form-horizontal" role="form" action="{route name='zikulausersmodule_admin_modifyregistration'}" method="post">
    <div>
        <input type="hidden" id="{$formData->getFormId()}_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="{$formData->getFormId()}_event_type" type="hidden" name="event_type" value="modify_registration" />
        <input id="{$formData->getFieldId('uid')}" type="hidden" name="uid" value="{$formData->getFieldData('uid')|safetext}" />
        <input id="{$formData->getFormId()}_restoreview" type="hidden" name="restoreview" value="{$restoreview|default:'view'}" />
        <fieldset>
            <legend>{gt text='Account information'}</legend>
            <div class="form-group">
                {assign var='fieldName' value='uname'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='User name'}<span class="required"></span></label>
                {assign var='fieldName' value='uname'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)}form-error{/if}" type="text" name="{$fieldName}" size="30" maxlength="25" value="{$formData->getFieldData($fieldName)|safetext}" />
                    <em class="help-block">{gt text='User names can contain letters, numbers, underscores, periods, or dashes.'}</em>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='email'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='E-mail address'}<span class="required"></span></label>
                {assign var='fieldName' value='email'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} form-error{/if}" type="text" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='emailagain'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Repeat e-mail address for verification'}<span class="required"></span></label>
                {assign var='fieldName' value='emailagain'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)}form-error{/if}" type="text" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='theme'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Theme'}</label>
                {assign var='fieldName' value='theme'}
                <div class="col-lg-9">
                    <select id="{$formData->getFieldId($fieldName)}" class="form-control" name="{$fieldName}">
                        <option value="">{gt text="Site's default theme"}</option>
                        {html_select_themes selected=$formData->getFieldData($fieldName) state=ThemeUtil::STATE_ACTIVE filter=ThemeUtil::FILTER_USER}
                    </select>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
        </fieldset>

        {notifyevent eventname='module.users.ui.form_edit.modify_registration' eventsubject=$formData->toUserArray() id=$formData->getFieldData('uid') assign="eventData"}
        {foreach item='eventDisplay' from=$eventData}
        {$eventDisplay}
        {/foreach}

        {notifydisplayhooks eventname='users.ui_hooks.user.form_edit' id=$formData->getFieldData('uid')}

        <fieldset>
            <legend>{gt text="Check your entries and submit your updates"}</legend>
            <p id="{$formData->getFormId()}_checkmessage" class="sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Save' to continue."}</p>
            <p id="{$formData->getFormId()}_validmessage" class="hide sub">{gt text="Your entries seem to be OK. Please click on 'Save' when you are ready to continue."}</p>
            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-9">
                        <div id="{$formData->getFormId()|cat:'_ajax_indicator'}" class="btn btn-warning hide"><i class="fa fa-spinner fa-spin"></i>&nbsp;{gt text="Checking"}</div>
                        {button id=$formData->getFormId()|cat:'_submitnewuser' type='submit' class='btn btn-success' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
                        {button id=$formData->getFormId()|cat:'_checkuserajax' type='button' class='btn btn-warning' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
                        <a class="btn btn-default" href="{if $restoreview == 'view'}{route name='zikulausersmodule_admin_viewregistrations' restoreview=true}{else}{route name='zikulausersmodule_admin_displayregistration' uid=$formData->getFieldData('uid')}{/if}">{gt text='Cancel'}</a>
                </div>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}