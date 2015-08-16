{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
</h3>
{* http://swiftmailer.org/docs/sending.html *}
{php}$works = function_exists('proc_open');$this->assign('works',$works);{/php}
{if !$works}
    <div class='alert alert-danger'>
        <h3>{gt text='WARNING'}</h3>
        <p>{gt text='The PHP functions, %s, that support SMTP and SENDMAIL do not appear to be functioning on this PHP installation. Please compile them and restart your server.', tag1='<code>proc_*</code>'}</p>
    </div>
{/if}
<div class="alert alert-info">
    <p>{gt text='Mailer relies on %s' tag1='<a href="http://symfony.com/doc/current/reference/configuration/swiftmailer.html"><i class="fa fa-external-link"></i> SwiftMailer configuration</a>'}</p>
</div>

{form cssClass='form-horizontal'}
    {formvalidationsummary}
    <fieldset>
        <legend>{gt text='General settings'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='transport' __text='Mail transport' mandatorysym=true}
            <div class="col-sm-9">
                {formdropdownlist id='transport' mandatory=true cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {charset assign=defaultcharset}
            {formlabel cssClass="col-sm-3 control-label" for='charset' __text='Character set' mandatorysym=true}
            <div class="col-sm-9">
                {formtextinput id='charset' size=10 maxLength=20 mandatory=true cssClass='form-control'}
                <p class="help-block sub">{gt text="Default: '%s'" tag1=$defaultcharset}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='encoding' __text="Encoding" mandatorysym=true}
            <div class="col-sm-9">
                {formdropdownlist id='encoding' mandatory=true cssClass='form-control'}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='8bit'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='html' __text='HTML-formatted messages'}
            <div class="col-sm-9">
                <div class="checkbox">
                    {formcheckbox id='html'}
                </div>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='wordwrap' __text='Word wrap' mandatorysym=true}
            <div class="col-sm-9">
                {formtextinput id='wordwrap' size=3 maxLength=3 mandatory=true cssClass='form-control'}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='50'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='enableLogging' __text='Enable logging of sent mail'}
            <div class="col-sm-9">
                <div class="checkbox">
                    {formcheckbox id='enableLogging'}
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset data-switch="transport[]" data-switch-value="smtp">
        <legend>{gt text='SMTP settings'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='host' __text='SMTP host server'}
            <div class="col-sm-9">
                {formtextinput id='host' size=30 maxLength=255 cssClass='form-control'}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='localhost'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='port' __text='SMTP port'}
            <div class="col-sm-9">
                {formtextinput id='port' size=5 maxLength=5 cssClass='form-control'}
                <p class="help-block sub">{gt text="Default: '%s'" tag1='25'}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='encryption' __text='SMTP encryption method'}
            <div class="col-sm-9">
                {formdropdownlist id='encryption' cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='auth_mode' __text='SMTP authentication type'}
            <div class="col-sm-9">
                {formdropdownlist id='auth_mode' cssClass='form-control'}
            </div>
        </div>
        <div data-switch="auth_mode[]" data-switch-value="login">
            <div class="form-group">
                {formlabel cssClass="col-sm-3 control-label" for='username' __text='SMTP user name'}
                <div class="col-sm-9">
                    {formtextinput id='username' size=30 maxLength=50 cssClass='form-control'}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-sm-3 control-label" for='password' __text='SMTP password'}
                <div class="col-sm-9">
                    {formtextinput id='password' textMode='password' size=30 maxLength=50 cssClass='form-control'}
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset data-switch="transport[]" data-switch-value="gmail">
        <legend>{gt text='Gmail settings'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='usernameGmail' __text='Gmail user name'}
            <div class="col-sm-9">
                {formtextinput id='usernameGmail' size=30 maxLength=50 cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-sm-3 control-label" for='passwordGmail' __text='Gmail password'}
            <div class="col-sm-9">
                {formtextinput id='passwordGmail' textMode='password' size=30 maxLength=50 cssClass='form-control'}
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            {formbutton class='btn btn-success' commandName='save' __text='Save'}
            {formbutton class='btn btn-danger' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}