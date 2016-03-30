{gt text='Log-in and registration' assign='templatetitle'}
{include file='User/menu.tpl'}

<p>{gt text="Please choose an action:"}</p>
<ul>
    <li><a href="{route name='zikulausersmodule_user_login'}">{gt text="Log in"}</a></li>

    {if $reg_allowreg}
    <li><a href="{route name='zikulausersmodule_registration_register'}">{gt text="Register new account"}</a></li>
    {else}
    <li>{gt text="Notice: New user registration is currently disabled."}<br />{gt text="Reasons"}:&nbsp;
    {$reg_noregreasons|safetext}</li>
    {/if}

    <li><a href="{route name='zikulausersmodule_user_lostpwduname'}">{gt text="Recover lost user name or password"}</a></li>
</ul>
