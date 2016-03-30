{strip}
{ajaxheader modname='ZikulaUsersModule' filename='Zikula.Users.LoginBlock.js'}
{foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
    {if ('ZikulaUsersModule' != $authentication_method.modname)}
        {ajaxheader modname=$authentication_method.modname filename=$authentication_method.modname|cat:'.LoginBlock.js'}
    {/if}
{/foreach}
{/strip}
<div class="users_loginblock_box">
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
    <form id="users_loginblock_login_form" class="{if !$show_login_form} hide{/if}" action="{route name='zikulausersmodule_user_login'}" method="post">
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
        <div id="users_loginblock_fields">
        {if !empty($login_form_fields)}
            {$login_form_fields}
        {/if}
        </div>
        {if $modvars.ZConfig.seclevel|lower != 'high'}
        <div class="checkbox" style="margin-bottom: 10px;">
            <label for="users_loginblock_rememberme">
                <input id="users_loginblock_rememberme" type="checkbox" name="rememberme" value="1" />
                {gt text="Keep me logged in on this computer"}
            </label>
        </div>
        {/if}

        {notifyevent eventname='module.users.ui.form_edit.login_block' assign='eventData'}
        {foreach item='eventDisplay' from=$eventData}
            {$eventDisplay}
        {/foreach}

        {notifydisplayhooks eventname='users.ui_hooks.login_block.form_edit' id=null}

        <button class="btn btn-success btn-sm btn-block" id="users_loginblock_submit" name="users_loginblock_submit" type="submit">
            <i class="fa fa-arrow-right"></i> {gt text="Log in"}
        </button>
    </form>
    <div id="users_loginblock_no_loginformfields"{if (!isset($selected_authentication_method) || !$selected_authentication_method) || (isset($selected_authentication_method) && $selected_authentication_method && isset($login_form_fields) && $login_form_fields)} class="hide"{/if}>
        <h5>{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.modname|safetext|default:''}{/if}</h5>
        <p class="alert alert-danger">
            {gt text='The log-in option you chose is not available at the moment.'}
            {if count($authentication_method_display_order) > 1}
            {gt text='Please choose another or contact the site administrator for assistance.'}
            {else}
            {gt text='Please contact the site administrator for assistance.'}
            {/if}
        </p>
    </div>
    <div id="users_loginblock_authentication_method_selectors">
    {if (count($authentication_method_display_order) > 1)}
        <h5 id="users_loginblock_h5_authentication_method"{if (!isset($selected_authentication_method) || !$selected_authentication_method)} class="hide"{/if}>{gt text="Or instead, login with your..."}</h5>
        <h5 id="users_loginblock_h5_no_authentication_method"{if (isset($selected_authentication_method) && $selected_authentication_method)} class="hide"{/if}>{gt text="Login with your..."}</h5>
        {homepage assign='form_action'}
        {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
            {authentication_method_selector form_type='loginblock' form_action=$form_action authentication_method=$authentication_method selected_authentication_method=$selected_authentication_method}
        {/foreach}
    {/if}
    </div>
    <div class="z-clearfix"></div>

    <h5>{gt text="Do you need to..."}</h5>
    {if $modvars.ZikulaUsersModule.reg_allowreg}
    <div style="padding: 2px 0;"><i class="fa fa-user-plus fa-fw fa-lg text-success"></i> <a href="{route name='zikulausersmodule_registration_register'}">{gt text="Create an account?"}</a></div>
    {/if}
    <div style="padding: 2px 0;"><i class="fa fa-unlock-alt fa-fw fa-lg text-warning"></i> <a href="{route name='zikulausersmodule_user_lostpwduname'}">{gt text="Recover your account information?"}</a></div>
</div>
