{strip}
    {ajaxheader modname='ZikulaUsersModule' filename='Zikula.Users.LoginBlock.js'}
    {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
        {if ('ZikulaUsersModule' != $authentication_method.modname)}
            {ajaxheader modname=$authentication_method.modname filename=$authentication_method.modname|cat:'.LoginBlock.js'}
        {/if}
    {/foreach}
{/strip}
<div>
    {strip}
        {assign var='show_login_form' value=false}
        {if (isset($selected_authentication_method) && $selected_authentication_method)}
            {login_form_fields form_type='loginblock' authentication_method=$selected_authentication_method assign='login_form_fields'}
            {if isset($login_form_fields) && $login_form_fields}
                {assign var='show_login_form' value=true}
            {/if}
        {/if}
    {/strip}
    <div id="users_loginblock_waiting" class="text-center hide">
        {img modname='core' set='ajax' src='indicator_circle.gif'}
    </div>
    <form id="users_loginblock_login_form" class="navbar-form navbar-right{if !$show_login_form} hide{/if}" action="{route name='zikulausersmodule_user_login'}" method="post">
        <div>
            <input type="hidden" id="users_loginblock_returnpage" name="returnpage" value="{$returnpage|safetext}" />
            <input type="hidden" id="users_loginblock_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
            <input type="hidden" id="users_loginblock_event_type" name="event_type" value="login_block" />
            <input type="hidden" id="users_loginblock_selected_authentication_module" name="authentication_method[modname]" value="{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.modname|safetext|default:'false'}{/if}" />
            <input type="hidden" id="users_loginblock_selected_authentication_method" name="authentication_method[method]" value="{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.method|safetext|default:'false'}{/if}" />
            {if ($modvars.ZConfig.seclevel|lower == 'high')}
                <input id="users_loginblock_rememberme" type="hidden" name="rememberme" value="0" />
            {/if}
        </div>
        <div id="users_loginblock_fields" style="display:inline;">
            {if !empty($login_form_fields)}
                {$login_form_fields}
            {/if}
        </div>

        {notifyevent eventname='module.users.ui.form_edit.login_block' assign='eventData'}
        {foreach item='eventDisplay' from=$eventData}
            {$eventDisplay}
        {/foreach}

        {notifydisplayhooks eventname='users.ui_hooks.login_block.form_edit' id=null}

        <button class="btn btn-success btn-sm" id="users_loginblock_submit" name="users_loginblock_submit" type="submit">
            <i class="fa fa-arrow-right"></i> {gt text="Log in"}
        </button>
    </form>
    <div id="users_loginblock_no_loginformfields"{if (!isset($selected_authentication_method) || !$selected_authentication_method) || (isset($selected_authentication_method) && $selected_authentication_method && isset($login_form_fields) && $login_form_fields)} class="hide"{/if}>
        <strong>{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.modname|safetext|default:''}{/if}</strong>
        <button type="button" class="btn btn-danger">
            {gt text='The log-in option you chose is not available at the moment.'}
            {if count($authentication_method_display_order) > 1}
                {gt text='Please choose another or contact the site administrator for assistance.'}
            {else}
                {gt text='Please contact the site administrator for assistance.'}
            {/if}
        </button>
    </div>
    <div id="users_loginblock_authentication_method_selectors">
        {if (count($authentication_method_display_order) > 1)}
            <strong id="users_loginblock_h5_authentication_method"{if (!isset($selected_authentication_method) || !$selected_authentication_method)} class="hide"{/if}>{gt text="Or instead, login with your..."}</strong>
            <strong id="users_loginblock_h5_no_authentication_method"{if (isset($selected_authentication_method) && $selected_authentication_method)} class="hide"{/if}>{gt text="Login with your..."}</strong>
            {homepage assign='form_action'}
            {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
                {authentication_method_selector form_type='loginblock' form_action=$form_action authentication_method=$authentication_method selected_authentication_method=$selected_authentication_method}
            {/foreach}
        {/if}
    </div>
</div>
