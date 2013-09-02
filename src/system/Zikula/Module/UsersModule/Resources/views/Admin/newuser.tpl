{strip}
    {gt text='Create new user' assign='templatetitle'}
    {ajaxheader modname=$modinfo.name filename='Zikula.Users.NewUser.js' noscriptaculous=true effects=true}
    {ajaxheader modname=$modinfo.name filename='Zikula.Users.Admin.NewUser.js' noscriptaculous=true effects=true}
    {if $modvars.ZikulaUsersModule.use_password_strength_meter == 1}
        {* TODO - Using ajaxheader here causes an error when the PassMeter is initialized. *}
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

<div id="{$formData->getFormId()}_errormsgs" class="alert alert-danger{if empty($errorMessages)} hide{/if}">
    {if isset($errorMessages)}
    {foreach from=$errorMessages item='message' name='errorMessages'}
    {$message|safetext}
    {if !$smarty.foreach.errorMessages.last}<hr />{/if}
    {/foreach}
    {/if}
</div>

<p class="alert alert-warning">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

<form id="{$formData->getFormId()}" class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='admin' func='newUser'}" method="post">
    <div>
        <input type="hidden" id="{$formData->getFormId()}_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="{$formData->getFormId()}_event_type" type="hidden" name="event_type" value="new_user" />
        <fieldset>
            <legend>{gt text='Account information'}</legend>
            <div class="form-group">
                {assign var='fieldName' value='uname'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='User name'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                {assign var='fieldName' value='uname'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} z-form-error{/if}" type="text" name="{$fieldName}" size="30" maxlength="25" value="{$formData->getFieldData($fieldName)|safetext}" />
                    <em class="help-block z-sub">{gt text='User names can contain letters, numbers, underscores, periods, or dashes.'}</em>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='email'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='E-mail address'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                {assign var='fieldName' value='email'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} z-form-error"{/if}" type="text" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='emailagain'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Repeat e-mail address for verification'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                {assign var='fieldName' value='emailagain'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} z-form-error{/if}" type="text" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" />
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
        <fieldset>
            <legend>{gt text='Log-in information'}</legend>
            {assign var='fieldName' value='setpass'}
            <div id="{$formData->getFieldId($fieldName)}_wrap" class="form-group">
                <label class="col-lg-3 control-label">{gt text="Set the user's password now?"}</label>
                <div id="{$formData->getFieldId($fieldName)}" class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            {assign var='fieldName' value='pass'}
            <div id="{$formData->getFieldId($fieldName)}_wrap" class="form-group">
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Password'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} z-form-error{/if}" type="password" name="{$fieldName}" size="30" maxlength="20" />
                    <em class="z-sub help-block">{gt text='Notice: The minimum length for user passwords is %s characters.' tag1=$modvars.ZikulaUsersModule.minpass}</em>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
                <div id="{$formData->getFormId()}_passmeter"></div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='passagain'}
                <label class="col-lg-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Repeat password for verification'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                {assign var='fieldName' value='passagain'}
                <div class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} z-form-error{/if}" type="password" name="{$fieldName}" size="30" maxlength="20" />
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            {assign var='fieldName' value='sendpass'}
            <div id="{$formData->getFieldId($fieldName)}_container" class="form-group">
                <label class="col-lg-3 control-label">{gt text="Send password via e-mail?"}</label>
                <div id="{$formData->getFieldId($fieldName)}" class="col-lg-9">
                    <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    <p class="help-block alert alert-warning">{gt text="Sending a password via e-mail is considered unsafe. It is recommended that you provide the password to the user using a secure method of communication."}</p>
                    <p class="help-block alert alert-info">{gt text="Even if you choose to not send the user's password via e-mail, other e-mail messages may be sent to the user as part of the registration process."}</p>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
                </div>
            </div>
            <div id="{$formData->getFormId()}_password_not_set_wrap" class="hide">
                

                {if $modvars.ZikulaUsersModule.reg_verifyemail == 'Zikula\Module\UsersModule\Constant::VERIFY_NO'|const}
                <p class="help-block alert alert-warning">{gt text="The user's e-mail address will be verified, even though e-mail address verification is turned off in 'Settings'. This is necessary because the user will create a password during the verification process."}</p>
                {else}
                <p class="help-block alert alert-info">{gt text="The user's e-mail address will be verified. The user will create a password at that time."}</p>
                {/if}
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Send welcome message to user?"}</label>
                <div class="col-lg-9">
                <div class="z-formlist">
                    <input id="usernotification_yes" type="radio" name="usernotification" value="1" />
                    <label for="usernotification_yes">{gt text="Yes"}</label>
                    <input id="usernotification_no" type="radio" name="usernotification" value="0" checked="checked" />
                    <label for="usernotification_no">{gt text="No"}</label>
                </div>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Send info message to adminstrators?"}</label>
                <div class="col-lg-9">
                <div class="z-formlist">
                    <input id="adminnotification_yes" type="radio" name="adminnotification" value="1" />
                    <label for="adminnotification_yes">{gt text="Yes"}</label>
                    <input id="adminnotification_no" type="radio" name="adminnotification" value="0" checked="checked" />
                    <label for="adminnotification_no">{gt text="No"}</label>
                </div>
            </div>
            <div id="{$formData->getFormId()}_password_is_set_wrap" class="form-group">
                {assign var='fieldName' value='usermustverify'}
                <label>{gt text="Verify user's e-mail address?"}</label>
                <div class="z-formlist">
                    <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes (recommended)"}</label>
                    <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                <em class="z-sub help-block">{gt text="Notice: This overrides the 'Verify e-mail address during registration' setting in 'Settings'."}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
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
            <p id="{$formData->getFormId()}_validmessage" class="hide z-sub">{gt text="Your entries seem to be OK. Please click on 'Submit registration' when you are ready to continue."}</p>
            <div class="z-formbuttons z-buttons">
                {img id=$formData->getFormId()|cat:'_ajax_indicator' class='hide z-center' modname='core' set='ajax' src='indicator_circle.gif' alt=''}
                {button id=$formData->getFormId()|cat:'_submitnewuser' type='submit' src='button_ok.png' set='icons/extrasmall' __alt='Submit new user' __title='Submit new user' __text='Submit new user'}
                {button id=$formData->getFormId()|cat:'_checkuserajax' type='button' class='hide' src='quick_restart.png' set='icons/extrasmall' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
                <a href="{modurl modname='ZikulaUsersModule' type='admin' func='view'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}
{* Script blocks should remain at the end of the file so that it does not block progressive rendering of the page. *}
{if $modvars.ZikulaUsersModule.use_password_strength_meter == 1}
{/if}
