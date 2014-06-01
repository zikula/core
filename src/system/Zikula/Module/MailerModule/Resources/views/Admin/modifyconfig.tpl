{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text="Settings"}
</h3>

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
            {formlabel cssClass="col-lg-3 control-label" for='encryption' __text='SMTP Encryption Method'}
            <div class="col-lg-9">
                {formdropdownlist cssClass="form-control" id='encryption'}
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
            {formlabel cssClass="col-lg-3 control-label" for='auth_mode' __text='Enable SMTP authentication'}
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

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class='btn btn-success' commandName='save' __text='Save'}
            {formbutton class='btn btn-danger' commandName='cancel' __text='Cancel'}
        </div>
    </div>
{/form}
{adminfooter}