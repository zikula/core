{pageaddvar name='javascript' value='system/Zikula/Module/UsersModule/Resources/public/js/ZikulaUsersModule.Admin.MailUsers.js'}
{adminheader}
<h3>
    <span class="fa fa-envelope"></span>
    {gt text="E-mail Users"}
</h3>

<form id="users_mailusers" class="form-horizontal" role="form" method="post" action="{route name='zikulausersmodule_admin_mailusers'}">
    <div>
        <input type="hidden" id="users_mailusers_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_mailusers_formid" name="formid" value="users_mailusers" />
        <div>
            <fieldset>
                <legend>{gt text='Select recipients'}</legend>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>{gt text="User name"}</th>
                            {if $modvars.ZConfig.profilemodule}
                            <th>{gt text="Internal name"}</th>
                            {/if}
                            <th>{gt text="E-mail address"}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {section name=item loop=$items}
                        <tr>
                            <td>{if ($items[item].uid != 1)}<input type="checkbox" class="user-checkboxes" name="userid[]" value="{$items[item].uid}" />{/if}</td>
                            <td>{$items[item].uname}</td>
                            {if $modvars.ZConfig.profilemodule}
                            <td>{usergetvar name='realname' uid=$items[item].uid}</td>
                            {/if}
                            <td>
                                {if !empty($items[item].email) && ($items[item].uid != 1)}
                                <input type="hidden" name="sendmail[recipientsname][{$items[item].uid}]" value="{$items[item].uname}" />
                                <input type="hidden" name="sendmail[recipientsemail][{$items[item].uid}]" value="{$items[item].email}" />
                                {$items[item].email}
                                {/if}
                            </td>
                        </tr>
                        {/section}
                    </tbody>
                </table>
                <p>
                    <a href="#" id="select-all">{gt text="Select all"}</a> / <a href="#" id="deselect-all">{gt text="De-select all"}</a>
                </p>
            </fieldset>

            <fieldset>
                <legend>{gt text='Compose message'}</legend>
                <p class="alert alert-info">{gt text="Notice: This e-mail message will be sent to your address and to all other recipients you select. Your address will be the entered as the main recipient, and all your selected recipients will be included in the blind carbon copies ('Bcc') list. You can specify the number of 'Bcc' recipients to be added to each e-mail message. If the number of your selected recipients exceeds the number you enter here, then repeat messages will be sent until everyone in your selection has been mailed (you will receive a copy of each message). The allowed batch size may be set by your hosting provider."}</p>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="users_from">{gt text="Sender's name"}</label>
                    <div class="col-sm-9">
                        <input id="users_from" class="form-control" name="sendmail[from]" type="text" size="40" value="{$modvars.ZConfig.sitename}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="users_rpemail">{gt text="Address to which replies should be sent"}</label>
                    <div class="col-sm-9">
                        <input id="users_rpemail" class="form-control" name="sendmail[rpemail]" type="text" size="40" value="{$modvars.ZConfig.adminmail}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="users_subject">{gt text="Subject"}</label>
                    <div class="col-sm-9">
                        <input id="users_subject" class="form-control" name="sendmail[subject]" type="text" size="40" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="users_format">{gt text='Format'}</label>
                    <div class="col-sm-9">
                        <select id="users_format" class="form-control" name="sendmail[format]" size="1" >
                            <option value="text"{if !isset($modvars.Mailer.html) || !$modvars.Mailer.html} selected="selected"{/if}>{gt text='Text'}</option>
                            <option value="html"{if isset($modvars.Mailer.html) && $modvars.Mailer.html} selected="selected"{/if}>{gt text='HTML'}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="users_message">{gt text="Message"}</label>
                    <div class="col-sm-9">
                        <textarea id="users_message" class="form-control" name="sendmail[message]" cols="50" rows="10"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="batchsize">{gt text="Send e-mail messages in batches"}</label>
                    <div class="col-sm-9">
                        <span>
                            <input name="sendmail[batchsize]" type="text" id="batchsize" class="form-control" value="100" size="5" />
                            <em>{gt text="messages per batch"}</em>
                        </span>
                    </div>
                </div>
            </fieldset>

            {notifydisplayhooks eventname='users.ui_hooks.user.form_edit' id=null}

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button class="btn btn-success" type='submit' title="{gt text='Send e-mail to selected recipients'}">
                        {gt text="Send e-mail to selected recipients"}
                    </button>
                    <a class="btn btn-default" href="{route name='zikulausersmodule_admin_index'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
                </div>
            </div>
        </div>
    </div>
</form>
{adminfooter}