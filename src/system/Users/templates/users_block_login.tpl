{strip}
{ajaxheader modname='Users' filename='Zikula.Users.LoginBlock.js'}
{foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
    {if ('Users' != $authentication_method.modname)}
        {ajaxheader modname=$authentication_method.modname filename=$authentication_method.modname|cat:'.LoginBlock.js'}
    {/if}
{/foreach}
{/strip}<div class="users_loginblock_box">{strip}
    {assign var='show_login_form' value=false}
    {if (isset($selected_authentication_method) && $selected_authentication_method)}
        {login_form_fields form_type='loginblock' authentication_method=$selected_authentication_method assign='login_form_fields'}
        {if isset($login_form_fields) && $login_form_fields}
            {assign var='show_login_form' value=true}
        {/if}
    {/if}
    {/strip}<div id="users_loginblock_waiting" class="z-center z-hide">
        {img modname='core' set='ajax' src='indicator_circle.gif'}
    </div>
    <form id="users_loginblock_login_form" class="z-form z-linear{if !$show_login_form} z-hide{/if}" action="{modurl modname="Users" type="user" func="login"}" method="post">
        <div>
            <input type="hidden" id="users_loginblock_returnpage" name="returnpage" value="{$returnpage|safetext}" />
            <input type="hidden" id="users_loginblock_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
            <input id="users_login_event_type" type="hidden" name="event_type" value="login_block" />
            <input type="hidden" id="users_loginblock_selected_authentication_module" name="authentication_method[modname]" value="{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.modname|safetext|default:'false'}{/if}" />
            <input type="hidden" id="users_loginblock_selected_authentication_method" name="authentication_method[method]" value="{if isset($selected_authentication_method) && $selected_authentication_method}{$selected_authentication_method.method|safetext|default:'false'}{/if}" />
            {if ($modvars.ZConfig.seclevel|lower == 'high')}
            <input id="users_loginblock_rememberme" type="hidden" name="rememberme" value="0" />
            {/if}
            <div id="users_loginblock_fields">
            {if !empty($login_form_fields)}
                {$login_form_fields}
            {/if}
            </div>
            {if $modvars.ZConfig.seclevel|lower != 'high'}
            <div class="z-formrow z-clearer">
                <div>
                    <input id="users_loginblock_rememberme" type="checkbox" name="rememberme" value="1" />
                    <label for="users_loginblock_rememberme">{gt text="Keep me logged in on this computer"}</label>
                </div>
            </div>

            {notifyevent eventname='module.users.ui.form_edit.login_block' assign="eventData"}
            {foreach item='eventDisplay' from=$eventData}
                {$eventDisplay}
            {/foreach}
            
            {notifydisplayhooks eventname='users.ui_hooks.login_block.form_edit' id=null}

            {/if}
            <div class="z-buttons z-right">
                <input class="z-bt-ok z-bt-small" id="users_loginblock_submit" name="users_loginblock_submit" type="submit" value="{gt text="Log in"}" />
            </div>
        </div>
    </form>
    <div id="users_loginblock_no_loginformfields"{if (!isset($selected_authentication_method) || !$selected_authentication_method) || (isset($selected_authentication_method) && $selected_authentication_method && isset($login_form_fields) && $login_form_fields)} class="z-hide"{/if}>
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
    <div id="users_loginblock_authentication_method_selectors">
    {if (count($authentication_method_display_order) > 1)}
        <h5 id="users_loginblock_h5_authentication_method"{if (!isset($selected_authentication_method) || !$selected_authentication_method)} class="z-hide"{/if}>{gt text="Or instead, login with your..."}</h5>
        <h5 id="users_loginblock_h5_no_authentication_method"{if (isset($selected_authentication_method) && $selected_authentication_method)} class="z-hide"{/if}>{gt text="Login with your..."}</h5>
        {homepage assign='form_action'}
        {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
            {authentication_method_selector form_type='loginblock' form_action=$form_action authentication_method=$authentication_method selected_authentication_method=$selected_authentication_method}
        {/foreach}
    {/if}
    </div>

    <h5>{gt text="Do you need to..."}</h5>
    {if $modvars.Users.reg_allowreg}
    <a class="user-icon-adduser" style="display:block;" href="{modurl modname='Users' type='user' func='register'}">{gt text="Create an account?"}</a>
    {/if}
    <a class="user-icon-lostusername" style="display:block;" href="{modurl modname='Users' type='user' func='lostpwduname'}">{gt text="Recover your account information?"}</a>
</div>
