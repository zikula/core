<h2>{gt text="Create administrator's user account"}</h2>
{if $passwordcomparefailed or $emailvalidatefailed or $urlvalidatefailed or $uservalidatefailed}
<div class="z-errormsg">
    {if $passwordcomparefailed}
    {gt text="Error! Could not create the administrator's user account. Please check your entries and try again."}
    {elseif $emailvalidatefailed}
    {gt text="Error! The administrator's e-mail address is not correctly formed. Please correct your entry and try again."}
    {elseif $uservalidatefailed}
    {gt text="Error! Spaces and special characters are not allowed in a user name. Please correct your entry and try again."}
    {/if}
</div>
{/if}
<form class="z-form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="action" value="selectmodule" />
        <input type="hidden" name="locale" value="{$locale}" />
        <input type="hidden" name="installtype" value="{$installtype}" />
        <fieldset>
            <legend>{gt text="Create administrator's account"}</legend>
            <div class="z-formrow">
                <label for="username">{gt text="User name"}</label>
                <input type="text" name="username" id="username" maxlength="80" value="{$username|default:'admin'}"{if $uservalidatefailed} class="validationfailed"{/if} />
            </div>
            <div class="z-formrow">
                <label for="password">{gt text="Password"}</label>
                <input type="password" name="password" id="password" maxlength="80" value="{$password}"{if $passwordcomparefailed} class="validationfailed"{/if} />
            </div>
            <div class="z-formrow">
                <label for="repeatpassword">{gt text="Password (repetition for verification)"}</label>
                <input type="password" name="repeatpassword" id="repeatpassword" maxlength="80" value="{$repeatpassword}"{if $passwordcomparefailed} class="validationfailed"{/if} />
            </div>
            <div class="z-formrow">
                <label for="email">{gt text="E-mail address"}</label>
                <input type="text" name="email" id="email" maxlength="80" value="{$email}"{if $emailvalidatefailed} class="validationfailed"{/if} />
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Next"}" class="z-btblue" />
        </div>
    </div>
</form>
