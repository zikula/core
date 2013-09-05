<div class="form-horizontal" role="form">
    <fieldset>
        <legend>{gt text='Account Information'}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text='User name:'}</label>
            <div class="col-lg-9">
            <span>{$reginfo.uname}</span>
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text='E-mail address:'}</label>
            <div class="col-lg-9">
            <span>{if !empty($reginfo.email)}<a href="mailto:{$reginfo.email|urlencode}">{$reginfo.email|safetext}</a>{else}---{/if}</span>
        </div>
    </div>
    </fieldset>
</div>

{if !isset($reginfo.pass) || empty($reginfo.pass)}
<div class="form-horizontal" role="form">
    <fieldset>
        <legend>{gt text='Log-in information'}</legend>
        <div class="form-group">
            <div class="z-formlist alert alert-info">{gt text='Because a password is not set for this registration, the e-mail verification process cannot be skipped. It must be completed so that the user can establish a password before the user account is created.'}</div>
        </div>
    </div>
    </fieldset>
</div>
{/if}

{notifyevent eventname='module.users.ui.display_view' eventsubject=$reginfo id=$reginfo.uid assign="eventData"}
{foreach item='eventDisplay' from=$eventData}
    {$eventDisplay}
{/foreach}

{notifydisplayhooks eventname='users.ui_hooks.registration.display_view' id=$reginfo.uid}

<div class="form-horizontal" role="form">
    <fieldset>
        <legend>{gt text='Registration Status'}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text='Expires:'}</label>
            <div class="z-formlist alert alert-info">{gt text='Because a password is not set for this registration, the e-mail verification process cannot be skipped. It must be completed so that the user can establish a password before the user account is created.'}</div>
        </div>
    </div>
    </fieldset>
</div>
{/if}

{notifyevent eventname='module.users.ui.display_view' eventsubject=$reginfo id=$reginfo.uid assign="eventData"}
{foreach item='eventDisplay' from=$eventData}
    {$eventDisplay}
{/foreach}

{notifydisplayhooks eventname='users.ui_hooks.registration.display_view' id=$reginfo.uid}

<div class="form-horizontal" role="form">
    <fieldset>
        <legend>{gt text='Registration Status'}</legend>
        <div class="form-group">
            <div class="col-lg-9">
            <span>{if $reginfo.isverified}{gt text='Never, registration is verified'}{elseif empty($reginfo.verificationsent)}{gt text='Expiration date will be set when the verification e-mail is sent'}{elseif !isset($reginfo.validuntil) || empty($reginfo.validuntil)}{gt text='Never'}{else}{$reginfo.validuntil}{/if}</span>
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text='E-mail verification:'}</label>
            <div class="col-lg-9">
            <span>{if !isset($reginfo.isverified) || empty($reginfo.isverified) || !$reginfo.isverified}{if !isset($reginfo.verificationsent) || empty($reginfo.verificationsent)}{img modname='core' set='icons/extrasmall' src='mail_delete.png' __title='E-mail verification not sent; awating approval' __alt='E-mail verification not sent; awating approval'} {gt text='Verification e-mail message not yet sent to the user'}{else}{img modname='core' set='icons/extrasmall' src='redled.png' __title='Pending verification of e-mail address' __alt='Pending verification of e-mail address'} {gt text='Not yet verified'}{/if}{else}{img modname='core' set='icons/extrasmall' src='greenled.png' __title='Verified' __alt='Verified'} {gt text='Verification complete'} <span class="sub">{gt text='(or verification was not required when the registration was completed)'}</span>{/if}</span>
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text='Administrator approval:'}</label>
            <div class="col-lg-9">
            <span>{if !isset($reginfo.isapproved) || empty($reginfo.isapproved) || !$reginfo.isapproved}{img modname='core' set='icons/extrasmall' src='redled.png' __title='Pending approval' __alt='Pending approval'} {gt text='Not yet approved'}{else}{img modname='core' set='icons/extrasmall' src='greenled.png' __title='Approved' __alt='Approved'} {gt text='Approved'} <span class="sub">{gt text='(or approval was not required when the registration was completed)'}</span>{/if}</span>
        </div>
    </div>
    </fieldset>
</div>
