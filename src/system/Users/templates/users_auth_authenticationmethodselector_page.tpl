<form id="users_login_select_authentication_form_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" class="users_login_select_authentication" method="post" action="{modurl modname='Users' type='user' func='login'}" enctype="application/x-www-form-urlencoded">
    <div>
        {if $modvars.ZConfig.anonymoussessions}
        <input type="hidden" id="users_login_select_authentication_{$authentication_method.modname|lower}_{$authentication_method.method|lower}_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        {/if}
        <input type="hidden" id="users_login_select_authentication_{$authentication_method.modname|lower}_{$authentication_method.method|lower}_module" name="authentication_method[modname]" value="{$authentication_method.modname}" />
        <input type="hidden" id="users_login_select_authentication_{$authentication_method.modname|lower}_{$authentication_method.method|lower}_method" name="authentication_method[method]" value="{$authentication_method.method}" />
        <input type="submit" id="users_login_select_authentication_{$authentication_method.modname|lower}_{$authentication_method.method|lower}_submit" class="users_login_select_authentication_button{if $is_selected} users_login_select_authentication_selected{/if}" name="submit" value="{if $authentication_method.method == 'email'}{gt text='E-mail address and password'}{else}{gt text='User name and password'}{/if}" />
    </div>
</form>