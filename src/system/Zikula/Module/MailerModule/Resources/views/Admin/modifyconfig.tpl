{adminheader}
<h3>
    <span class="icon-wrench"></span>
    {gt text="Settings"}
</h3>

{form cssClass='form-horizontal'}
    {formvalidationsummary}
    <fieldset>
        <legend>{gt text="General settings"}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='mailertype' __text='Mail transport' mandatorysym=true}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='mailertype' mandatory=true}
            </div>
        </div>
        <div class="form-group">
            {charset assign=defaultcharset}
            {formlabel cssClass="col-lg-3 control-label" for='charset' __text='Character set' mandatorysym=true}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='charset' size=10 maxLength=20 mandatory=true}
                <p class="help-block sub">{gt text="Default: '%s'" tag1=$defaultcharset}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='encoding' __text="Encoding" mandatorysym=true}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='encoding' mandatory=true}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='8bit'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='html' __text='HTML-formatted messages'}
            <div class="col-lg-9">
                {formcheckbox id='html'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='wordwrap' __text='Word wrap' mandatorysym=true}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='wordwrap' size=3 maxLength=3 mandatory=true}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='50'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='msmailheaders' __text='Use Microsoft mail client headers'}
            <div class="col-lg-9">
                {formcheckbox id='msmailheaders'}
            </div>
        </div>
    </fieldset>

    <fieldset data-switch="mailertype[]" data-switch-value="2">
        <legend>{gt text="'Sendmail' settings"}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='sendmailpath' __text="Path to 'Sendmail'"}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='sendmailpath' size=50 maxLength=255}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='/usr/sbin/sendmail'}</p>
            </div>
        </div>
    </fieldset>

    <fieldset data-switch="mailertype[]" data-switch-value="4">
        <legend>{gt text="SMTP settings"}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='smtpserver' __text='SMTP server'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='smtpserver' size=30 maxLength=255}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='localhost'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='smtpport' __text='SMTP port'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='smtpport' size=5 maxLength=5}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='25'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='smtpsecuremethod' __text='SMTP Security Method'}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='smtpsecuremethod'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='smtptimeout' __text='SMTP time-out'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='smtptimeout' size=5 maxLength=5}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='10 seconds'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='smtpauth' __text='Enable SMTP authentication'}
            <div class="col-lg-9">
                {formcheckbox id='smtpauth'}
            </div>
        </div>
        <div data-switch="smtpauth" data-switch-value="1">
            <div class="form-group">
                {formlabel cssClass="col-lg-3 control-label" for='smtpusername' __text='SMTP user name'}
                <div class="col-lg-9">
                    {formtextinput cssClass="form-control" id='smtpusername' size=30 maxLength=50}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3 control-label" for='smtppassword' __text='SMTP password'}
                <div class="col-lg-9">
                    {formtextinput cssClass="form-control" id='smtppassword' textMode='password' size=30 maxLength=50}
                </div>
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class='btn btn-success' commandName='save' __text='Save'}
            {formbutton class='btn btn-danger' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}