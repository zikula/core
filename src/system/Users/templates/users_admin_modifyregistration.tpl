{ajaxheader modname='Users' filename='users_admin_modifyregistration.js' noscriptaculous=true effects=true}
{gt text='Edit registration for \'%1$s\'' tag1=$reginfo.uname assign='templatetitle'}

{include file='users_admin_menu.tpl'}
<a id="users_formtop"></a>
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=xedit.gif set=icons/large alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>
    <p class="z-warningmsg">{gt text="The items that are marked with an asterisk ('*') are required entries."}</p>

    <div id="users_errormessages_div" class="z-errormsg{if empty($errorMessages)} z-hide{/if}">
        <p>Please correct the following items:</p>
        <ul id="users_errormessages">
            {foreach from=$errorMessages item='message'}
            <li>{$message}</li>
            {/foreach}
        </ul>
    </div>

    <form id="users_modifyregistration" class="z-form" action="{modurl modname='Users' type='admin' func='updateRegistration'}" method="post">
        <div>
            <input type="hidden" id="users_authid" name="authid" value="{insert name='generateauthkey' module='Users'}" />
            <input type="hidden" id="users_reginfo_uid" name="reginfo[uid]" value="{$reginfo.uid}" />
            <input type="hidden" id="users_reginfo_agreetoterms" name="reginfo[agreetoterms]" value="{$reginfo.agreetoterms}" />
            <input type="hidden" id="users_checkmode" name="checkmode" value="modify" />
            <input type="hidden" id="users_restoreview" name="restoreview" value="{$restoreview|default:'view'}" />
            <fieldset>
                <legend>{gt text='Registration info'}</legend>
                <div class="z-formrow">
                    <label for="users_reginfo_uname">{gt text='User name'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                    <input id="users_reginfo_uname"{if isset($errorFields.reginfo_uname)} class="errorrequired"{/if} type="text" name="reginfo[uname]" size="21" maxlength="25" value="{$reginfo.uname|default:''}" />
                    <em class="z-formnote z-sub">{gt text='User names can contain letters, numbers, underscores, and/or periods.'}</em>
                </div>
                <div class="z-formrow">
                    <label for="users_reginfo_email">{gt text='E-mail address'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                    <input id="users_reginfo_email"{if isset($errorFields.reginfo_email) || isset($errorFields.emailagain)} class="errorrequired"{/if} type="text" name="reginfo[email]" size="21" maxlength="60" value="{$reginfo.email|default:''}" />
                </div>
                <div class="z-formrow">
                    <label for="users_emailagain">{gt text='E-mail address (repeat for verification)'}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                    <input id="users_emailagain"{if isset($errorFields.emailagain)} class="errorrequired"{/if} type="text" name="emailagain" size="21" maxlength="60" value="{$emailagain|default:''}" />
                </div>
            </fieldset>

            {if $showProps}
            {modfunc modname=$profileModName type='form' func='edit' dynadata=$reginfo.dynadata}
            {/if}

            <fieldset>
                <legend>{gt text="Check your entries and submit the modified registration"}</legend>
                <p id="users_checkmessage" class="z-sub">{gt text="Notice: When you are ready, click on 'Check your entries' to have your entries checked. When your entries are OK, click on 'Submit modifications' to continue."}</p>
                <p id="users_validmessage" class="z-hide z-sub">{gt text="Your entries seem to be OK. Please click on 'Submit modifications' when you are ready to continue."}</p>
                <div class="z-formbuttons z-buttons">
                    {button id='submitchanges' type='submit' src='button_ok.gif' set='icons/extrasmall' __alt='Submit new user' __title='Submit modifications' __text='Submit modifications'}
                    {button id='checkuserajax' type='button' class='z-hide' src='help.gif' set='icons/extrasmall' __alt='Check your entries' __title='Check your entries' __text='Check your entries'}
                    <a href="{$cancelurl|safetext}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
                    {img id='ajax_indicator' style='display: none;' modname='core' set='ajax' src='indicator_circle.gif' alt=''}
                </div>
            </fieldset>
        </div>
    </form>
</div>
