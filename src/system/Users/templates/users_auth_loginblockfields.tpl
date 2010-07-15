<legend>{if (count($authmodules) > 1)}{gt text='Web site account' domain='zikula'}{/if}</legend>
<div class="z-formrow">
    <label for="loginblock_authinfo_loginid">
        {if $loginviaoption == 1}
        {gt text="E-mail address" domain='zikula'}
        {else}
        {gt text="User name" domain='zikula'}
        {/if}
    </label>
    <input id="loginblock_authinfo_loginid" type="text" name="authinfo[loginid]" maxlength="64" value="" />
</div>

<div class="z-formrow">
    <label for="loginblock_authinfo_pass">{gt text="Password" domain='zikula'}</label>
    <input id="loginblock_authinfo_pass" type="password" name="authinfo[pass]" maxlength="20" />
</div>
