{strip}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="user" size="small"}
    <h3>{gt text="Registration applications list"}</h3>
</div>

{if count($reglist) > 0}
<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="User name"}</th>
            <th>{gt text="E-mail address"}</th>
            <th class="z-center">{gt text="Approved?"}</th>
            <th class="z-center">{gt text="Verified?"}</th>
            <th class="z-center" colspan="{$actions.count}">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {gt assign='titleIfSent' text='Send a new e-mail verification code'}
        {gt assign='titleIfNotSent' text='Send an e-mail verification code'}
        {foreach from=$reglist item='reginfo' name='reglist'}<tr class="{cycle values='z-odd,z-even'}">
            <td>{$reginfo.uname|safetext}</td>
            <td>{if !empty($reginfo.email)}<a href="mailto:{$reginfo.email|urlencode}">{$reginfo.email|safetext}</a>{else}---{/if}</td>
            <td class="z-center">
                {if $reginfo.isapproved}
                {img modname='core' set='icons/extrasmall' src='greenled.png' __title='Approved' __alt='Approved' class='tooltips'}
                {else}
                {img modname='core' set='icons/extrasmall' src='redled.png' __title='Pending approval' __alt='Pending approval' class='tooltips'}
                {/if}
            </td>
            <td class="z-center">
                {if $reginfo.isverified}
                {img modname='core' set='icons/extrasmall' src='greenled.png' __title='Verified' __alt='Verified' class='tooltips'}
                {elseif !$reginfo.verificationsent}
                {if ($modvars.Users.moderation_order != 'Users_Constant::APPROVAL_BEFORE'|const) || (($modvars.Users.moderation_order == 'Users_Constant::APPROVAL_BEFORE'|const) && ($reginfo.isapproved))}
                {img modname='core' set='icons/extrasmall' src='flag.png' __title='E-mail verification not sent; must be resent' __alt='E-mail verification not sent; must be resent' class='tooltips'}
                {else}
                {img modname='core' set='icons/extrasmall' src='mail_delete.png' __title='E-mail verification not sent' __alt='E-mail verification not sent' class='tooltips'}
                {/if}
                {else}
                {img modname='core' set='icons/extrasmall' src='redled.png' __title='Pending verification of e-mail address' __alt='Pending verification of e-mail address' class='tooltips'}
                {/if}
            </td>
            {assign var="regactions" value=$actions.list[$reginfo.uid]}
            {strip}
            {* For the following, (isset($regactions.optname) == true) means that the current user can, in general, perform the operation; *}
            {* ($regactions.optname == true) means that the operation can be performed for that individual registration record. *}
            {if isset($regactions.display)}
            <td class="users_action">
                {if $regactions.display}
                <a href="{$regactions.display|safetext}">{img src='documentinfo.png' modname='core' set='icons/extrasmall' __title='Display registration details' __alt='Display registration details' class='tooltips'}</a>
                {else}
                {* For each option, invisible image to take up as much space as a normal image to maintain alignment. Must be visibility: hidden, not display: none. *}
                {img style='visibility: hidden;' src='agt_stop.png' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available' class='tooltips'}
                {/if}
            </td>
            {/if}
            {if isset($regactions.modify)}
            <td class="users_action">
                {if $regactions.modify}
                <a href="{$regactions.modify|safetext}">{img src='xedit.png' modname='core' set='icons/extrasmall' __title='Modify registration details' __alt='Modify registration details' class='tooltips'}</a>
                {else}
                {img style='visibility: hidden;' src='agt_stop.png' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available' class='tooltips'}
                {/if}
            </td>
            {/if}
            {if isset($regactions.approve)}
            <td class="users_action">
                {if $regactions.approve && !$reginfo.isverified}
                {if isset($modvars.Users.moderation_order) && ($modvars.Users.moderation_order == 'Users_Constant::APPROVAL_AFTER'|const)}
                <a href="{$regactions.approve|safetext}">{img src='button_ok.png' modname='core' set='icons/extrasmall' __title='Pre-approve (verification still required)' __alt='Pre-approve (verification still required)' class='tooltips'}</a>
                {else}
                <a href="{$regactions.approve|safetext}">{img src='button_ok.png' modname='core' set='icons/extrasmall' __title='Approve' __alt='Approve' class='tooltips'}</a>
                {/if}
                {elseif $regactions.approve && $reginfo.isverified}
                <a href="{$regactions.approve|safetext}">{img src='add_user.png' modname='core' set='icons/extrasmall' __title='Approve (creates a new user account)' __alt='Approve (creates a new user account)' class='tooltips'}</a>
                {else}
                {img style='visibility: hidden;' src='agt_stop.png' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available' class='tooltips'}
                {/if}
            </td>
            {/if}
            {if isset($regactions.deny)}
            <td class="users_action">
                {if $regactions.deny}
                <a href="{$regactions.deny|safetext}">{img src='delete_user.png' modname='core' set='icons/extrasmall' __title='Deny (deletes registration)' __alt='Deny (deletes registration)' class='tooltips'}</a>
                {else}
                {img style='visibility: hidden;' src='agt_stop.png' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available' class='tooltips'}
                {/if}
            </td>
            {/if}
            {if isset($regactions.verify)}
            <td class="users_action">
                {if $regactions.verify}
                {if !empty($reginfo.verifycode)}
                {assign var='optionTitle' value=$titleIfSent}
                {else}
                {assign var='optionTitle' value=$titleIfNotSent}
                {/if}
                <a href="{$regactions.verify|safetext}">{img src='mail_send.png' modname='core' set='icons/extrasmall' title=$optionTitle alt=$optionTitle class='tooltips'}</a>
                {else}
                {img style='visibility: hidden;' src='agt_stop.png' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available' class='tooltips'}
                {/if}
            </td>
            {/if}
            {if isset($regactions.approveForce)}
            <td class="users_action">
                {if $regactions.approveForce && !$reginfo.isverified}
                <a href="{$regactions.approveForce|safetext}">{img src='db_update.png' modname='core' set='icons/extrasmall' __title='Skip verification (approves, and creates a new user account)' __alt='Skip verification (approves, and creates a new user account)' class='tooltips'}</a>
                {else}
                {img style='visibility: hidden;' src='agt_stop.png' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available' class='tooltips'}
                {/if}
            </td>
            {/if}
            {/strip}
        </tr>
        {/foreach}
    </tbody>
</table>

{if !empty($pager)}{pager rowcount=$pager.rowcount limit=$pager.limit posvar=$pager.posvar}{/if}

<p class="z-sub z-center z-bold">{gt text='Legend'}</p>
<table class="z-datatable">
    <thead>
        <tr>
            <th colspan="2" class="z-sub z-center">{gt text='Approval'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="z-w45">
                <div title="{gt text='Approved'}" class="z-sub user-icon-greenled">{gt text='An administrator has approved the registration, or approval was not required when the registration was completed.'}</div>
            </td>
            <td class="z-w45">
                <div title="{gt text='Pending approval'}" class="z-sub user-icon-redled">{gt text='Waiting for an administrator to approve the registration.'}</div>
            </td>
        </tr>
    </tbody>
</table>
<table class="z-datatable">
    <thead>
        <tr>
            <th colspan="2" class="z-sub z-center">{gt text='Verification'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="z-w45">
                <div title="{gt text='Verified'}" class="z-sub user-icon-greenled">{gt text='The user has completed the e-mail verification process, or e-mail verification was not required when the registration was completed.'}</div>
            </td>
            <td class="z-w45">
                <div title="{gt text='Pending verification of e-mail address'}" class="z-sub user-icon-redled">{gt text='An e-mail has been sent to the registered e-mail address, but the user has not yet responded.'}</div>
            </td>
        </tr>
        <tr>
            <td class="z-w45">
                <div title="{gt text='Verification e-mail message not yet sent'}" class="z-sub user-icon-mail_delete">{gt text='A verification e-mail has not been sent to the registered e-mail address.'}{if $modvars.Users.moderation_order == 'Users_Constant::APPROVAL_BEFORE'|const} {gt text='If it is not yet approved, then it will be sent on approval.'}{/if}</div>
            </td>
            <td class="z-w45">
                <div title="{gt text='E-mail verification not sent; must be resent'}" class="z-sub user-icon-status_unknown">{gt text='A verification e-mail has not been sent to the registered e-mail address, but the registration is in a state where one should already have been sent.'}</div>
            </td>
        </tr>
    </tbody>
</table>
{else}
<p class="z-informationmsg">{gt text='There are no pending registration applications to review.'}</p>
{/if}
{adminfooter}