{adminheader}
<h3>
    <span class="fa fa-envelope"></span>
    {gt text='Test current settings'}
</h3>

<div class="alert alert-info">
    <h4>{gt text='Current settings from'} <code>dynamic/generated.yml</code>:</h4>
    {$swiftmailerHtml}
</div>
{form cssClass='form-horizontal'}
{formvalidationsummary}
    <fieldset>
        <legend>{gt text='Settings test'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text="Sender's name"}</label>
            <div class="col-sm-9">
                <div class="well well-sm">
                    {$modvars.ZConfig.sitename}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text="Sender's e-mail address"}</label>
            <div class="col-sm-9">
                <div class="well well-sm">
                    {$modvars.ZConfig.adminmail}
                </div>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass='col-sm-3 control-label' for='toname' __text="Recipient's name" mandatorysym=true}
            <div class="col-sm-9">
                {formtextinput id='toname' size=30 maxLength=50 mandatory=true cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass='col-sm-3 control-label' for='toaddress' __text="Recipient's e-mail address" mandatorysym=true}
            <div class="col-sm-9">
                {formemailinput id='toaddress' size=30 maxLength=50 mandatory=true cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass='col-sm-3 control-label' for='subject' __text='Subject' mandatorysym=true}
            <div class="col-sm-9">
                {formtextinput id='subject' size=30 maxLength=50 mandatory=true cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">
                {gt text='Message Type'}
                <span class="required"></span>
            </label>
            <div id="message_type" class="col-sm-9">
                {formradiobutton id='mailer_text' dataField='msgtype' value='text' mandatory=true}
                {formlabel for='mailer_text' __text='Plain-text message'}
                {formradiobutton id='mailer_html' dataField='msgtype' value='html' mandatory=true}
                {formlabel for='mailer_html' __text='HTML-formatted message'}
                {formradiobutton id='mailer_multipart' dataField='msgtype' value='multipart' mandatory=true}
                {formlabel for='mailer_multipart' __text='Multi-part message'}
            </div>
        </div>
        <div class="form-group" data-switch="msgtype" data-switch-value="html,multipart">
            {formlabel cssClass="col-sm-3 control-label" for='mailer_body' __text='HTML-formatted message'}
            <div class="col-sm-9">
                {formtextinput cssClass="form-control" id='mailer_body' textMode='multiline' cols=50 rows=10}
            </div>
        </div>
        <div class="form-group" data-switch="msgtype" data-switch-value="text,multipart">
            {formlabel cssClass="col-sm-3 control-label" for='mailer_textbody' __text='Plain-text message'}
            <div class="col-sm-9">
                {formtextinput cssClass="form-control" id='mailer_textbody' textMode='multiline' cols=50 rows=10}
            </div>
        </div>
    </fieldset>

    {notifydisplayhooks eventname='mailer.ui_hooks.htmlmail.form_edit' id=null}

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            {formbutton class='btn btn-success' commandName='save' __text='Send test email now'}
            {formbutton class='btn btn-danger' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}
