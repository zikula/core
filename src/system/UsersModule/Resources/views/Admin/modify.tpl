{pageaddvar name="javascript" value="system/UsersModule/Resources/public/js/Zikula.Users.Admin.Modify.js"}
{pageaddvar name="javascript" value="system/UsersModule/Resources/public/js/Zikula.Users.Common.UserValidation.js"}
{strip}
    {gt text='Edit user account of %s' tag1=$user_attributes.realname|default:$formData->getFieldData('uname') assign='templatetitle'}
    {if $modvars.ZikulaUsersModule.use_password_strength_meter == 1}
        {pageaddvar name='javascript' value='jquery'}
        {pageaddvar name='javascript' value='system/UsersModule/Resources/public/js/Zikula.Users.PassMeter.js'}
        {pageaddvarblock}
            <script type="text/javascript">
                ( function($) {
                    $(document).ready(function() {
                        ZikulaUsersPassMeter.init('{{$formData->getFieldId('pass')}}', '{{$formData->getFormId()}}_passmeter',{
                            username: '{{$formData->getFieldId('uname')}}',
                            minLength: '{{$modvars.ZikulaUsersModule.minpass}}'
                        });
                    });
                })(jQuery);
            </script>
        {/pageaddvarblock}
    {/if}
    {insert name='csrftoken' assign='csrftoken'}
    {if ($formData->getFieldData('uid') == $coredata.user.uid)}
        {assign var='editingSelf' value=true}
    {else}
        {assign var='editingSelf' value=false}
    {/if}
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
    <span class="fa fa-pencil"></span>
    {$templatetitle}
</h3>

<p class="alert alert-warning">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

