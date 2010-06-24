{gt text='Import users' assign='templatetitle'}
{include file='users_admin_menu.htm'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=db_comit.gif set=icons/large alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>
    {if $importResults neq ''}
    <div class="z-errormsg">
        {$importResults}
    </div>
    {/if}
    <form class="z-form" action="{modurl modname='Users' type='admin' func='import'}" method="post" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="confirmed" value="1" />
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
                {button src='button_ok.gif' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
                <a href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
    <div class="z-informationmsg">
        <h3>{gt text="About the CVS file"}</h3>
        <p>{gt text="The first row of the CVS file must contain the field names. It must be like this:"}</p>
        <p>uname,pass,email,activated,sendMail,groups</p>
        <p>{gt text="where:"}</p>
        <dl>
            <dt>* uname{gt text=" (mandatory) - The user name. This value must be unique."}</dt>
            <dt>* pass{gt text=" (mandatory) - The user password. It must have %s characters or more. Preferentially containing letters and numbers." tag1=$minpass}</dt>
            <dt>* email{gt text=" (mandatory) - The user email. If the validation method is based on the user email this value must be unique."}</dt>
            <dt>* activated{gt text=" - Type 0 if user is not active, 1 if the user must be active, 2 if user has to accept the 'Terms of use' before being active (only if legal module is activated), 4 if user has to change the passwords on login and 6 if user has both: accept the 'Terms of use' and change the password. The default value is 1."}</dt>
            <dt>* sendmail{gt text=" - Type 1 if the system must send the password to the user via email and 0 otherwise. The default value is 1. The module Mailer must be active and correctly configured. The email is sent only if user activated value is upper than 0."}</dt>
            <dt>* groups{gt text=" - The identities of the groups where the user must belong separated by the character |. If you do not specify any group, the default group is %s." tag1=$defaultGroup}</dt>
        </dl>
        <p>{gt text="An example of a valid CSV file"}</p>
        <dl>
            <dt>uname,pass,email,activated,sendMail</dt>
            <dt>{gt text="albert,12secure09,albert@example.org,1,1,2"}</dt>
            <dt>{gt text="george,lesssecure,george@example.org,1,0,1|5"}</dt>
            <dt>{gt text="robert,hispassword,robert@example.org,,,"}</dt>
        </dl>
        <p>{gt text="Another example of a valid CSV file"}</p>
        <dl>
            <dt>uname,pass,email</dt>
            <dt>{gt text="albert,12secure09,albert@example.org"}</dt>
            <dt>{gt text="george,lesssecure,george@example.org"}</dt>
            <dt>{gt text="robert,hispassword,robert@example.org"}</dt>
        </dl>
    </div>
</div>