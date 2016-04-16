{strip}
{ajaxheader modname='ZikulaUsersModule' filename='Zikula.Users.Admin.Config.js' noscriptaculous=true effects=true}
{pageaddvarblock}
<script type="text/javascript">
    ZikulaUsersAdminConfig.setup = function() {
        ZikulaUsersAdminConfig.formId = '{{$configData->getFormId()}}';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED'|const}}
        ZikulaUsersAdminConfig.registrationModeratedId = '{{$configData->getFieldId($fieldName)}}';
        ZikulaUsersAdminConfig.registrationModeratedYesId = '{{$configData->getFieldId($fieldName)}}' + '_yes';
        ZikulaUsersAdminConfig.registrationModeratedNoId = '{{$configData->getFieldId($fieldName)}}' + '_no';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_AUTO_LOGIN'|const}}
        ZikulaUsersAdminConfig.registrationAutoLoginWrapId = '{{$configData->getFieldId($fieldName)}}' + '_wrap';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE'|const}}
        ZikulaUsersAdminConfig.registrationApprovalOrderWrapId = '{{$configData->getFieldId($fieldName)}}' + '_wrap';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_VERIFICATION_MODE'|const}}
        ZikulaUsersAdminConfig.registrationVerificationModeId = '{{$configData->getFieldId($fieldName)}}';
        ZikulaUsersAdminConfig.registrationVerificationModeUserPwdId = '{{$configData->getFieldId($fieldName)}}' + '_' + '{{'Zikula\UsersModule\Constant::VERIFY_USERPWD'|const}}';
        ZikulaUsersAdminConfig.registrationVerificationModeNoneId = '{{$configData->getFieldId($fieldName)}}' + '_' + '{{'Zikula\UsersModule\Constant::VERIFY_NO'|const}}';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION'|const}}
        ZikulaUsersAdminConfig.registrationAntispamQuestionId = '{{$configData->getFieldId($fieldName)}}';
        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER'|const}}
        ZikulaUsersAdminConfig.registrationAntispamAnswerMandatoryId = '{{$configData->getFieldId($fieldName)}}' + '_mandatory';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_LOGIN_METHOD'|const}}
        ZikulaUsersAdminConfig.loginMethodId = '{{$configData->getFieldId($fieldName)}}';
        ZikulaUsersAdminConfig.loginMethodUserNameId = '{{$configData->getFieldId($fieldName)}}' + '_username';
        ZikulaUsersAdminConfig.loginMethodEmailId = '{{$configData->getFieldId($fieldName)}}' + '_email';
        ZikulaUsersAdminConfig.loginMethodEitherId = '{{$configData->getFieldId($fieldName)}}' + '_either';

        {{assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REQUIRE_UNIQUE_EMAIL'|const}}
        ZikulaUsersAdminConfig.requireUniqueEmailYesId = '{{$configData->getFieldId($fieldName)}}' + '_yes';
        ZikulaUsersAdminConfig.requireUniqueEmailNoId = '{{$configData->getFieldId($fieldName)}}' + '_no';
    }
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
</h3>

<form class="form-horizontal" role="form" id="{$configData->getFormId()}" action="{route name='zikulausersmodule_admin_config'}" method="post">
    <div>
        <input id="{$configData->getFormId()}_csrftoken" name="csrftoken" type="hidden" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_ANONYMOUS_DISPLAY_NAME'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Name displayed for anonymous user'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="20" maxlength="20" />
                    <em class="help-block sub">{gt text='Anonymous users are visitors to your site who have not logged in.'}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_ITEMS_PER_PAGE'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Number of items displayed per page'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" size="3" value="{$configData->getFieldData($fieldName)|safetext}" />
                    <em class="help-block sub">{gt text="When lists are displayed (for example, lists of users, lists of registrations) this option controls how many items are displayed at one time."}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_AVATAR_IMAGE_PATH'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="Path to user's avatar images"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_GRAVATARS_ENABLED'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Allow globally recognized avatars'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_GRAVATAR_IMAGE'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Default gravatar image'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Account page settings"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS'|const}
                <label class="col-sm-3 control-label">{gt text="Display graphics on user's account page"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)}checked="checked" {/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)}checked="checked" {/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Path to account page images'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Number of links per page'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" size="3" value="{$configData->getFieldData($fieldName)|safetext}" />
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Number of links per row'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" size="3" value="{$configData->getFieldData($fieldName)|safetext}" />
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_MANAGE_EMAIL_ADDRESS'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Users module handles e-mail address maintenance'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User credential settings"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_LOGIN_METHOD'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Credential required for user log-in'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_username" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::LOGIN_METHOD_UNAME'|const}" {if ($configData->getFieldData($fieldName) < constant('Zikula\UsersModule\Constant::LOGIN_METHOD_EMAIL')) || ($configData->getFieldData($fieldName) > constant('Zikula\UsersModule\Constant::LOGIN_METHOD_ANY'))}checked="checked" {/if}/>
                        <label for="{$configData->getFieldId($fieldName)}_username">{gt text="User name"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_email" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::LOGIN_METHOD_EMAIL'|const}" {if $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::LOGIN_METHOD_EMAIL')}checked="checked" {/if}/>
                        <label for="{$configData->getFieldId($fieldName)}_email">{gt text="E-mail address"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_either" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::LOGIN_METHOD_ANY'|const}" {if $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::LOGIN_METHOD_ANY')}checked="checked" {/if}/>
                        <label for="{$configData->getFieldId($fieldName)}_either">{gt text="Either user name or e-mail address"}</label>
                        <div class="help-block alert alert-warning">{gt text="Notice: If the 'Credential required for user log-in' is set to 'E-mail address' or to 'Either user name or e-mail address', then the 'New e-mail addresses must be unique' option below must be set to 'Yes'."}</div>
                        <div class="help-block alert alert-warning">{gt text="Notice: If the 'New e-mail addresses must be unique' option was set to 'no' at some point, then user accounts with duplicate e-mail addresses might exist in the system. They will experience difficulties logging in with their e-mail address."}</div>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REQUIRE_UNIQUE_EMAIL'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='New e-mail addresses must be unique'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    </div>
                    <em class="help-block sub">{gt text="If set to yes, then e-mail addresses entered for new registrations and for e-mail address change requests cannot already be in use by another user account or registration."}</em>
                    <div class="help-block alert alert-warning">{gt text="Notice: If this option was set to 'no' at some point, then user accounts or registrations with duplicate e-mail addresses might exist in the system. Setting this option to 'yes' will not affect those accounts or registrations."}</div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_PASSWORD_MINIMUM_LENGTH'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="Minimum length for user passwords"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="2" maxlength="2" />
                    <em class="help-block sub">{gt text="This affects both passwords created during registration, as well as passwords modified by users or administrators."} {gt text="Enter an integer greater than zero."}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_HASH_METHOD'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="Password hashing method"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <select class="form-control" id="{$configData->getFieldId($fieldName)}" name="{$fieldName}">
                        <option value="sha1" {if $configData->getFieldData($fieldName) == 'sha1'} selected="selected"{/if}>SHA1</option>
                        <option value="sha256" {if $configData->getFieldData($fieldName) == 'sha256'} selected="selected"{/if}>SHA256</option>
                    </select>
                    <em class="help-block sub">{gt text="The default hashing method is 'SHA256'."}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="Show password strength meter"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)}checked="checked" {/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)}checked="checked" {/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="E-mail address verifications expire in"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|default:0}" maxlength="3" />
                        <span class="input-group-addon">{gt text="days"}</span>
                    </div>
                    <em class="sub help-block">{gt text="Enter the number of days a user's request to change e-mail addresses should be kept while waiting for verification. Enter zero (0) for no expiration."}</em>
                    <div class="alert alert-warning help-block">{gt text="Changing this setting will affect all requests to change e-mail addresses currently pending verification."}</div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Password reset requests expire in'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|default:0}" maxlength="3" />
                        <span class="input-group-addon">{gt text='days'}</span>
                    </div>
                    <em class="sub help-block">{gt text="This setting only affects users who have not established security question responses. Enter the number of days a user's request to reset a password should be kept while waiting for verification. Enter zero (0) for no expiration."}</em>
                    <div class="alert alert-warning help-block">{gt text='Changing this setting will affect all password change requests currently pending verification.'}</div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Registration settings"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ENABLED'|const}
                <label class="col-sm-3 control-label">{gt text="Allow new user account registrations"}<span class="required"></span></label>
                {assign var='registrationEnabledFieldName' value=$fieldName}
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes'}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No'}</label>
                        {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                    </div>
                </div>
            </div>
            <div class="form-group" data-switch="{$registrationEnabledFieldName}" data-switch-value="0">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_DISABLED_REASON'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="Statement displayed if registration disabled"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="{$configData->getFieldId($fieldName)}" name="{$fieldName}" cols="45" rows="10">{$configData->getFieldData($fieldName)|safehtml}</textarea>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text="E-mail address to notify of registrations"}</label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}" type="email" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                    <em class="help-block sub">{gt text='A notification is sent to this e-mail address for each registration. Leave blank for no notifications.'}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_PASSWORD_REMINDER_ENABLED'|const}
                <label class="col-sm-3 control-label">{gt text='Password reminder is enabled'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes'}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No'}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_PASSWORD_REMINDER_MANDATORY'|const}
                <label class="col-sm-3 control-label">{gt text='Password reminder is mandatory'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes'}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No'}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED'|const}
                <label class="col-sm-3 control-label">{gt text='User registration is moderated'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div id="{$configData->getFieldId($fieldName)}">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes'}</label>
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No'}</label>
                        {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                    </div>
                </div>
            </div>
            {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_VERIFICATION_MODE'|const}
            <div class="form-group" id="{$configData->getFieldId($fieldName)}">
                <label class="col-sm-3 control-label">{gt text="Verify e-mail address during registration"}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div>
                        <input id="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::VERIFY_USERPWD'|const}" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::VERIFY_USERPWD'|const}" {if ($configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::VERIFY_USERPWD')) || $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::VERIFY_SYSTEMPWD')} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::VERIFY_USERPWD'|const}">{gt text='Yes. User chooses password, then activates account via e-mail'}</label>
                    </div>
                    <div>
                        <input id="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::VERIFY_NO'|const}" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::VERIFY_NO'|const}" {if $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::VERIFY_NO')} checked="checked"{/if}/>
                        <label for="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::VERIFY_NO'|const}">{gt text='No'}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_AUTO_LOGIN'|const}
            <div class="form-group" id="{$configData->getFieldId($fieldName)}_wrap">
                <label class="col-sm-3 control-label">{gt text='Log in new registrations automatically?'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div class="z-formlist">
                        <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Newly registered users are logged in automatically.'}</label>
                    </div>
                    <em class="sub help-block">{gt text="Newly registered users are logged in automatically only if there is no approval, verification, or other requirement that must be met to complete the registration process."}</em>
                    <div class="z-formlist">
                        <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_no">{gt text='Newly registered users are not logged in automatically.'}</label>
                    </div>
                    <em class="sub help-block">{gt text='Newly registered users are redirected to the log-in screen, if appropriate. If there are other registration requirements to be met, then they are shown this information instead.'}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE'|const}
            <div class="form-group" id="{$configData->getFieldId($fieldName)}_wrap">
                <label class="col-sm-3 control-label">{gt text='Order that approval and verification occur'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div class="z-formlist">
                        <input id="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::APPROVAL_BEFORE'|const}" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::APPROVAL_BEFORE'|const}"{if $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::APPROVAL_BEFORE')} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::APPROVAL_BEFORE'|const}">{gt text="Registration applications must be approved before users verify their e-mail address."}</label>
                    </div>
                    <div class="z-formlist">
                        <input id="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::APPROVAL_AFTER'|const}" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::APPROVAL_AFTER'|const}"{if $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::APPROVAL_AFTER')} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::APPROVAL_AFTER'|const}">{gt text="Users must verify their e-mail address before their application is approved."}</label>
                    </div>
                    <div class="z-formlist">
                        <input id="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::APPROVAL_ANY'|const}" type="radio" name="{$fieldName}" value="{'Zikula\UsersModule\Constant::APPROVAL_ANY'|const}"{if $configData->getFieldData($fieldName) == constant('Zikula\UsersModule\Constant::APPROVAL_ANY')} checked="checked"{/if} />
                        <label for="{$configData->getFieldId($fieldName)}_{'Zikula\UsersModule\Constant::APPROVAL_ANY'|const}">{gt text="Application approval and e-mail address verification can occur in any order."}</label>
                    </div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_EXPIRE_DAYS_REGISTRATION'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Registrations pending verification expire in'}<span class="required"></span></label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input id="{$configData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} form-error{/if}" type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|default:0}" maxlength="3" />
                        <span class="input-group-addon">{gt text='days'}</span>
                    </div>
                    <em class="sub help-block">{gt text="Enter the number of days a registration record should be kept while waiting for e-mail address verification. (Unverified registrations will be deleted the specified number of days after sending an e-mail verification message.) Enter zero (0) for no expiration (no automatic deletion)."}</em>
                    <div class="alert alert-info help-block">{gt text="If registration is moderated and applications must be approved before verification, then registrations will not expire until the specified number of days after approval."}</div>
                    <div class="alert alert-warning help-block">{gt text="Changing this setting will affect all registrations currently pending e-mail address verification."}</div>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>

            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Spam protection question'}</label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}" class="form-control" tpye="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="50" maxlength="255" />
                    <em class="help-block sub">{gt text="You can set a question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts."}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Spam protection answer'}<span id="{$configData->getFieldId($fieldName)}_mandatory" class="required hide"></span></label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} form-error{/if}" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="50" maxlength="255" />
                    <em class="help-block sub">{gt text="Registering users will have to provide this response when answering the spam protection question. It is required if a spam protection question is provided."}</em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Reserved user names'}</label>
                <div class="col-sm-9">
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} type="text" class="form-control" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                    <em class="help-block sub">
                        {gt text='Separate each user name with a comma.'}<br />
                        {gt text='Each user name on this list is not allowed to be chosen by someone registering for a new account.'}
                    </em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Banned user agents'}</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} name="{$fieldName}" cols="45" rows="2">{$configData->getFieldData($fieldName)|safehtml}</textarea>
                    <em class="help-block sub">
                        {gt text='Separate each user agent string with a comma.'}<br />
                        {gt text='Each item on this list is a browser user agent identification string. If a user attempts to register a new account using a browser whose user agent string begins with one on this list, then the user is not allowed to begin the registration process.'}
                    </em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS'|const}
                <label class="col-sm-3 control-label" for="{$configData->getFieldId($fieldName)}">{gt text='Banned e-mail address domains'}</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="form-error"{/if} name="{$fieldName}" cols="45" rows="2">{$configData->getFieldData($fieldName)|safehtml}</textarea>
                    <em class="help-block sub">
                        {gt text='Separate each domain with a comma.'}<br />
                        {gt text="Each item on this list is an e-mail address domain (the part after the '@'). E-mail addresses on new registrations or on an existing user's change of e-mail address requests are not allowed to have any domain on this list."}
                    </em>
                    {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User log-in settings"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_LOGIN_WCAG_COMPLIANT'|const}
                <label class="col-sm-3 control-label">{gt text='WCAG-compliant log-in and log-out'}<span class="required"></span></label>
                <div class="col-sm-9">
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes'}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No'}</label>
                    <em class="sub">{gt text="Notice: Uses meta refresh."}</em>
                </div>
                {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
            </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS'|const}
                <label class="col-sm-3 control-label">{gt text='Failed login displays inactive status'}<span class="required"></span></label>
                <div class="col-sm-9">
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes. The log-in error message will indicate that the user account is inactive.'}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No. A generic error message is displayed.'}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
            </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS'|const}
                <label class="col-sm-3 control-label">{gt text='Failed login displays verification status'}<span class="required"></span></label>
                <div class="col-sm-9">
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes. The log-in error message will indicate that the registration is pending verification.'}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No. A generic error message is displayed.'}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
            </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\UsersModule\Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS'|const}
                <label class="col-sm-3 control-label">{gt text='Failed login displays approval status'}<span class="required"></span></label>
                <div class="col-sm-9">
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text='Yes. The log-in error message will indicate that the registration is pending approval.'}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text='No. A generic error message is displayed.'}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="help-block alert alert-danger">{$errorFields.$fieldName}</p>{/if}
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Save'}">
                    {gt text="Save"}
                </button>
                {helplink filename='Help/Admin/config.txt' class="btn btn-info" popup=1 __title='Help' icon_type='help' icon_size='extrasmall' __icon_alt='Help' __icon_title='Help'}
                <a class="btn btn-danger" href="{route name='zikulausersmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
