{ajaxheader modname='Mailer' filename='mailer_admin_testconfig.js' noscriptaculous=true effects=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="mail" size="small"}
    <h3>{gt text="Test current settings"}</h3>
</div>

{form cssClass='z-form'}
    <div>
        {formvalidationsummary}
        <fieldset>
            <legend>{gt text="Settings test"}</legend>
            <div class="z-formrow">
                <span class="z-label">{gt text="Sender's name"}</span>
                <span>{$modvars.ZConfig.sitename}</span>
            </div>
            <div class="z-formrow">
                <span class="z-label">{gt text="Sender's e-mail address"}</span>
                <span>{$modvars.ZConfig.adminmail}</span>
            </div>
            <div class="z-formrow">
                    {formlabel for='toname' __text="Recipient's name" mandatorysym=true}
                    {formtextinput id='toname' size=30 maxLength=50 mandatory=true}
            </div>
            <div class="z-formrow">
                {formlabel for='toaddress' __text="Recipient's e-mail address" mandatorysym=true}
                {formemailinput id='toaddress' size=30 maxLength=50 mandatory=true}
            </div>
            <div class="z-formrow">
                {formlabel for='subject' __text="Subject" mandatorysym=true}
                {formtextinput id='subject' size=30 maxLength=50 mandatory=true}
            </div>
            <div class="z-formrow">
                <span class="z-label">
                    {gt text="Message Type"}
                    <span class="z-form-mandatory-flag">*</span>
                </span>
                <div id="message_type">
                    {formradiobutton id='mailer_text' dataField='msgtype' value='text' mandatory=true}
                    {formlabel for='mailer_text' __text='Plain-text message'}
                    {formradiobutton id='mailer_html' dataField='msgtype' value='html' mandatory=true}
                    {formlabel for='mailer_html' __text='HTML-formatted message'}
                    {formradiobutton id='mailer_multipart' dataField='msgtype' value='multipart' mandatory=true}
                    {formlabel for='mailer_multipart' __text='Multi-part message'}
                </div>
            </div>
            <div id="mailer_body_div" class="z-formrow">
                {formlabel for='mailer_body' __text='HTML-formatted message'}
                {formtextinput id='mailer_body' textMode='multiline' cols=50 rows=10}
            </div>
            <div id="mailer_textbody_div" class="z-formrow">
                {formlabel for='mailer_textbody' __text='Plain-text message'}
                {formtextinput id='mailer_textbody' textMode='multiline' cols=50 rows=10}
            </div>
        </fieldset>

        {notifydisplayhooks eventname='mailer.ui_hooks.htmlmail.form_edit' id=null}

        <div class="z-buttons z-formbuttons">
            {formbutton class='z-bt-ok' commandName='save' __text='Test settings now'}
            {formbutton class='z-bt-cancel' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}