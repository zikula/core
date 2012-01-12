{strip}{gt text='New account registration' assign='templatetitle'}
{/strip}
{include file="users_user_menu.tpl"}

<h3>{gt text="Sorry! New user registration is currently disabled."}</h3>
<div class="z-warningmsg">
    {$modvars.Users.reg_noregreasons|safetext}
</div>
