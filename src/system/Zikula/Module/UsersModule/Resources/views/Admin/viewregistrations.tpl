{adminheader}
<h3>
    <span class="icon icon-user"></span>
    {gt text="Registration applications list"}
</h3>

{if count($reglist) > 0}
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text="User name"}</th>
            <th>{gt text="Internal ID"}</th>
            <th>{gt text="Registration date"}</th>
            <th>{gt text="E-mail address"}</th>
            <th class="text-center">{gt text="Approved?"}</th>
            <th class="text-center">{gt text="Verified?"}</th>
            <th class="text-center" colspan="{$actions.count}">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {gt assign='titleIfSent' text='Send a new e-mail verification code'}
        {gt assign='titleIfNotSent' text='Send an e-mail verification code'}
        {foreach from=$reglist item='reginfo' name='reglist'}
        <tr>
            <td>{$reginfo.uname|safetext}</td>
            <td>{$reginfo.uid|safetext}</td>
            <td>{$reginfo.user_regdate|safetext}</td>
            <td>{if !empty($reginfo.email)}<a href="mailto:{$reginfo.email|urlencode}">{$reginfo.email|safetext}</a>{else}---{/if}</td>
            <td class="text-center">
                {if $reginfo.isapproved}
                <span class="icon icon-ok icon-green tooltips" title="{gt text='Approved'}"></span>
                {else}
                <span class="icon icon-remove icon-red tooltips" title="{gt text='Pending approval'}"></span>
                {/if}
            </td>
            <td class="text-center">
                {if $reginfo.isverified}
                <span class="icon icon-ok icon-green tooltips" title="{gt text='Verified'}"></span>
                {elseif !$reginfo.verificationsent}

                {if ($modvars.ZikulaUsersModule.moderation_order != 'Zikula\Module\UsersModule\Constant::APPROVAL_BEFORE'|const) || (($modvars.ZikulaUsersModule.moderation_order == 'Zikula\Module\UsersModule\Constant::APPROVAL_BEFORE'|const) && ($reginfo.isapproved))}
                <span class="icon icon-warning-sign icon-red tooltips" title="{gt text='E-mail verification not sent; must be resent'}"></span>
                {else}
                <span class="icon icon-remove icon-red tooltips" title="{gt text='E-mail verification not sent'}"></span>
                {/if}
                {else}
                <span class="icon icon-time icon-red tooltips"  title="{gt text='Pending verification of e-mail address'}"></span>
                {/if}
            </td>
            {assign var="regactions" value=$actions.list[$reginfo.uid]}
            {strip}
            {* For the following, (isset($regactions.optname) == true) means that the current user can, in general, perform the operation; *}
            {* ($regactions.optname == true) means that the operation can be performed for that individual registration record. *}
            <td class="actions">
            {if isset($regactions.display)}
                {if $regactions.display}
                <a class="icon icon-info-sign icon-fixed-width tooltips" href="{$regactions.display|safetext}" title="{gt text='Display registration details'}"></a>
                {else}
                {* For each option, invisible image to take up as much space as a normal image to maintain alignment. Must be visibility: hidden, not display: none. *}
                <span class="icon icon-fixed-width"></span>
                {/if}
            {/if}
            {if isset($regactions.modify)}
                {if $regactions.modify}
                <a class="icon icon-edit icon-fixed-width tooltips" href="{$regactions.modify|safetext}" title="{gt text='Modify registration details'}"></a>
                {else}
                <span class="icon icon-fixed-width"></span>
                {/if}
            {/if}
            {if isset($regactions.approve)}
                {if $regactions.approve && !$reginfo.isverified}
                {if isset($modvars.ZikulaUsersModule.moderation_order) && ($modvars.ZikulaUsersModule.moderation_order == 'Zikula\Module\UsersModule\Constant::APPROVAL_AFTER'|const)}
                <a class="icon icon-check icon-fixed-width tooltips" href="{$regactions.approve|safetext}" text="{gt text='Pre-approve (verification still required)'}"></a>
                {else}
                <a class="icon icon-check icon-fixed-width tooltips" href="{$regactions.approve|safetext}" title="{gt text='Approve'}"></a>
                {/if}
                {elseif $regactions.approve && $reginfo.isverified}
                <a class="icon icon-check icon-fixed-width tooltips" href="{$regactions.approve|safetext}" title="{gt text='Approve (creates a new user account)'}"></a>
                {else}
                <span class="icon icon-fixed-width"></span>
                {/if}
            {/if}
            {if isset($regactions.deny)}
                {if $regactions.deny}
                <a class="icon icon-trash icon-fixed-width tooltips" href="{$regactions.deny|safetext}" title="{gt text='Deny (deletes registration)'}"></a>
                {else}
                <span class="icon icon-fixed-width"></span>
                {/if}
            {/if}
            {if isset($regactions.verify)}
                {if $regactions.verify}
                {if !empty($reginfo.verifycode)}
                {assign var='optionTitle' value=$titleIfSent}
                {else}
                {assign var='optionTitle' value=$titleIfNotSent}
                {/if}
                <a class="icon icon-envelope icon-fixed-width tooltips" href="{$regactions.verify|safetext}" title={$optionTitle}></a>
                {else}
                <span class="icon icon-fixed-width"></span>
                {/if}
            {/if}
            {if isset($regactions.approveForce)}
                {if $regactions.approveForce && !$reginfo.isverified}
                <a class="icon icon-share icon-fixed-width tooltips" href="{$regactions.approveForce|safetext}" title="{gt text='Skip verification (approves, and creates a new user account)'}"></a>
                {else}
                <span class="icon icon-fixed-width"></span>
                {/if}
            {/if}
            </td>
            {/strip}
        </tr>
        {/foreach}
    </tbody>
