{include file="mailer_admin_menu.tpl"}
{ajaxheader modname=Mailer filename=mailer_admin_modifyconfig.js noscriptaculous=true effects=true}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="config" size="small"}</div>
    <h3>{gt text="Settings"}</h3>

    <form class="z-form" action="{modurl modname="Mailer" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
            <fieldset>
                <legend>{gt text="General settings"}</legend>
                <div class="z-formrow">
                    <label for="mailer_mailertype">{gt text="Mail transport"}</label>
                    <select id="mailer_mailertype" name="mailertype">{html_options options=$mailertypes selected=$mailertype}</select>
                </div>
                <div class="z-formrow">
                    {charset assign=defaultcharset}
                    <label for="mailer_charset">{gt text="Character set (default: '%s')" tag1=$defaultcharset}</label>
                    <input id="mailer_charset" name="charset" type="text" size="10" maxlength="20" value="{$charset|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="mailer_encoding">{gt text="Encoding (default: '8bit')"}</label>
                    <select id="mailer_encoding" name="encoding">
                        <option value="8bit"{if $encoding eq '8bit'} selected="selected"{/if}>8bit</option>
                        <option value="7bit"{if $encoding eq '7bit'} selected="selected"{/if}>7bit</option>
                        <option value="binary"{if $encoding eq 'binary'} selected="selected"{/if}>binary</option>
                        <option value="base64"{if $encoding eq 'base64'} selected="selected"{/if}>base64</option>
                        <option value="quoted-printable"{if $encoding eq 'quoted-printable'} selected="selected"{/if}>quoted-printable</option>
                    </select>
                </div>
                <div class="z-formrow">
                    <label for="mailer_html">{gt text="HTML-formatted messages"}</label>
                    <input id="mailer_html" type="checkbox" name="html" value="1"{if $html} checked="checked"{/if} />
                </div>
                <div class="z-formrow">
                    <label for="mailer_wordwrap">{gt text="Word wrap (default: 50)"}</label>
                    <input id="mailer_wordwrap" name="wordwrap" type="text" size="3" maxlength="3" value="{$wordwrap|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="mailer_msmailheaders">{gt text="Use Microsoft mail client headers"}</label>
                    {if $msmailheaders eq 1}
                    <input id="mailer_msmailheaders" name="msmailheaders" type="checkbox" value="1" checked="checked" />
                    {else}
                    <input id="mailer_msmailheaders" name="msmailheaders" type="checkbox" value="1" />
                    {/if}
                </div>
            </fieldset>
            <fieldset id="mailer_sendmailsettings">
                <legend>{gt text="'Sendmail' settings"}</legend>
                <div class="z-formrow">
                    <label for="mailer_sendmailpath">{gt text="Path to 'Sendmail'"}</label>
                    <input id="mailer_sendmailpath" name="sendmailpath" type="text" size="50" maxlength="50" value="{$sendmailpath|safetext}" />
                </div>
            </fieldset>
            <fieldset id="mailer_smtpsettings">
                <legend>{gt text="SMTP settings"}</legend>
                <div class="z-formrow">
                    <label for="mailer_smtpserver">{gt text="SMTP server (default: localhost)"}</label>
                    <input id="mailer_smtpserver" name="smtpserver" type="text" size="30" maxlength="50" value="{$smtpserver|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="mailer_smtpport">{gt text="SMTP port (default: 25)"}</label>
                    <input id="mailer_smtpport" name="smtpport" type="text" size="5" maxlength="5" value="{$smtpport|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="mailer_smtpsecuremethod">{gt text="SMTP Security Method"}</label>
                    <select id="mailer_smtpsecuremethod" name="smtpsecuremethod">
                        <option value=""{if $smtpsecuremethod eq ''} selected="selected"{/if}>None</option>
                        <option value="ssl"{if $smtpsecuremethod eq 'ssl'} selected="selected"{/if}>SSL</option>
                        <option value="tls"{if $smtpsecuremethod eq 'tls'} selected="selected"{/if}>TLS</option>
                    </select>
                </div>
                <div class="z-formrow">
                    <label for="mailer_smtptimeout">{gt text="SMTP time-out (default: 10 seconds)"}</label>
                    <input id="mailer_smtptimeout" name="smtptimeout" type="text" size="5" maxlength="5" value="{$smtptimeout|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="mailer_smtpauth">{gt text="Enable SMTP authentication"}</label>
                    {if $smtpauth eq 1}
                    <input id="mailer_smtpauth" name="smtpauth" type="checkbox" value="1" checked="checked" />
                    {else}
                    <input id="mailer_smtpauth" name="smtpauth" type="checkbox" value="1" />
                    {/if}
                </div>
                <div id="mailer_smtp_authentication">
                    <div class="z-formrow">
                        <label for="mailer_smtpusername">{gt text="SMTP user name"}</label>
                        <input id="mailer_smtpusername" name="smtpusername" type="text" size="30" maxlength="50" value="{$smtpusername|safetext}" />
                    </div>
                    <div class="z-formrow">
                        <label for="mailer_smtppassword">{gt text="SMTP password"}</label>
                        <input id="mailer_smtppassword" name="smtppassword" type="password" size="30" maxlength="30" value="{$smtppassword|safetext}" />
                    </div>
                </div>
            </fieldset>

            <div class="z-buttons z-formbuttons">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Mailer type=admin func=main}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
