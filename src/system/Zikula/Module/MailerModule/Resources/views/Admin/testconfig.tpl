{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="mail" size="small"}
    <h3>{gt text="Test current settings"}</h3>
</div>

{form cssClass='form-horizontal'}
{formvalidationsummary}
    <fieldset>
        <legend>{gt text="Settings test"}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text="Sender's name"}</label>
            <div class="col-lg-9">
                <div class="well well-sm">
                    {$modvars.ZConfig.sitename}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text="Sender's e-mail address"}</label>
            <div class="col-lg-9">
                <div class="well well-sm">
                    {$modvars.ZConfig.adminmail}
                </div>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='toname' __text="Recipient's name" mandatorysym=true}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='toname' size=30 maxLength=50 mandatory=true}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='toaddress' __text="Recipient's e-mail address" mandatorysym=true}
            <div class="col-lg-9">
                {formemailinput id='toaddress' cssClass="form-control" size=30 maxLength=50 mandatory=true}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='subject' __text="Subject" mandatorysym=true}
            <div class="col-lg-9">    
                {formtextinput cssClass="form-control" id='subject' size=30 maxLength=50 mandatory=true}
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">
                {gt text="Message Type"}
                <span class="z-form-mandatory-flag">*</span>
            </label>
            <div id="message_type" class="col-lg-9">
                {formradiobutton id='mailer_text' dataField='msgtype' value='text' mandatory=true}
                {formlabel for='mailer_text' __text='Plain-text message'}
                {formradiobutton id='mailer_html' dataField='msgtype' value='html' mandatory=true}
                {formlabel for='mailer_html' __text='HTML-formatted message'}
                {formradiobutton id='mailer_multipart' dataField='msgtype' value='multipart' mandatory=true}
                {formlabel for='mailer_multipart' __text='Multi-part message'}
            </div>
        </div>
        <div class="form-group" data-switch="msgtype" data-switch-value="html,multipart">
            {formlabel cssClass="col-lg-3 control-label" for='mailer_body' __text='HTML-formatted message'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='mailer_body' textMode='multiline' cols=50 rows=10}
            </div>
        </div>
        <div class="form-group" data-switch="msgtype" data-switch-value="text,multipart">
            {formlabel cssClass="col-lg-3 control-label" for='mailer_textbody' __text='Plain-text message'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='mailer_textbody' textMode='multiline' cols=50 rows=10}
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class='z-bt-ok' commandName='save' __text='Test settings now'}
            {formbutton class='z-bt-cancel' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}