{if $editingSelf}
<div class="alert alert-info">{gt text='You are editing your own record, therefore you are not permitted to change your membership in certain system groups, and you are not permitted to change your activated state. These fields are disabled below.'}</div>
{/if}
<form id="{$formData->getFormId()}" class="form-horizontal" role="form" action="{route name='zikulausersmodule_admin_modify'}" method="post">
    <fieldset>
        <legend>{gt text='Account information'}</legend>
        <input id="{$formData->getFormId()}_csrftoken" type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="{$formData->getFormId()}_event_type" type="hidden" name="event_type" value="modify_user" />
        {assign var='fieldName' value='uid'}
        <input id="{$formData->getFieldId($fieldName)}" type="hidden" name="{$fieldName}" value="{$formData->getFieldData($fieldName)|safetext}" />
        <div class="form-group">
            {assign var='fieldName' value='uname'}
            <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='User name'}<span class="required"></span></label>
            {assign var='fieldName' value='uname'}
            <div class="col-sm-9">
                <input id="{$formData->getFieldId($fieldName)}"  class=" to-lower-case form-control{if isset($errorFields.$fieldName)}form-error{/if}" type="text" name="{$fieldName}" size="30" maxlength="25" value="{$formData->getFieldData($fieldName)|safetext}" required="required"/>
                <em class="help-block sub">{gt text='User names can contain letters, numbers, underscores, periods, or dashes.'}</em>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        <div class="form-group">
            {assign var='fieldName' value='email'}
            <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='E-mail address'}<span class="required"></span></label>
            {assign var='fieldName' value='email'}
            <div class="col-sm-9">
                <input id="{$formData->getFieldId($fieldName)}" class="form-control to-lower-case{if isset($errorFields.$fieldName)} form-error{/if}" type="email" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" data-match="#users_modify_emailagain" data-match-error-message="{gt text='The value entered does not match the e-mail address entered in the e-mail address field.'}" required="required"/>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        <div class="form-group">
            {assign var='fieldName' value='emailagain'}
            <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Repeat e-mail address for verification'}<span class="required"></span></label>
            {assign var='fieldName' value='emailagain'}
            <div class="col-sm-9">
                <input id="{$formData->getFieldId($fieldName)}" class="form-control to-lower-case{if isset($errorFields.$fieldName)} form-error{/if}" type="email" name="{$fieldName}" size="30" maxlength="60" value="{$formData->getFieldData($fieldName)|safetext}" required="required"/>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        <div class="form-group">
            {assign var='fieldName' value='activated'}
            <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='User status'}</label>
            {assign var='fieldName' value='activated'}
            <div class="col-sm-9">
                {if $editingSelf}
                <input type="hidden" name="{$fieldName}" value="{$formData->getFieldData($fieldName)}" />
                {/if}
                <select id="{$formData->getFieldId($fieldName)}" class="form-control" name="{$fieldName}{if $editingSelf}_displayonly" disabled="disabled{/if}">
                    <option value="{'Zikula\UsersModule\Constant::ACTIVATED_INACTIVE'|const}" {if $formData->getFieldData($fieldName) != 'Zikula\UsersModule\Constant::ACTIVATED_ACTIVE'|const}selected="selected"{/if}>{gt text="Inactive"}</option>
                    <option value="{'Zikula\UsersModule\Constant::ACTIVATED_ACTIVE'|const}" {if $formData->getFieldData($fieldName) == 'Zikula\UsersModule\Constant::ACTIVATED_ACTIVE'|const}selected="selected"{/if}>{gt text="Active"}</option>
                </select>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        <div class="form-group">
            {assign var='fieldName' value='theme'}
            <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">
                {gt text='Theme'}
            </label>
            {assign var='fieldName' value='theme'}
            <div class="col-sm-9">
                <select id="{$formData->getFieldId($fieldName)}" class="form-control" name="{$fieldName}">
                    <option value="">{gt text="Site's default theme"}</option>
                    {html_select_themes selected=$formData->getFieldData($fieldName) state=ThemeUtil::STATE_ACTIVE filter=ThemeUtil::FILTER_USER}
                </select>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">
                    {if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}
                </p>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Log-in information'}</legend>
        {if $hasNoPassword}
            {assign var='usersAuth' value=false}
        {else}
            {assign var='usersAuth' value=true}
        {/if}
        {if !$usersAuth}
        <p class="alert alert-info">
            {gt text='This user does not currently log in with a web site account password. To enable the user to do so&mdash;in addition to any other log-in method used, create a password for the account here.'}
        </p>
        {/if}
        {assign var='fieldName' value='setpass'}


        <p class="help-block alert alert-info">
            {gt text='This user does not currently log in with a web site account password. To enable the user to do so&mdash;in addition to any other log-in method used, create a password for the account here.'}
        </p>

        <div id="{$formData->getFieldId($fieldName)}_wrap" class="form-group">
            <label class="col-sm-3 control-label">
                {if $usersAuth}
                {gt text="Change the user's password?"}
                {else}
                {gt text="Create a password for the user?"}
                {/if}
            </label>


            {assign var='fieldName' value='setpass'}
            <div id="{$formData->getFieldId($fieldName)}_wrap" class="col-sm-9">
                <div id="{$formData->getFieldId($fieldName)}">
                    <input id="{$formData->getFieldId($fieldName)}_yes" type="radio" name="{$fieldName}" value="1"{if $formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_yes">{gt text="Yes"}</label>
                    <input id="{$formData->getFieldId($fieldName)}_no" type="radio" name="{$fieldName}" value="0"{if !$formData->getFieldData($fieldName)} checked="checked"{/if} />
                    <label for="{$formData->getFieldId($fieldName)}_no">{gt text="No"}</label>
                </div>
                <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">{if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}</p>
            </div>
        </div>
        <div id="{$formData->getFieldId('pass')}_wrap">
            {assign var='fieldName' value='pass'}
            <div class="form-group">
                <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">
                    {if $usersAuth}
                    {gt text='New password'}
                    {else}
                    {gt text='Create a password'}
                    {/if}
                    <span class="required"></span>
                </label>
                <div class="col-sm-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} form-error{/if}" type="password" name="{$fieldName}" size="30" maxlength="20" data-match="#users_modify_passagain" data-match-error-message="The value entered does not match the password entered in the password field." data-min="{$modvars.ZikulaUsersModule.minpass}" data-min-error-message="{gt text='Passwords must be at least %s characters in length.' tag1=$modvars.ZikulaUsersModule.minpass}"/>
                    <em class="sub help-block">{gt text='Notice: The minimum length for user passwords is %s characters.' tag1=$modvars.ZikulaUsersModule.minpass}</em>
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">
                        {if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}
                    </p>
                    <div id="{$formData->getFormId()}_passmeter"></div>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='passagain'}
                <label class="col-sm-3 control-label" for="{$formData->getFieldId($fieldName)}">{gt text='Repeat password for verification'}<span class="required"></span></label>
                {assign var='fieldName' value='passagain'}
                <div class="col-sm-9">
                    <input id="{$formData->getFieldId($fieldName)}" class="form-control{if isset($errorFields.$fieldName)} form-error{/if}" type="password" name="{$fieldName}" size="30" maxlength="20" />
                    <p id="{$formData->getFieldId($fieldName)}_error" class="help-block alert alert-danger{if !isset($errorFields.$fieldName)} hide{/if}">
                        {if isset($errorFields.$fieldName)}{$errorFields.$fieldName}{/if}
                    </p>
                </div>
            </div>
            <div id="{$formData->getFormId()}_password_not_set_wrap" class="form-group hide">
                {if $usersAuth}
                <p class="help-block alert alert-info">{gt text="The user's password will not be changed."}</p>
                {else}
                <p class="help-block alert alert-info">{gt text="A web site account password will not be created for the user. The user will continue to log in with the method(s) currently used."}</p>
                {/if}
                <div id="{$formData->getFormId()}_password_is_set_wrap">
                    {if $usersAuth}
                    <p class="help-block alert alert-warning">{gt text="The user's password will be changed."}</p>
                    {else}
                    <p class="help-block alert alert-warning">{gt text="A web site account password will be created for the user. The user will continue to log in with the method(s) currently used, and will additionally be able to log in with this password."}</p>
                    {/if}
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Group membership'}</legend>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{gt text='Group'}</th>
                    <th>{gt text='Member'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach key='group_id' item='group' from=$accessPermissions}
                <tr>
                    <td>{$group.name}</td>
                    <td class="text-right">
                        {if $editingSelf && ((($group_id == $defaultGroupId) && $group.access) || ($group_id == $primaryAdminGroupId))}<input type="hidden" name="access_permissions[]" value="{$group_id}" />{/if}<input type="checkbox" {if $editingSelf && ((($group_id == $defaultGroupId) && $group.access) || ($group_id == $primaryAdminGroupId))}disabled="disabled"{else}name="access_permissions[]" value="{$group_id}"{/if} {if $group.access}checked="checked" {/if}/>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <p id="{$formData->getFormId()}_groupmembership_error" class="help-block alert alert-danger{if !isset($errorFields.groupmembership)} hide{/if}">
            {if isset($errorFields.groupmembership)}{$errorFields.groupmembership}{/if}
        </p>
    </fieldset>

    {notifyevent eventname='module.users.ui.form_edit.modify_user' eventsubject=$formData->toUserArray() id=$formData->getFieldData('uid') assign="eventData"}
    {foreach item='eventDisplay' from=$eventData}
        {$eventDisplay}
    {/foreach}

    {notifydisplayhooks eventname='users.ui_hooks.user.form_edit' id=$formData->getFieldData('uid')}

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <div id="{$formData->getFormId()|cat:'_ajax_indicator'}" class="btn btn-warning hide"><i class="fa fa-spinner fa-spin"></i>&nbsp;{gt text="Checking"}</div>
            <button id="{$formData->getFormId()|cat:'_submit'}" class="btn btn-success" type="submit" title="{gt text='Save'}">
                {gt text='Save'}
            </button>
            <a class="btn btn-danger" href="{route name='zikulausersmodule_admin_view'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>

<h3>
    <span class="fa fa-cog"></span>
    {gt text='Other actions for %s' tag1=$user_attributes.realname|default:$formData->getFieldData('uname')}
</h3>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
        {if !$editingSelf}
        <a class="btn btn-danger" href="{route name='zikulausersmodule_admin_deleteusers' userid=$formData->getFieldData('uid')}">{gt text='Delete'}</a>
        {/if}
        <a class="btn btn-info" href="{route name='zikulausersmodule_admin_lostusername' userid=$formData->getFieldData('uid') csrftoken=$csrftoken}">{gt text='Send user name'}</a>
        <a class="btn btn-info" href="{route name='zikulausersmodule_admin_lostpassword' userid=$formData->getFieldData('uid') csrftoken=$csrftoken}">{gt text='Send password recovery code'}</a>
    </div>
</div>
{adminfooter}