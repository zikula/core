{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="import" size="small"}
    <h3>{gt text='Import users'}</h3>
</div>

{if $importResults neq ''}
<div class="z-errormsg">
    {$importResults}
</div>
{/if}

<form class="z-form" action="{modurl modname='Users' type='admin' func='import'}" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="confirmed" value="1" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Select the CSV file"}</legend>
            <div class="z-formrow">
                <label for="users_import">{gt text="CSV file (Max. %s)" tag1=$post_max_size}</label>
                <input id="users_import" type="file" name="importFile" size="30" />
                <em class="z-formnote z-sub">{gt text='The file must be utf8 encoded.'}</em>
            </div>
            <div class="z-formrow">
                <label for="users_import_delimiter">{gt text="CSV delimiter"}</label>
                <select id="users_import_delimiter" name="delimiter">
                    <option value="1">Comma (,)</option>
                    <option value="2">Semicolon (;)</option>
                    <option value="3">Colon (:)</option>
                </select>
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Import' __title='Import' __text='Import'}
            <a href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>

<div class="z-informationmsg">
    <h4>{gt text="About the CSV file"}</h4>
    <dl>
        <dt>{gt text="The first row of the CSV file must contain the field names. It must be like this:"}</dt>
        <dd>uname,pass,email,activated,sendmail,groups</dd>
    </dl>
    <dl>
        <dt>{gt text="where:"}</dt>

        <dd>* uname{gt text=" (mandatory) - The user name. This value must be unique."}</dd>
        <dd>* pass{gt text=" (mandatory) - The user password. It must have %s characters or more. Preferentially containing letters and numbers." tag1=$modvars.Users.minpass}</dd>
        <dd>* email{gt text=" (mandatory) - The user email. If the validation method is based on the user email this value must be unique."}</dd>
        <dd>* activated{gt text=" - Type 0 if user is not active, 1 if the user must be active. The default value is 1."}</dd>
        <dd>* sendmail{gt text=" - Type 1 if the system must send the password to the user via email and 0 otherwise. The default value is 1. The module Mailer must be active and correctly configured. The email is sent only if user activated value is upper than 0."}</dd>
        <dd>* groups{gt text=" - The identities of the groups where the user must belong separated by the character |. If you do not specify any group, the default group is %s." tag1=$defaultGroup}</dd>
    </dl>
    <dl>
        <dt>{gt text="An example of a valid CSV file"}</dt>
        <dd>uname,pass,email,activated,sendmail,groups</dd>
        <dd>{gt text="albert,12secure09,albert@example.org,1,1,2"}</dd>
        <dd>{gt text="george,lesssecure,george@example.org,1,0,1|5"}</dd>
        <dd>{gt text="robert,hispassword,robert@example.org,,,"}</dd>
    </dl>
    <dl>
        <dt>{gt text="Another example of a valid CSV file"}</dt>
        <dd>uname,pass,email</dd>
        <dd>{gt text="albert,12secure09,albert@example.org"}</dd>
        <dd>{gt text="george,lesssecure,george@example.org"}</dd>
        <dd>{gt text="robert,hispassword,robert@example.org"}</dd>
    </dl>
</div>
{adminfooter}