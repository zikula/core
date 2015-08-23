{strip}{gt text='New account registration' assign='templatetitle'}
{pagesetvar name='title' value=$templatetitle}
{foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
{if ('ZikulaUsersModule' != $authentication_method.modname)}
    {ajaxheader modname=$authentication_method.modname filename=$authentication_method.modname|cat:'.Login.js'}
{/if}
{/foreach}
{/strip}
<h2>{$templatetitle}</h2>

{insert name="getstatusmsg"}

<div>
    <h5 id="users_login_h5_no_authentication_method"{if !empty($selected_authentication_method)} class="hide"{/if}>{gt text="Choose how you would like to log in."}</h5>
    <h5 id="users_login_h5_authentication_method"{if empty($selected_authentication_method)} class="hide"{/if}>{gt text="Log in below, or change how you would like to log in."}</h5>
    {route name='zikulausersmodule_user_register' assign='form_action'}
    <div class="alert alert-info">
        {gt text='If you prefer, you can create an account and password for use only with this site by clicking below...'}
        {gt text='Click on one of the following to log into this site using that service...'}
    </div>
    <div class="authentication_select_method_bigbutton">
    {foreach from=$authentication_method_display_order item='authentication_method' name='authentication_method_display_order'}
        {authentication_method_selector form_type='registration' form_action=$form_action authentication_method=$authentication_method selected_authentication_method=$selected_authentication_method}
    {/foreach}
    </div>
</div>

{if !empty($selected_authentication_method)}
    {login_form_fields form_type='registration' authentication_method=$selected_authentication_method assign='login_form_fields'}
{/if}
<form id="users_login_login_form" class="z-form gap z-clearer{if !isset($login_form_fields) || empty($login_form_fields) || !isset($selected_authentication_method) || empty($selected_authentication_method)} hide{/if}" action="{route name='zikulausersmodule_user_register'}" method="post">
    <div>
        <input id="users_login_selected_authentication_module" type="hidden" name="authentication_method[modname]" value="{$selected_authentication_method.modname|default:''}" />
        <input id="users_login_selected_authentication_method" type="hidden" name="authentication_method[method]" value="{$selected_authentication_method.method|default:''}" />
        <input id="users_login_registration_authentication_info" type="hidden" name="registration_authentication_info" value="1" />
        <input id="users_login_csrftoken" type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <div id="users_login_fields">
                {if isset($login_form_fields) && !empty($login_form_fields)}
                {$login_form_fields}
                {/if}
            </div>
        </fieldset>

        <div class="form-group">
            <button class="btn btn-success col-sm-offset-3" title="{gt text='Continue registration'}">
                <i class="fa fa-arrow-right"></i>
                {gt text='Continue registration'}
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
        {gt text='The registration option you chose is not available at the moment.'}
        {if count($authentication_method_display_order) > 1}
        {gt text='Please choose another or contact the site administrator for assistance.'}
        {else}
        {gt text='Please contact the site administrator for assistance.'}
        {/if}
    </p>
</div>
