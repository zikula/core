<form id="users_block_loginform" class="z-form" action="{modurl modname="Users" type="user" func="login"}" method="post">
    <div>
        <input type="hidden" name="url" value="{$returnurl|safetext}" />
        <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Users"}" />
        <input id="users_authmodule" type="hidden" name="authmodule" value="{$authmodule}" />

        <fieldset>
            {modfunc modname=$authmodule type='auth' func='loginBlockFields' authinfo=$authinfo assign='loginblockfields'}
            {if $loginblockfields}
            {$loginblockfields}

            {if $seclevel != 'High'}
            <input id="loginblock_rememberme" type="checkbox" value="1" name="rememberme" />
            <label for="loginblock_rememberme">{gt text="Remember me" domain='zikula'}</label>
            {/if}
            <div class="z-buttons">{button src='button_ok.gif' set='icons/extrasmall' __alt='Log in' __title='Log in' __text='Log in'}</div>
            {else}
            <legend>{$authmodule}</legend>
            <p class="z-errormsg">{gt text='This log-in option is not available right now.' domain='zikula'} {if count($authmodules) > 1}{gt text='Please choose another or contact the site administrator for assistance.' domain='zikula'}{else}{gt text='Please contact the site administrator for assistance.' domain='zikula'}{/if}</p>
            {/if}
        </fieldset>
        {if (count($authmodules) > 1)}
        <fieldset>
            <legend>{gt text="Login with your..." domain='zikula'}</legend>
            <div>{foreach from=$authmodules key='cur_authmodule_name' item='cur_authmodule'}
            {modfunc modname=$cur_authmodule_name type='auth' func='loginBlockIcon' assign='loginblockicon'}{if $loginblockicon}{$loginblockicon}{else}<a id="users_block_loginwith_{$cur_authmodule_name}" class="users_block_loginwith" href="{modurl modname='Users' func='loginScreen'}">{$cur_authmodule_name}</a>{/if}
            {/foreach}</div>
        </fieldset>
        {/if}
        <fieldset>
            <legend></legend>
            {if $allowregistration}<div><a href="{modurl modname='Users' func='register'}">{img modname='core' set='icons' src='extrasmall/add_user.gif' alt='' title=''} {gt text="New account" domain='zikula'}</a></div>{/if}
            <div><a href="{modurl modname='Users' func='lostpwduname'}">{img modname='core' set='icons' src='extrasmall/lostusername.png' alt='' title=''} {gt text="Login problems?" domain='zikula'}</a></div>
        </fieldset>
    </div>
</form>
{zdebug}