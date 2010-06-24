<h2>{gt text="Log-in for existing installation"}</h2>
<p><strong>{gt text="An existing Zikula installation has been found. Please log-in as administrator before proceeding further."}</strong></p>
{if $loginstate}
<div class="z-errormsg">
    {if $loginstate == 'failed'}{gt text="Error! Could not log you in. Please check your user name and password then try again."}
    {elseif $loginstate == 'notadmin'}{gt text="Error! The user name you entered in order to log-in does not have administration rights."}{/if}
</div>
{/if}
<form class="z-form" action="install.php?lang={$lang}" method="post">
    <div>
        <input type="hidden" name="action" value="login" />
        <input type="hidden" name="locale" value="{$locale}" />
        <fieldset>
            <legend>{gt text="Log-in"}</legend>
            <div class="z-formrow">
                <label for="loginuser">{gt text="Administrator's user name"}</label>
                <input type="text" name="loginuser" id="loginuser" maxlength="80" value="{$loginuser}" />
            </div>
            <div class="z-formrow">
                <label for="loginpassword">{gt text="Password"}</label>
                <input type="password" name="loginpassword" id="loginpassword" maxlength="80" value="{$loginpassword}" />
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Next"}" class="z-btblue" />
        </div>
    </div>
</form>
