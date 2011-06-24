{strip}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        $('select_all').observe('click', function(e){
            Zikula.toggleInput('users_mailusers', true);
            e.stop()
        });
        $('deselect_all').observe('click', function(e){
            Zikula.toggleInput('users_mailusers', false);
            e.stop()
        });
    });
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="mail" size="small"}
    <h3>{gt text="E-mail Users"}</h3>
</div>

<form id="users_mailusers" class="z-form" method="post" action="{modurl modname="Users" type="admin" func="mailUsers"}">
    <div>
        <input type="hidden" id="users_mailusers_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="users_mailusers_formid" name="formid" value="users_mailusers" />
        <div>
            <fieldset>
                <legend>{gt text='Select recipients'}</legend>
                <table class="z-datatable">
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
                        <tr class="{cycle values='z-odd,z-even'}">
                            <td>{if ($items[item].uid != 1)}<input type="checkbox" name="userid[]" value="{$items[item].uid}" />{/if}</td>
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
                    <a href="#" id="select_all">{gt text="Select all"}</a> / <a href="#" id="deselect_all">{gt text="De-select all"}</a>
                </p>
            </fieldset>

            <fieldset>
                <legend>{gt text='Compose message'}</legend>
                <p class="z-informationmsg">{gt text="Notice: This e-mail message will be sent to your address and to all other recipients you select. Your address will be the entered as the main recipient, and all your selected recipients will be included in the blind carbon copies ('Bcc') list. You can specify the number of 'Bcc' recipients to be added to each e-mail message. If the number of your selected recipients exceeds the number you enter here, then repeat messages will be sent until everyone in your selection has been mailed (you will receive a copy of each message). The allowed batch size may be set by your hosting provider."}</p>
                <div class="z-formrow">
                    <label for="users_from">{gt text="Sender's name"}</label>
                    <input id="users_from" name="sendmail[from]" type="text" size="40" />
                </div>
                <div class="z-formrow">
                    <label for="users_rpemail">{gt text="Address to which replies should be sent"}</label>
                    <input id="users_rpemail" name="sendmail[rpemail]" type="text" size="40" />
                </div>
                <div class="z-formrow">
                    <label for="users_subject">{gt text="Subject"}</label>
                    <input id="users_subject" name="sendmail[subject]" type="text" size="40" />
                </div>
                <div class="z-formrow">
                    <label for="users_message">{gt text="Message"}</label>
                    <textarea id="users_message" name="sendmail[message]" cols="50" rows="10"></textarea>
                </div>
                <div class="z-formrow">
                    <label for="batchsize">{gt text="Send e-mail messages in batches"}</label>
                    <span>
                        <input name="sendmail[batchsize]" type="text" id="batchsize" value="100" size="5" />
                        <em>{gt text="messages per batch"}</em>
                    </span>
                </div>
            </fieldset>

            <div class="z-formbuttons z-buttons">
                {button type='submit' src='mail_generic.png' set='icons/extrasmall' __alt="Send e-mail to selected recipients" __title="Send e-mail to selected recipients" __text="Send e-mail to selected recipients"}
                <a href="{modurl modname='Users' type='admin' func='main'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall'  __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}