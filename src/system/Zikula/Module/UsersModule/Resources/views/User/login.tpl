{* TODO - handle re-display of display hooks when AJAX changes log-in method. For now, disable AJAX switching of login method and use URL fallback. *}
{* ajaxheader modname='ZikulaUsersModule' filename='Zikula.Users.Login.js' *}
{foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
{if ('ZikulaUsersModule' != $authentication_method.modname)}
    {ajaxheader modname=$authentication_method.modname filename=$authentication_method.modname|cat:'.Login.js'}
{/if}
{/foreach}
{gt text='User log-in' assign='templatetitle'}
{modulelinks modname='ZikulaUsersModule' type='user'}
{include file='User/menu.tpl'}
{if (count($authentication_method_display_order) > 1)}
<div>
    <h5 id="users_login_h5_no_authentication_method"{if !empty($selected_authentication_method)} class="hide"{/if}>{gt text="Choose how you would like to log in by clicking on one of the following..."}</h5>
    <h5 id="users_login_h5_authentication_method"{if empty($selected_authentication_method)} class="hide"{/if}>{gt text="Log in below, or change how you would like to log in by clicking on one of the following..."}</h5>
    <h5 id="users_login_h5" class="hide"></h5>
    <div class="authentication_select_method_bigbutton">
    {modurl modname='ZikulaUsersModule' type='user' func='login' returnpage=$returnpage|urlencode assign='form_action'}
    {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
        {if $smarty.foreach.authentication_method_display_order.iteration == 6}
            </div>
            <div class="authentication_select_method_smallbutton z-clearer">
        {/if}
        {authentication_method_selector form_type='loginscreen' form_action=$form_action authentication_method=$authentication_method selected_authentication_method=$selected_authentication_method}
    {/foreach}
    </div>
</div>
<div class="clearfix" style="margin-bottom:20px"></div>
{/if}
{if !empty($selected_authentication_method)}
    {login_form_fields form_type='loginscreen' authentication_method=$selected_authentication_method assign='login_form_fields'}
{/if}
<form id="users_login_login_form" class="form-horizontal{if !isset($login_form_fields) || empty($login_form_fields) || !isset($selected_authentication_method) || empty($selected_authentication_method)} hide{/if}" action="{modurl modname="Users" type="user" func="login"}" method="post">
    <div>
        <input id="users_login_selected_authentication_module" type="hidden" name="authentication_method[modname]" value="{$selected_authentication_method.modname|default:''}" />
        <input id="users_login_selected_authentication_method" type="hidden" name="authentication_method[method]" value="{$selected_authentication_method.method|default:''}" />
        <input id="users_login_returnpage" type="hidden" name="returnpage" value="{$returnpage}" />
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
            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-9">
                    <input id="users_login_rememberme" type="checkbox" name="rememberme" value="1" />
                    <label for="users_login_rememberme">{gt text="Keep me logged in on this computer"}</label>
                </div>
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

        <div>
            <button class="btn btn-default" title="{gt text='Log in'}">
                {gt text='Log in'}
            </button>
        </div>
    </div>
</form>
<div id="users_login_waiting" class="z-form z-clearer gap hide">
    <fieldset>
        <p class="text-center gap">{img modname='core' set='ajax' src='large_fine_white.gif'}</p>
    </fieldset>
</div>
<div id="users_login_no_loginformfields" class="z-clearer gap{if (isset($login_form_fields) && !empty($login_form_fields)) || !isset($selected_authentication_method) || empty($selected_authentication_method)} hide{/if}">
    <h5>{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.modname|default:''}{/if}</h5>
    <p class="alert alert-danger">
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
