{strip}{gt text='New account registration' assign='templatetitle'}
{/strip}
{include file="User/menu.tpl"}

<h3>{gt text="Sorry! New user registration is currently disabled."}</h3>
<div class="alert alert-warning">
    {$modvars.ZikulaUsersModule.reg_noregreasons|safetext}
</div>