</table>

{if !empty($pager)}{pager rowcount=$pager.rowcount limit=$pager.limit posvar=$pager.posvar}{/if}

<p class="sub text-center bold">{gt text='Legend'}</p>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th colspan="2" class="sub text-center">{gt text='Approval'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="col-md-6">
                <span title="{gt text='Approved'}" class="icon icon-ok icon-green"></span>
                {gt text='An administrator has approved the registration, or approval was not required when the registration was completed.'}
            </td>
            <td class="col-md-6">
                <span title="{gt text='Pending approval'}" class="icon icon-remove icon-red"></span>
                {gt text='Waiting for an administrator to approve the registration.'}
            </td>
        </tr>
    </tbody>
</table>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th colspan="2" class="sub text-center">{gt text='Verification'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="col-md-6">
                <span title="{gt text='Verified'}" class="icon icon-ok icon-green"></span>
                {gt text='The user has completed the e-mail verification process, or e-mail verification was not required when the registration was completed.'}
            </td>
            <td class="col-md-6">
                <span title="{gt text='Pending verification of e-mail address'}" class="icon icon-time icon-orange"></span>
                {gt text='An e-mail has been sent to the registered e-mail address, but the user has not yet responded.'}
            </td>
        </tr>
        <tr>
            <td>
                <span title="{gt text='Verification e-mail message not yet sent'}" class="icon icon-remove icon-red"></span>
            {gt text='A verification e-mail has not been sent to the registered e-mail address.'}{if $modvars.ZikulaUsersModule.moderation_order == 'Zikula\Module\UsersModule\Constant::APPROVAL_BEFORE'|const} {gt text='If it is not yet approved, then it will be sent on approval.'}{/if}
            </td>
            <td>
                <span title="{gt text='E-mail verification not sent; must be resent'}" class="icon icon-warning-sign icon-red"></span>
                {gt text='A verification e-mail has not been sent to the registered e-mail address, but the registration is in a state where one should already have been sent.'}
            </td>
        </tr>
    </tbody>
</table>
{else}
<p class="alert alert-info">{gt text='There are no pending registration applications to review.'}</p>
{/if}
{adminfooter}