{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text="Settings"}
</h3>
{* http://swiftmailer.org/docs/sending.html *}
{php}$works = function_exists('proc_open');$this->assign('works',$works);{/php}
{if !$works}
    <div class='alert alert-danger'>
        <h3>{gt text='WARNING'}</h3>
        <p>{gt text='The PHP functions, %s, that support SMPT and SENDMAIL do not appear to be functioning on this PHP installation. Please compile them and restart your server.', tag1='<code>proc_*</code>'}</p>
    </div>
{/if}
<div class="alert alert-info">
    <p>{gt text='Mailer relies on %s' tag1='<a href="http://symfony.com/doc/current/reference/configuration/swiftmailer.html"><i class="fa fa-external-link"></i> SwiftMailer configuration</a>'}</p>
</div>

{form cssClass='form-horizontal'}
    {formvalidationsummary}
    <fieldset>
        <legend>{gt text="General settings"}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='transport' __text='Mail transport' mandatorysym=true}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='transport' mandatory=true}
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
                <div class="checkbox">
                    {formcheckbox id='html'}
                </div>
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
            {formlabel cssClass="col-lg-3 control-label" for='enableLogging' __text='Enable logging of sent mail'}
            <div class="col-lg-9">
                <div class="checkbox">
                    {formcheckbox id='enableLogging'}
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset data-switch="transport[]" data-switch-value="smtp">
        <legend>{gt text="SMTP settings"}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='host' __text='SMTP host server'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='host' size=30 maxLength=255}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='localhost'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='port' __text='SMTP port'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='port' size=5 maxLength=5}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='25'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='encryption' __text='SMTP encryption method'}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='encryption'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='auth_mode' __text='SMTP authentication type'}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='auth_mode'}
            </div>
        </div>
        <div data-switch="auth_mode[]" data-switch-value="login">
            <div class="form-group">
                {formlabel cssClass="col-lg-3 control-label" for='username' __text='SMTP user name'}
                <div class="col-lg-9">
                    {formtextinput cssClass="form-control" id='username' size=30 maxLength=50}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3 control-label" for='password' __text='SMTP password'}
                <div class="col-lg-9">
                    {formtextinput cssClass="form-control" id='password' textMode='password' size=30 maxLength=50}
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset data-switch="transport[]" data-switch-value="gmail">
        <legend>{gt text="Gmail settings"}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='username' __text='Gmail user name'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='username' size=30 maxLength=50}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for='password' __text='Gmail password'}
            <div class="col-lg-9">
                {formtextinput cssClass="form-control" id='password' textMode='password' size=30 maxLength=50}
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