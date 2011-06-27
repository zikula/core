{strip}
{ajaxheader modname='Users' filename='Zikula.Users.Admin.Config.js' noscriptaculous=true effects=true}
{pageaddvarblock}
<script type="text/javascript">
    Zikula.Users.Admin.Config.setup = function() {
        Zikula.Users.Admin.Config.formId = '{{$configData->getFormId()}}';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ENABLED'|constant}}
        Zikula.Users.Admin.Config.registrationEnabledId = '{{$configData->getFieldId($fieldName)}}';
        Zikula.Users.Admin.Config.registrationEnabledWrapId = '{{$configData->getFieldId($fieldName)}}' + '_wrap';
        Zikula.Users.Admin.Config.registrationEnabledYesId = '{{$configData->getFieldId($fieldName)}}' + '_yes';
        Zikula.Users.Admin.Config.registrationEnabledNoId = '{{$configData->getFieldId($fieldName)}}' + '_no';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED'|constant}}
        Zikula.Users.Admin.Config.registrationModeratedId = '{{$configData->getFieldId($fieldName)}}';
        Zikula.Users.Admin.Config.registrationModeratedYesId = '{{$configData->getFieldId($fieldName)}}' + '_yes';
        Zikula.Users.Admin.Config.registrationModeratedNoId = '{{$configData->getFieldId($fieldName)}}' + '_no';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN'|constant}}
        Zikula.Users.Admin.Config.registrationAutoLoginWrapId = '{{$configData->getFieldId($fieldName)}}' + '_wrap';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE'|constant}}
        Zikula.Users.Admin.Config.registrationApprovalOrderWrapId = '{{$configData->getFieldId($fieldName)}}' + '_wrap';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE'|constant}}
        Zikula.Users.Admin.Config.registrationVerificationModeId = '{{$configData->getFieldId($fieldName)}}';
        Zikula.Users.Admin.Config.registrationVerificationModeUserPwdId = '{{$configData->getFieldId($fieldName)}}' + '_' + '{{'Users_Constant::VERIFY_USERPWD'|constant}}';
        Zikula.Users.Admin.Config.registrationVerificationModeNoneId = '{{$configData->getFieldId($fieldName)}}' + '_' + '{{'Users_Constant::VERIFY_NO'|constant}}';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION'|constant}}
        Zikula.Users.Admin.Config.registrationAntispamQuestionId = '{{$configData->getFieldId($fieldName)}}';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER'|constant}}
        Zikula.Users.Admin.Config.registrationAntispamAnswerMandatoryId = '{{$configData->getFieldId($fieldName)}}' + '_mandatory';

        {{assign var='fieldName' value='Users_Constant::MODVAR_LOGIN_METHOD'|constant}}
        Zikula.Users.Admin.Config.loginMethodId = '{{$configData->getFieldId($fieldName)}}';
        Zikula.Users.Admin.Config.loginMethodUserNameId = '{{$configData->getFieldId($fieldName)}}' + '_username';
        Zikula.Users.Admin.Config.loginMethodEmailId = '{{$configData->getFieldId($fieldName)}}' + '_email';
        Zikula.Users.Admin.Config.loginMethodEitherId = '{{$configData->getFieldId($fieldName)}}' + '_either';

        {{assign var='fieldName' value='Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL'|constant}}
        Zikula.Users.Admin.Config.requireUniqueEmailYesId = '{{$configData->getFieldId($fieldName)}}' + '_yes';
        Zikula.Users.Admin.Config.requireUniqueEmailNoId = '{{$configData->getFieldId($fieldName)}}' + '_no';
    }
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" id="{$configData->getFormId()}" action="{modurl modname='Users' type='admin' func='config'}" method="post">
    <div>
        <input id="{$configData->getFormId()}_csrftoken" name="csrftoken" type="hidden" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Name displayed for anonymous user"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="20" maxlength="20" />
                <em class="z-formnote z-sub">{gt text="Anonymous users are visitors to your site who have not logged in."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_ITEMS_PER_PAGE'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Number of items displayed per page"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" size="3" value="{$configData->getFieldData($fieldName)|safetext}" />
                <em class="z-formnote z-sub">{gt text="When lists are displayed (for example, lists of users, lists of registrations) this option controls how many items are displayed at one time."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_AVATAR_IMAGE_PATH'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Path to user's avatar images"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_GRAVATARS_ENABLED'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Allow globally recognized avatars"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_GRAVATAR_IMAGE'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Default gravatar image"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Account page settings"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS'|constant}
                <label>{gt text="Display graphics on user's account page"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)}checked="checked" {/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)}checked="checked" {/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Path to account page images"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Number of links per page"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" size="3" value="{$configData->getFieldData($fieldName)|safetext}" />
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Number of links per row"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" size="3" value="{$configData->getFieldData($fieldName)|safetext}" />
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_MANAGE_EMAIL_ADDRESS'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Users module handles e-mail address maintenance"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User credential settings"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_LOGIN_METHOD'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Credential required for user log-in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_username" type="radio" name="{$fieldName}" value="{'Users_Constant::LOGIN_METHOD_UNAME'|constant}" {if ($configData->getFieldData($fieldName) < constant('Users_Constant::LOGIN_METHOD_EMAIL')) || ($configData->getFieldData($fieldName) > constant('Users_Constant::LOGIN_METHOD_ANY'))}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_username">{gt text="User name"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_email" type="radio" name="{$fieldName}" value="{'Users_Constant::LOGIN_METHOD_EMAIL'|constant}" {if $configData->getFieldData($fieldName) == constant('Users_Constant::LOGIN_METHOD_EMAIL')}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_email">{gt text="E-mail address"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_either" type="radio" name="{$fieldName}" value="{'Users_Constant::LOGIN_METHOD_ANY'|constant}" {if $configData->getFieldData($fieldName) == constant('Users_Constant::LOGIN_METHOD_ANY')}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_either">{gt text="Either user name or e-mail address"}</label>
                    <div class="z-formnote z-warningmsg">{gt text="Notice: If the 'Credential required for user log-in' is set to 'E-mail address' or to 'Either user name or e-mail address', then the 'New e-mail addresses must be unique' option below must be set to 'Yes'."}</div>
                    <div class="z-formnote z-warningmsg">{gt text="Notice: If the 'New e-mail addresses must be unique' option was set to 'no' at some point, then user accounts with duplicate e-mail addresses might exist in the system. They will experience difficulties logging in with their e-mail address."}</div>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="New e-mail addresses must be unique"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                <em class="z-formnote z-sub">{gt text="If set to yes, then e-mail addresses entered for new registrations and for e-mail address change requests cannot already be in use by another user account or registration."}</em>
                <div class="z-formnote z-warningmsg">{gt text="Notice: If this option was set to 'no' at some point, then user accounts or registrations with duplicate e-mail addresses might exist in the system. Setting this option to 'yes' will not affect those accounts or registrations."}</div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Minimum length for user passwords"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="2" maxlength="2" />
                <em class="z-formnote z-sub">{gt text="This affects both passwords created during registration, as well as passwords modified by users or administrators."} {gt text="Enter an integer greater than zero."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_HASH_METHOD'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Password hashing method"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <select id="{$configData->getFieldId($fieldName)}" name="{$fieldName}">
                    <option value="sha1" {if $configData->getFieldData($fieldName) == 'sha1'} selected="selected"{/if}>SHA1</option>
                    <option value="sha256" {if $configData->getFieldData($fieldName) == 'sha256'} selected="selected"{/if}>SHA256</option>
                </select>
                <em class="z-formnote z-sub">{gt text="The default hashing method is 'SHA256'."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Show password strength meter"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)}checked="checked" {/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)}checked="checked" {/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="E-mail address verifications expire in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div>
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|default:0}" maxlength="3" />
                    <label for="{$configData->getFieldId($fieldName)}">{gt text="days"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Enter the number of days a user's request to change e-mail addresses should be kept while waiting for verification. Enter zero (0) for no expiration."}</em>
                <div class="z-warningmsg z-formnote">{gt text="Changing this setting will affect all requests to change e-mail addresses currently pending verification."}</div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Password reset requests expire in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div>
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|default:0}" maxlength="3" />
                    <label for="{$configData->getFieldId($fieldName)}">{gt text="days"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="This setting only affects users who have not established security question responses. Enter the number of days a user's request to reset a password should be kept while waiting for verification. Enter zero (0) for no expiration."}</em>
                <div class="z-warningmsg z-formnote">{gt text="Changing this setting will affect all password change requests currently pending verification."}</div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Registration settings"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ENABLED'|constant}
                <label>{gt text="Allow new user account registrations"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow" id="{$configData->getFieldId($fieldName)}_wrap">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_DISABLED_REASON'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Statement displayed if registration disabled"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <textarea id="{$configData->getFieldId($fieldName)}" name="{$fieldName}" cols="45" rows="10">{$configData->getFieldData($fieldName)|safehtml}</textarea>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="E-mail address to notify of registrations"}</label>
                <input id="{$configData->getFieldId($fieldName)}" type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="A notification is sent to this e-mail address for each registration. Leave blank for no notifications."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED'|constant}
                <label>{gt text="User registration is moderated"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE'|constant}
            <div class="z-formrow" id="{$configData->getFieldId($fieldName)}">
                <label>{gt text="Verify e-mail address during registration"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_{'Users_Constant::VERIFY_USERPWD'|constant}" type="radio" name="{$fieldName}" value="{'Users_Constant::VERIFY_USERPWD'|constant}" {if ($configData->getFieldData($fieldName) == constant('Users_Constant::VERIFY_USERPWD')) || $configData->getFieldData($fieldName) == constant('Users_Constant::VERIFY_SYSTEMPWD')} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_{'Users_Constant::VERIFY_USERPWD'|constant}">{gt text="Yes. User chooses password, then activates account via e-mail"}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_{'Users_Constant::VERIFY_NO'|constant}" type="radio" name="{$fieldName}" value="{'Users_Constant::VERIFY_NO'|constant}" {if $configData->getFieldData($fieldName) == constant('Users_Constant::VERIFY_NO')} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_{'Users_Constant::VERIFY_NO'|constant}">{gt text="No"}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN'|constant}
            <div class="z-formrow" id="{$configData->getFieldId($fieldName)}_wrap">
                <label>{gt text="Log in new registrations automatically?"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Newly registered users are logged in automatically."}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Newly registered users are logged in automatically only if there is no approval, verification, or other requirement that must be met to complete the registration process."}</em>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="Newly registered users are not logged in automatically."}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Newly registered users are redirected to the log-in screen, if appropriate. If there are other registration requirements to be met, then they are shown this information instead."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE'|constant}
            <div class="z-formrow" id="{$configData->getFieldId($fieldName)}_wrap">
                <label>{gt text="Order that approval and verification occur"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_{'Users_Constant::APPROVAL_BEFORE'|constant}" type="radio" name="{$fieldName}" value="{'Users_Constant::APPROVAL_BEFORE'|constant}" {if $configData->getFieldData($fieldName) == constant('Users_Constant::APPROVAL_BEFORE')} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_{'Users_Constant::APPROVAL_BEFORE'|constant}">{gt text="Registration applications must be approved before users verify their e-mail address."}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_{'Users_Constant::APPROVAL_AFTER'|constant}" type="radio" name="{$fieldName}" value="{'Users_Constant::APPROVAL_AFTER'|constant}" {if $configData->getFieldData($fieldName) == constant('Users_Constant::APPROVAL_AFTER')} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_{'Users_Constant::APPROVAL_AFTER'|constant}">{gt text="Users must verify their e-mail address before their application is approved."}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_{'Users_Constant::APPROVAL_ANY'|constant}" type="radio" name="{$fieldName}" value="{'Users_Constant::APPROVAL_ANY'|constant}" {if $configData->getFieldData($fieldName) == constant('Users_Constant::APPROVAL_ANY')} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_{'Users_Constant::APPROVAL_ANY'|constant}">{gt text="Application approval and e-mail address verification can occur in any order."}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_EXPIRE_DAYS_REGISTRATION'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Registrations pending verification expire in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div>
                    <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|default:0}" maxlength="3" />
                    <label for="{$configData->getFieldId($fieldName)}">{gt text="days"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Enter the number of days a registration record should be kept while waiting for e-mail address verification. (Unverified registrations will be deleted the specified number of days after sending an e-mail verification message.) Enter zero (0) for no expiration (no automatic deletion)."}</em>
                <div class="z-informationmsg z-formnote">{gt text="If registration is moderated and applications must be approved before verification, then registrations will not expire until the specified number of days after approval."}</div>
                <div class="z-warningmsg z-formnote">{gt text="Changing this setting will affect all registrations currently pending e-mail address verification."}</div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>

            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Spam protection question"}</label>
                <input id="{$configData->getFieldId($fieldName)}" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="You can set a question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Spam protection answer"}<span id="{$configData->getFieldId($fieldName)}_mandatory" class="z-form-mandatory-flag z-hide">{gt text="*"}</span></label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safehtml}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="Registering users will have to provide this response when answering the spam protection question. It is required if a spam protection question is provided."}</em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Reserved user names"}</label>
                <input id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} type="text" name="{$fieldName}" value="{$configData->getFieldData($fieldName)|safetext}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">
                    {gt text="Separate each user name with a comma."}<br />
                    {gt text="Each user name on this list is not allowed to be chosen by someone registering for a new account."}
                </em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Banned user agents"}</label>
                <textarea id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} name="{$fieldName}" cols="45" rows="2">{$configData->getFieldData($fieldName)|safehtml}</textarea>
                <em class="z-formnote z-sub">
                    {gt text="Separate each user agent string with a comma."}<br />
                    {gt text="Each item on this list is a browser user agent identification string. If a user attempts to register a new account using a browser whose user agent string begins with one on this list, then the user is not allowed to begin the registration process."}
                </em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS'|constant}
                <label for="{$configData->getFieldId($fieldName)}">{gt text="Banned e-mail address domains"}</label>
                <textarea id="{$configData->getFieldId($fieldName)}"{if isset($errorFields.$fieldName)} class="z-form-error"{/if} name="{$fieldName}" cols="45" rows="2">{$configData->getFieldData($fieldName)|safehtml}</textarea>
                <em class="z-formnote z-sub">
                    {gt text="Separate each domain with a comma."}<br />
                    {gt text="Each item on this list is an e-mail address domain (the part after the '@'). E-mail addresses on new registrations or on an existing user's change of e-mail address requests are not allowed to have any domain on this list."}
                </em>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User log-in settings"}</legend>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_LOGIN_WCAG_COMPLIANT'|constant}
                <label>{gt text="WCAG-compliant log-in and log-out"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="{$configData->getFieldId($fieldName)}">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)}checked="checked" {/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                    <em class="z-sub">{gt text="Notice: Uses meta refresh."}</em>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS'|constant}
                <label>{gt text="Failed login displays inactive status"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes. The log-in error message will indicate that the user account is inactive."}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No. A generic error message is displayed."}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS'|constant}
                <label>{gt text="Failed login displays verification status"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes. The log-in error message will indicate that the registration is pending verification."}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No. A generic error message is displayed."}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
            <div class="z-formrow">
                {assign var='fieldName' value='Users_Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS'|constant}
                <label>{gt text="Failed login displays approval status"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1" {if $configData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$configData->getFieldId($fieldName)}_yes">{gt text="Yes. The log-in error message will indicate that the registration is pending approval."}</label>
                </div>
                <div class="z-formlist">
                    <input id="{$configData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0" {if !$configData->getFieldData($fieldName)} checked="checked"{/if}/>
                    <label for="{$configData->getFieldId($fieldName)}_no">{gt text="No. A generic error message is displayed."}</label>
                </div>
                {if isset($errorFields.$fieldName)}<p class="z-formnote z-errormsg">{$errorFields.$fieldName}</p>{/if}
            </div>
        </fieldset>

        <div class="z-formbuttons z-buttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            {helplink filename='Help/Admin/config.txt' popup=1 __title='Help' icon_type='help' icon_size='extrasmall' __icon_alt='Help' __icon_title='Help'}
            <a href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}