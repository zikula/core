{* TODO - handle re-display of display hooks when AJAX changes log-in method. For now, disable AJAX switching of login method and use URL fallback. *}
{* ajaxheader modname='Users' filename='Zikula.Users.Login.js' *}
{foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
{if ('Users' != $authentication_method.modname)}
    {ajaxheader modname=$authentication_method.modname filename=$authentication_method.modname|cat:'.Login.js'}
{/if}
{/foreach}
{gt text='User log-in' assign='templatetitle'}
{modulelinks modname='Users' type='user'}
{include file='users_user_menu.tpl'}
{if (count($authentication_method_display_order) > 1)}
<div>
    <h5 id="users_login_h5_no_authentication_method"{if !empty($selected_authentication_method)} class="z-hide"{/if}>{gt text="Choose how you would like to log in by clicking on one of the following..."}</h5>
    <h5 id="users_login_h5_authentication_method"{if empty($selected_authentication_method)} class="z-hide"{/if}>{gt text="Log in below, or change how you would like to log in by clicking on one of the following..."}</h5>
    <h5 id="users_login_h5" class="z-hide"></h5>
    <div class="authentication_select_method_bigbutton">
    {modurl modname='Users' type='user' func='login' assign='form_action'}
    {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
        {if $smarty.foreach.authentication_method_display_order.iteration == 6}
            </div>
            <div class="authentication_select_method_smallbutton z-clearer">
        {/if}
        {authentication_method_selector form_type='loginscreen' form_action=$form_action authentication_method=$authentication_method selected_authentication_method=$selected_authentication_method}
    {/foreach}
    </div>
</div>
{/if}

{if !empty($selected_authentication_method)}
    {login_form_fields form_type='loginscreen' authentication_method=$selected_authentication_method assign='login_form_fields'}
{/if}
<form id="users_login_login_form" class="z-form z-gap z-clearer{if !isset($login_form_fields) || empty($login_form_fields) || !isset($selected_authentication_method) || empty($selected_authentication_method)} z-hide{/if}" action="{modurl modname="Users" type="user" func="login"}" method="post">
    <div>
        <input id="users_login_selected_authentication_module" type="hidden" name="authentication_method[modname]" value="{$selected_authentication_method.modname|safetext|default:''}" />
        <input id="users_login_selected_authentication_method" type="hidden" name="authentication_method[method]" value="{$selected_authentication_method.method|safetext|default:''}" />
        <input id="users_login_returnpage" type="hidden" name="returnpage" value="{$returnpage|safetext}" />
        <input id="users_login_csrftoken" type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input id="users_login_event_type" type="hidden" name="event_type" value="login_screen" />
        {if ($modvars.ZConfig.seclevel|lower == 'high')}
        <input id="users_login_rememberme" type="hidden" name="rememberme" value="0" />
        {/if}
        <fieldset>
            <div id="users_login_fields">
                {$login_form_fields}
            </div>
            {if ($modvars.ZConfig.seclevel|lower != 'high')}
            <div class="z-formrow">
                <span class="z-formlist">
                    <input id="users_login_rememberme" type="checkbox" name="rememberme" value="1" />
                    <label for="users_login_rememberme">{gt text="Keep me logged in on this computer"}</label>
                </span>
            </div>
            {/if}
        </fieldset>

        {if isset($user_obj) && !empty($user_obj)}
            {notifyevent eventname='module.users.ui.form_edit.login_screen' id=$user_obj.uid eventsubject=$user_obj assign='eventData'}
        {else}
            {notifyevent eventname='module.users.ui.form_edit.login_screen' assign='eventData'}
        {/if}

        {foreach item='eventDisplay' from=$eventData}
            {$eventDisplay}
        {/foreach}
            
        {if isset($user_obj) && !empty($user_obj)}
            {notifydisplayhooks eventname='users.ui_hooks.login_block.form_edit' id=$user_obj.uid}
        {else}
            {notifydisplayhooks eventname='users.ui_hooks.login_block.form_edit' id=null}
        {/if}

        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Log in' __title='Log in' __text='Log in'}
        </div>
    </div>
</form>
<div id="users_login_waiting" class="z-form z-clearer z-gap z-hide">
    <fieldset>
        <p class="z-center z-gap">{img modname='core' set='ajax' src='large_fine_white.gif'}</p>
    </fieldset>
</div>
<div id="users_login_no_loginformfields" class="z-clearer z-gap{if (isset($login_form_fields) && !empty($login_form_fields)) || !isset($selected_authentication_method) || empty($selected_authentication_method)} z-hide{/if}">
    <h5>{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.modname|safetext|default:''}{/if}</h5>
    <p class="z-errormsg">
        {gt text='The log-in option you chose is not available at the moment.'}
        {if count($authentication_method_display_order) > 1}
        {gt text='Please choose another or contact the site administrator for assistance.'}
        {else}
        {gt text='Please contact the site administrator for assistance.'}
        {/if}
    </p>
</div>
<script type="text/javascript" language="JavaScript">
document.getElementById("users_login_login_id").focus();
</script>
