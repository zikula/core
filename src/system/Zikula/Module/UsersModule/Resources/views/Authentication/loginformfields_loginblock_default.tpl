<div class="form-group">
    <label class="col-lg-5 control-label" for="users_loginblock_login_id">
        {if $authentication_method == 'email'}
            {gt text='Email address'}
        {elseif $authentication_method == 'uname'}
            {gt text='User name'}
        {elseif $authentication_method == 'unameoremail'}
            {gt text='User name or e-mail address'}
        {/if}
    </label>
    <div class="col-lg-7">
        <input id="users_loginblock_login_id" type="text" name="authentication_info[login_id]" maxlength="64" value="" />
    </div>
</div>

<div class="form-group">
    <label class="col-lg-5 control-label" for="users_loginblock_pass">{gt text="Password"}</label>
     <div class="col-lg-7">
        <input id="users_loginblock_pass" type="password" name="authentication_info[pass]" maxlength="25" />
    </div>
</div>