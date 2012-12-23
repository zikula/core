{ajaxheader modname='Mailer' filename='mailer_admin_modifyconfig.js' noscriptaculous=true effects=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

{form cssClass='z-form'}
    <div>
        {formvalidationsummary}
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                {formlabel for='mailertype' __text='Mail transport' mandatorysym=true}
                {formdropdownlist id='mailertype' mandatory=true}
            </div>
            <div class="z-formrow">
                {charset assign=defaultcharset}
                {formlabel for='charset' __text='Character set' mandatorysym=true}
                {formtextinput id='charset' size=10 maxLength=20 mandatory=true}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1=$defaultcharset}</p>
            </div>
            <div class="z-formrow">
                {formlabel for='encoding' __text="Encoding" mandatorysym=true}
                {formdropdownlist id='encoding' mandatory=true}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1='8bit'}</p>
            </div>
            <div class="z-formrow">
                {formlabel for='html' __text='HTML-formatted messages'}
                {formcheckbox id='html'}
            </div>
            <div class="z-formrow">
                {formlabel for='wordwrap' __text='Word wrap' mandatorysym=true}
                {formtextinput id='wordwrap' size=3 maxLength=3 mandatory=true}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1='50'}</p>
            </div>
            <div class="z-formrow">
                {formlabel for='msmailheaders' __text='Use Microsoft mail client headers'}
                {formcheckbox id='msmailheaders'}
            </div>
        </fieldset>

        <fieldset id="mailer_sendmailsettings">
            <legend>{gt text="'Sendmail' settings"}</legend>
            <div class="z-formrow">
                {formlabel for='sendmailpath' __text="Path to 'Sendmail'"}
                {formtextinput id='sendmailpath' size=50 maxLength=255}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1='/usr/sbin/sendmail'}</p>
            </div>
        </fieldset>

        <fieldset id="mailer_smtpsettings">
            <legend>{gt text="SMTP settings"}</legend>
            <div class="z-formrow">
                {formlabel for='smtpserver' __text='SMTP server'}
                {formtextinput id='smtpserver' size=30 maxLength=255}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1='localhost'}</p>
            </div>
            <div class="z-formrow">
                {formlabel for='smtpport' __text='SMTP port'}
                {formtextinput id='smtpport' size=5 maxLength=5}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1='25'}</p>
            </div>
            <div class="z-formrow">
                {formlabel for='smtpsecuremethod' __text='SMTP Security Method'}
                {formdropdownlist id='smtpsecuremethod'}
            </div>
            <div class="z-formrow">
                {formlabel for='smtptimeout' __text='SMTP time-out'}
                {formtextinput id='smtptimeout' size=5 maxLength=5}
                <p class="z-formnote z-sub">{gt text="Default: '%s'" tag1='10 seconds'}</p>
            </div>
            <div class="z-formrow">
                {formlabel for='smtpauth' __text='Enable SMTP authentication'}
                {formcheckbox id='smtpauth'}
            </div>
            <div id="mailer_smtp_authentication">
                <div class="z-formrow">
                    {formlabel for='smtpusername' __text='SMTP user name'}
                    {formtextinput id='smtpusername' size=30 maxLength=50}
                </div>
                <div class="z-formrow">
                    {formlabel for='smtppassword' __text='SMTP password'}
                    {formtextinput id='smtppassword' textMode='password' size=30 maxLength=50}
                </div>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {formbutton class='z-bt-ok' commandName='save' __text='Save'}
            {formbutton class='z-bt-cancel' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}