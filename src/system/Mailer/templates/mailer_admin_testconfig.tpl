{ajaxheader modname=Mailer filename=mailer_admin_testconfig.js noscriptaculous=true effects=true}
{adminheader}
<div class="z-admin-pageheader">
    {icon type="mail" size="small"}
    <h3>{gt text="Test current settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Mailer" type="admin" func="sendmessage"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input id="mailer_html" type="hidden" name="html" value="0" />
        <fieldset>
            <legend>{gt text="Settings test"}</legend>
            <div class="z-formrow">
                <label for="mailer_fromname">{gt text="Sender's name"}</label>
                <span id="mailer_fromname">{$modvars.ZConfig.sitename}</span>
            </div>
            <div class="z-formrow">
                <label for="mailer_fromname">{gt text="Sender's e-mail address"}</label>
                <span id="mailer_fromaddress">{$modvars.ZConfig.adminmail}</span>
            </div>
            <div class="z-formrow">
                <label for="mailer_toname">{gt text="Recipient's name"}</label>
                <input id="mailer_toname" name="toname" type="text" size="30" maxlength="50" />
            </div>
            <div class="z-formrow">
                <label for="mailer_toaddress">{gt text="Recipient's e-mail address"}</label>
                <input id="mailer_toaddress" name="toaddress" type="text" size="30" maxlength="50" />
            </div>
            <div class="z-formrow">
                <label for="mailer_subject">{gt text="Subject"}</label>
                <input id="mailer_subject" name="subject" type="text" size="30" maxlength="50" />
            </div>
            <div class="z-formrow">
                <label>{gt text="Message Type"}</label>
                <div>
                    <input id="mailer_msgtype_text" type="radio" name="msgtype" value="text" checked="checked" />
                    <label for="mailer_msgtype_text">{gt text="Plain-text message"}</label>
                    <input id="mailer_msgtype_html" type="radio" name="msgtype" value="html" />
                    <label for="mailer_msgtype_html">{gt text="HTML-formatted message"}</label>
                    <input id="mailer_msgtype_multipart" type="radio" name="msgtype" value="multipart" />
                    <label for="mailer_msgtype_multipart">{gt text="Multi-part message"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="mailer_body"><span id="mailer_body_html" style="display: none;">{gt text="HTML"} </span>{gt text="Message"}</label>
                <textarea id="mailer_body" name="body" cols="50" rows="10"></textarea>
            </div>
            <div id="mailer_altbody_div" class="z-formrow" style="display: none;">
                <label for="mailer_altbody">{gt text="Plain-text message"}</label>
                <textarea id="mailer_altbody" name="altbody" cols="50" rows="10"></textarea>
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Test settings now" __title="Test settings now" __text="Test settings now"}
            <a href="{modurl modname=Mailer type=admin func=main}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}