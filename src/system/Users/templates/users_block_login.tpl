<div class="users_block_box">
    {if $authmodule}
    <form id="users_block_loginform" class="z-form z-linear" action="{modurl modname="Users" type="user" func="login"}" method="post">
            <input type="hidden" name="url" value="{$returnurl|safetext}" />
            {insert name='generateauthkey' module='Users' assign='authkey'}<input type="hidden" name="authid" value="{$authkey}" />
            <input type="hidden" name="authmodule" value="{$authmodule}" id="users_authmodule" />
        {modfunc modname=$authmodule type='auth' func='loginBlockFields' assign='loginblockfields'}
        <div>
            {if $loginblockfields}
            {$loginblockfields}

            {if $seclevel != 'High'}
            <div class="z-formrow">
                <div>
                    <input id="loginblock_rememberme" type="checkbox" value="1" name="rememberme" /><label for="loginblock_rememberme">{gt text="Remember me" domain='zikula'}</label>
                </div>
            </div>
            {/if}
            <div class="z-buttons">
                <input class="z-bt-ok z-bt-small" type="submit" value="{gt text="Log in" domain='zikula'}" />
            </div>
            {else}
            <h5>{$authmodule}</h5>
            <p class="z-errormsg">
                {gt text='This log-in option is not available right now.' domain='zikula'}
                {if count($authmodules) > 1}
                {gt text='Please choose another or contact the site administrator for assistance.' domain='zikula'}
                {else}
                {gt text='Please contact the site administrator for assistance.' domain='zikula'}
                {/if}
            </p>
            {/if}
        </div>
    </form>
    {/if}

    {if (count($authmodules) > 1)}
    {if $authmodule}
    <h5>{gt text="Or instead, login with your..." domain='zikula'}</h5>
    {else}
    <h5>{gt text="Login with your..." domain='zikula'}</h5>
    {/if}
    {foreach from=$authmodules key='cur_authmodule_name' item='cur_authmodule' name='cur_authmodule'}
    {modfunc modname=$cur_authmodule_name type='auth' func='loginBlockIcon' assign='loginblockicon'}
    {if $loginblockicon}
    {$loginblockicon}
    {else}
    <a id="users_block_loginwith_{$cur_authmodule_name}" class="users_block_loginwith" href="{modurl modname='Users' func='loginScreen'}">{$cur_authmodule_name}</a>{if !$smarty.foreach.cur_authmodule.last}<br />{/if}
    {/if}
    {/foreach}
    {/if}

    <h5>{gt text="Do you need to..." domain='zikula'}</h5>
    {if $allowregistration}<a class="user-icon-adduser" style="display:block;" href="{modurl modname='Users' func='register'}">{gt text="Create an account?" domain='zikula'}</a>
    {/if}<a class="user-icon-lostusername" style="display:block;" href="{modurl modname='Users' func='lostpwduname'}">{gt text="Recover your account information?" domain='zikula'}</a>
</div>
