<form id="users_loginblock_loginwith_Users" class="users_loginblock_loginwith" method="post" action="{homepage}" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" id="users_authid_Users" name="authid" value="{$authkey}" />
        <input type="hidden" id="loginwith_Users" name="loginwith" value="Users" />
        <input type="submit" id="users_loginblock_loginwith_button_Users" class="users_loginblock_loginwith_button" value="{if $loginviaoption == 1}{gt text='E-mail address' domain='zikula'}{else}{gt text='User name' domain='zikula'}{/if}" />
    </div>
</form>