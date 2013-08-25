<div class="z-formrow">
    <label for="users_loginblock_login_id">
        {if $authentication_method == 'email'}
            {gt text='Email address'}
        {elseif $authentication_method == 'uname'}
            {gt text='User name'}
        {elseif $authentication_method == 'unameoremail'}
            {gt text='User name or e-mail address'}
        {/if}
    </label>
    <input id="users_loginblock_login_id" type="text" name="authentication_info[login_id]" maxlength="64" value="" />
</div>

<div class="z-formrow">
    <label for="users_loginblock_pass">{gt text="Password"}</label>
    <input id="users_loginblock_pass" type="password" name="authentication_info[pass]" maxlength="25" />
</div>