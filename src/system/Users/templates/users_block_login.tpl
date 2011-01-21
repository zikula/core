{ajaxheader modname='Users' filename='users_loginblock.js'}
<div class="users_block_box">{insert name='generateauthkey' module='Users' assign='authkey'}
    {if (isset($authmodule) && $authmodule)}{modfunc modname=$authmodule type='auth' func='loginBlockFields' assign='loginblockfields'}{/if}
    <form id="users_block_loginform" class="z-form z-linear{if (empty($loginblockfields) || !$loginblockfields)} z-hide{/if}" action="{modurl modname="Users" type="user" func="login"}" method="post">
            <input type="hidden" name="url" value="{$returnurl|safetext}" />
            <input type="hidden" id="users_authid" name="authid" value="{$authkey}" />
            <input type="hidden" id="users_authmodule" name="authmodule" value="{$authmodule|default:'false'}" />
        {if (!empty($loginblockfields) && $loginblockfields)}{$loginblockfields}{/if}
        {if $seclevel != 'High'}<div class="z-formrow">
            <div><input id="loginblock_rememberme" type="checkbox" value="1" name="rememberme" /><label for="loginblock_rememberme">{gt text="Remember me" domain='zikula'}</label></div>
        </div>{/if}
        <div class="z-buttons z-right">
            <input class="z-bt-ok z-bt-small" type="submit" value="{gt text="Log in" domain='zikula'}" />
        </div>
    </form>
    <div id="loginblock_no_loginblockfields"{if (empty($authmodule) || !$authmodule) || (!empty($authmodule) && $authmodule && !empty($loginblockfields) && $loginblockfields)} class="z-hide"{/if}>
        <h5>{$authmodule}</h5>
        <p class="z-errormsg">
            {gt text='This log-in option is not available right now.' domain='zikula'}
            {if count($authmodules) > 1}
            {gt text='Please choose another or contact the site administrator for assistance.' domain='zikula'}
            {else}
            {gt text='Please contact the site administrator for assistance.' domain='zikula'}
            {/if}
        </p>
    </div>
    {if (count($authmodules) > 1)}<h5 id="loginblock_h5_authmodule"{if (empty($authmodule) || !$authmodule)} class="z-hide"{/if}>{gt text="Or instead, login with your..." domain='zikula'}</h5>
    <h5 id="loginblock_h5_no_authmodule"{if (!empty($authmodule) && $authmodule)} class="z-hide"{/if}>{gt text="Login with your..." domain='zikula'}</h5>
    {foreach from=$authmodules key='cur_authmodule_name' item='cur_authmodule' name='cur_authmodule'}{modfunc modname=$cur_authmodule_name type='auth' func='loginBlockIcon' assign='loginblockicon'}{if $loginblockicon}{$loginblockicon}{else}<a id="users_block_loginwith_{$cur_authmodule_name}" class="users_block_loginwith" href="{modurl modname='Users' func='loginScreen'}">{$cur_authmodule_name}</a>{if !$smarty.foreach.cur_authmodule.last}<br />{/if}{/if}{/foreach}{/if}

    <h5>{gt text="Do you need to..." domain='zikula'}</h5>
    {if $allowregistration}<a class="user-icon-adduser" style="display:block;" href="{modurl modname='Users' func='register'}">{gt text="Create an account?" domain='zikula'}</a>
    {/if}<a class="user-icon-lostusername" style="display:block;" href="{modurl modname='Users' func='lostpwduname'}">{gt text="Recover your account information?" domain='zikula'}</a>
</div>
{* zdebug checkpermission=false *}
