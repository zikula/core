<fieldset>
    <legend>{gt text='Account Information'}</legend>
    <div class="form-group">
        <label class="col-sm-3 control-label">{gt text='User name:'}</label>
        <div class="col-sm-9">
            <div class="form-control-static">
                {$reginfo.uname}
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{gt text='E-mail address:'}</label>
        <div class="col-sm-9">
            <div class="form-control-static">
                {if !empty($reginfo.email)}
                <a href="mailto:{$reginfo.email|urlencode}">{$reginfo.email|safetext}</a>
                {else}
                ---
                {/if}
            </div>
        </div>
    </div>
</fieldset>

{if !isset($reginfo.pass) || empty($reginfo.pass)}
<fieldset>
    <legend>{gt text='Log-in information'}</legend>
    <div class="alert alert-info">
        {gt text='Because a password is not set for this registration, the e-mail verification process cannot be skipped. It must be completed so that the user can establish a password before the user account is created.'}
    </div>
</fieldset>
{/if}

{notifyevent eventname='module.users.ui.display_view' eventsubject=$reginfo id=$reginfo.uid assign="eventData"}
{foreach item='eventDisplay' from=$eventData}
    {$eventDisplay}
{/foreach}

{notifydisplayhooks eventname='users.ui_hooks.registration.display_view' id=$reginfo.uid}

<fieldset>
    <legend>{gt text='Registration Status'}</legend>
    <div class="form-group">
        <label class="col-sm-3 control-label">{gt text='Expires:'}</label>
        <div class="col-sm-9">
            <div class="alert alert-info">
                {gt text='Because a password is not set for this registration, the e-mail verification process cannot be skipped. It must be completed so that the user can establish a password before the user account is created.'}
            </div>
        </div>
    </div>
</fieldset>

{notifyevent eventname='module.users.ui.display_view' eventsubject=$reginfo id=$reginfo.uid assign="eventData"}
{foreach item='eventDisplay' from=$eventData}
    {$eventDisplay}
{/foreach}

{notifydisplayhooks eventname='users.ui_hooks.registration.display_view' id=$reginfo.uid}

<fieldset>
    <legend>{gt text='Registration Status'}</legend>
    <div class="form-group">
        <div class="col-sm-9">
            <span>{if $reginfo.isverified}{gt text='Never, registration is verified'}{elseif empty($reginfo.verificationsent)}{gt text='Expiration date will be set when the verification e-mail is sent'}{elseif !isset($reginfo.validuntil) || empty($reginfo.validuntil)}{gt text='Never'}{else}{$reginfo.validuntil}{/if}</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{gt text='E-mail verification:'}</label>
        <div class="col-sm-9">
            <div class="form-control-static">
                {if !isset($reginfo.isverified) || empty($reginfo.isverified) || !$reginfo.isverified}
                {if !isset($reginfo.verificationsent) || empty($reginfo.verificationsent)}
                <span class="fa fa-times fa-fw fa-red"></span>
                {gt text='Verification e-mail message not yet sent to the user'}
                {else}
                <span class="fa fa-clock-o fa-fw fa-red"></span>
                {gt text='Not yet verified'}
                {/if}
                {else}
                <span class="fa fa-check fa-fw fa-green"></span>
                {gt text='Verification complete'} <span class="sub">{gt text='(or verification was not required when the registration was completed)'}</span>
                {/if}
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{gt text='Administrator approval:'}</label>
        <div class="col-sm-9">
            <div class="form-control-static">
                {if !isset($reginfo.isapproved) || empty($reginfo.isapproved) || !$reginfo.isapproved}
                <span class="fa fa-times fa-fw fa-red"></span>
                {gt text='Not yet approved'}
                {else}
                <span class="fa fa-check fa-fw fa-green"></span>
                {gt text='Approved'} <span class="sub">{gt text='(or approval was not required when the registration was completed)'}</span>
                {/if}
            </div>
        </div>
    </div>
</fieldset>
