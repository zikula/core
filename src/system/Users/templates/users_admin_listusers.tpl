{configgetvar name='profilemodule' assign='profilemodule'}
{gt text="Search results" assign=templatetitle}
{include file="users_admin_menu.tpl"}

<div class="z-admincontainer">

    {if $mailusers eq true || $deleteusers eq true}
    <script type="text/javascript">
        /**
        * Checks/unchecks all tables
        * Auther PHPMyadmin
        *
        * @param   string   the form name
        * @param   boolean  whether to check or to uncheck the element
        *
        * @return  boolean  always true
        */
        function setCheckboxes(the_form, do_check)
        {
            var elts      = document.forms[the_form].elements['userid[]'];
            var elts_cnt  = (typeof(elts.length) != 'undefined')
            ? elts.length
            : 0;

            if (elts_cnt) {
                for (var i = 0; i < elts_cnt; i++) {
                    elts[i].checked = do_check;
                } // end for
            } else {
                elts.checked        = do_check;
            } // end if... else

            return true;
        } // end of the 'setCheckboxes()' function
    </script>
    {/if}

    <div class="z-adminpageicon">{img modname='Users' src='admin.png' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    <form id="userlist" class="z-form" method="post" action="{modurl modname="Users" type="admin" func="processusers"}">
        <div>
            <table class="z-datatable">
                <thead>
                    <tr>
                        {if $mailusers eq true || $deleteusers eq true}
                        <th>&nbsp;</th>
                        {/if}
                        <th>{gt text="User name"}</th>
                        {if $profilemodule}
                        <th>{gt text="Internal name"}</th>
                        {/if}
                        <th>{gt text="E-mail address"}</th>
                        <th class="z-right">{gt text="Actions"}</th>
                    </tr>
                </thead>
                <tbody>
                    {section name=item loop=$items}
                    <tr class="{cycle values='z-odd,z-even'}">
                        {if (($mailusers eq true) || ($deleteusers eq true))}
                        <td>{if ($items[item].uid neq 1)}<input type="checkbox" name="userid[]" value="{$items[item].uid}" />{/if}</td>
                        {/if}
                        <td>{$items[item].uname}</td>
                        {if $profilemodule}
                        <td>{usergetvar name='_UREALNAME' uid=$items[item].uid}</td>
                        {/if}
                        <td>
                            {if ($items[item].email neq '') && ($items[item].uid neq 1)}
                            <input type="hidden" name="sendmail[recipientsname][{$items[item].uid}]" value="{$items[item].uname}" />
                            <input type="hidden" name="sendmail[recipientsemail][{$items[item].uid}]" value="{$items[item].email}" />
                            {$items[item].email}{/if}
                        </td>
                        <td class="z-right">
                            {if $actions[item].modifyUrl}<a href="{$actions[item].modifyUrl|safehtml}">{img modname=core set=icons/extrasmall src=xedit.gif __alt="Edit" __title="Edit" class="tooltips"}</a>{/if}
                            {if $actions[item].deleteUrl}<a href="{$actions[item].deleteUrl|safehtml}">{img modname=core set=icons/extrasmall src=14_layer_deletelayer.gif __alt="Delete" __title="Delete" class="tooltips"}</a>{/if}
                        </td>
                    </tr>
                    {/section}
                </tbody>
            </table>

            {if $mailusers eq true || $deleteusers eq true}
            <p>
                <a href="#" onclick="setCheckboxes('userlist', true); return false;">{gt text="Select all"}</a> / <a href="#" onclick="setCheckboxes('userlist', false); return false;">{gt text="De-select all"}</a>
            </p>
            {/if}

            {if $mailusers eq true}
            <fieldset>
                <legend>{gt text="Send e-mail message"}</legend>
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
            {/if}

            <div class="z-formbuttons z-buttons">
                {if $mailusers eq true || $deleteusers eq true}
                <select name="op" id="op">
                    {if $mailusers eq true}
                    <option value="mail">{gt text="Send"}</option>
                    {/if}
                    {if $deleteusers eq true}
                    <option value="delete">{gt text="Delete"}</option>
                    {/if}
                </select>
                <input type="submit" name="submit" value="{gt text="Save"}" />
            </div>
            {/if}
        </div>
    </form>
</div>

<script type="text/javascript">
    Zikula.UI.Tooltips($$('.tooltips'));
</script>
