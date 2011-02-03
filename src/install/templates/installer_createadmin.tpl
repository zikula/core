{assign var="step" value=3}
<h2>{gt text="Create administrator's user account"}</h2>
{if $passwordcomparefailed or $emailvalidatefailed or $urlvalidatefailed or $uservalidatefailed or $badpassword}
<div class="z-errormsg">
    {if $uservalidatefailed}
    {gt text="Error! Usernames can only consist of a combination of letters, numbers and may only contain the symbols . and _"}
    {elseif $emailvalidatefailed}
    {gt text="Error! The administrator's e-mail address is not correctly formed. Please correct your entry and try again."}
    {elseif $passwordcomparefailed}
    {gt text="Error! Passwords do not match."}
    {elseif $badpassword}
    {gt text="Error! Passwords must be at least 7 characters long."}
    
    {/if}
</div>
{/if}
<form id="createadmin_form" class="z-form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="action" value="finish" />
        <input type="hidden" name="locale" value="{$locale}" />
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
        <div class="z-buttons z-center">
            <input type="submit" value="{gt text="Proceed with Installation"}" class="z-bt-ok" />
        </div>
    </div>
</form>
