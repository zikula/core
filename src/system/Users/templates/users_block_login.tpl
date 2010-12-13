<form id="users_block_loginform" class="z-form z-linear" action="{modurl modname="Users" type="user" func="login"}" method="post">
    <div>
        <input type="hidden" name="url" value="{$returnurl|safetext}" />
        <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Users"}" />
        <input type="hidden" name="authmodule" value="{$authmodule}" id="users_authmodule" />

        {if $authmodule}
        {modfunc modname=$authmodule type='auth' func='loginBlockFields' assign='loginblockfields'}
        <div class="users_block_box">
            {if $loginblockfields}
            {$loginblockfields}

            {if $seclevel != 'High'}
            <div class="z-formrow">
                <div>
                    <input id="loginblock_rememberme" type="checkbox" value="1" name="rememberme" />
                    <label for="loginblock_rememberme">{gt text="Remember me" domain='zikula'}</label>
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
        {/if}

        {if (count($authmodules) > 1)}
        <div class="users_block_box">
            {if $authmodule}
            <h5>{gt text="Or instead, login with your..." domain='zikula'}</h5>
            {else}
            <h5>{gt text="Login with your..." domain='zikula'}</h5>
            {/if}
            <div>
                {foreach from=$authmodules key='cur_authmodule_name' item='cur_authmodule' name='cur_authmodule'}
                {modfunc modname=$cur_authmodule_name type='auth' func='loginBlockIcon' assign='loginblockicon'}
                {if $loginblockicon}
                {$loginblockicon}
                {else}
                <a id="users_block_loginwith_{$cur_authmodule_name}" class="users_block_loginwith" href="{modurl modname='Users' func='loginScreen'}">{$cur_authmodule_name}</a>
                {/if}
                {if !$smarty.foreach.cur_authmodule.last}<br />{/if}
                {/foreach}
            </div>
        </div>
        {/if}

        <div class="users_block_box">
            <h5>{gt text="Miscellaneous" domain='zikula'}</h5>
            <ul id="user-block-login-tools">
                {if $allowregistration}
                <li><a class="user-icon-adduser" href="{modurl modname='Users' func='register'}">{gt text="New account" domain='zikula'}</a></li>
                {/if}
                <li><a class="user-icon-lostusername" href="{modurl modname='Users' func='lostpwduname'}">{gt text="Login problems?" domain='zikula'}</a></li>
            </ul>
        </div>
    </div>
</form>