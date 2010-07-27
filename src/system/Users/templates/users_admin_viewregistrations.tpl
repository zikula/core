{gt text="Registration applications list" assign=templatetitle}
{include file="users_admin_menu.tpl"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='Users' src='admin.png' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {if count($reglist) > 0}
    <table class="z-admintable">
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
                <td class="z-center">{if $reginfo.isapproved}{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Approved' __alt='Approved'}{else}{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Pending approval' __alt='Pending approval'}{/if}</td>
                <td class="z-center">{if $reginfo.isverified}{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Verified' __alt='Verified'}{elseif !$reginfo.verificationsent}{img modname='core' set='icons/extrasmall' src='mail_delete.gif' __title='E-mail verification not sent; awating approval' __alt='E-mail verification not sent; awating approval'}{else}{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Pending verification of e-mail address' __alt='Pending verification of e-mail address'}{/if}</td>
                {assign var="regactions" value=$actions.list[$reginfo.uid]}
                {strip}
                {* For the following, (isset($regactions.optname) == true) means that the current user can, in general, perform the operation; *}
                {* ($regactions.optname == true) means that the operation can be performed for that individual registration record. *}
                {if isset($regactions.display)}
                <td class="z-center">
                    {if $regactions.display}
                    <a href="{$regactions.display|safetext}">{img src='info.gif' modname='core' set='icons/extrasmall' __title='Display registration details' __alt='Display registration details'}</a>
                    {else}
                    {* For each option, invisible image to take up as much space as a normal image to maintain alignment. Must be visibility: hidden, not display: none. *}
                    {img style='visibility: hidden;' src='agt_stop.gif' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available'}
                    {/if}
                </td>
                {/if}
                {if isset($regactions.modify)}
                <td class="z-center">
                    {if $regactions.modify}
                    <a href="{$regactions.modify|safetext}">{img src='xedit.gif' modname='core' set='icons/extrasmall' __title='Modify registration details' __alt='Modify registration details'}</a>
                    {else}
                    {img style='visibility: hidden;' src='agt_stop.gif' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available'}
                    {/if}
                </td>
                {/if}
                {if isset($regactions.approve)}
                <td class="z-center">
                    {if $regactions.approve && !$reginfo.isverified}
                    <a href="{$regactions.approve|safetext}">{img src='ok.gif' modname='core' set='icons/extrasmall' __title='Approve' __alt='Approve'}</a>
                    {elseif $regactions.approve && $reginfo.isverified}
                    <a href="{$regactions.approve|safetext}">{img src='add_user.gif' modname='core' set='icons/extrasmall' __title='Approve (creates a new user account)' __alt='Approve (creates a new user account)'}</a>
                    {else}
                    {img style='visibility: hidden;' src='agt_stop.gif' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available'}
                    {/if}
                </td>
                {/if}
                {if isset($regactions.deny)}
                <td class="z-center">
                    {if $regactions.deny}
                    <a href="{$regactions.deny|safetext}">{img src='delete_user.gif' modname='core' set='icons/extrasmall' __title='Deny (deletes registration)' __alt='Deny (deletes registration)'}</a>
                    {else}
                    {img style='visibility: hidden;' src='agt_stop.gif' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available'}
                    {/if}
                </td>
                {/if}
                {if isset($regactions.verify)}
                <td class="z-center">
                    {if $regactions.verify}
                        {if !empty($reginfo.verifycode)}
                            {assign var='optionTitle' value=$titleIfSent}
                        {else}
                            {assign var='optionTitle' value=$titleIfNotSent}
                        {/if}
                    <a href="{$regactions.verify|safetext}">{img src='mail_send.gif' modname='core' set='icons/extrasmall' title=$optionTitle alt=$optionTitle}</a>
                    {else}
                    {img style='visibility: hidden;' src='agt_stop.gif' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available'}
                    {/if}
                </td>
                {/if}
                {if isset($regactions.approveForce)}
                <td class="z-center">
                    {if $regactions.approveForce && !$reginfo.isverified}
                    <a href="{$regactions.approveForce|safetext}">{img src='db_update.gif' modname='core' set='icons/extrasmall' __title='Skip verification (approves, and creates a new user account)' __alt='Skip verification (approves, and creates a new user account)'}</a>
                    {else}
                    {img style='visibility: hidden;' src='agt_stop.gif' modname='core' set='icons/extrasmall' __title='Not available' __alt='Not available'}
                    {/if}
                </td>
                {/if}
                {/strip}
            </tr>
            {/foreach}
        </tbody>
    </table>

    {if !empty($pager)}{pager rowcount=$pager.rowcount limit=$pager.limit posvar=$pager.posvar}{/if}

    <table class="z-admintable">
        <tbody>
            <tr>
                <th colspan="4" class="z-center">{gt text='Legend'}</th>
            </tr>
            <tr>
                <td style="vertical-align: top; width: 50%;">
                    <table class="z-admintable">
                        <thead>
                            <tr>
                                <th colspan="2" class="z-center">{gt text='Approval'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="z-odd">
                                <td>{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Approved' __alt='Approved'}</td>
                                <td>{gt text='An administrator has approved the registration, or approval was not required when the registration was completed.'}</td>
                            </tr>
                            <tr class="z-even">
                                <td>{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Pending approval' __alt='Pending approval'}</td>
                                <td>{gt text='Waiting for an administrator to approve the registration.'}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="vertical-align: top; width: 50%;">
                    <table class="z-admintable">
                        <thead>
                            <tr>
                                <th colspan="2" class="z-center">{gt text='Verification'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="z-odd">
                                <td>{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Verified' __alt='Verified'}</td>
                                <td>{gt text='The user has completed the e-mail verification process, or e-mail verification was not required when the registration was completed.'}</td>
                            </tr>
                            <tr class="z-even">
                                <td>{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Pending verification of e-mail address' __alt='Pending verification of e-mail address'}</td>
                                <td>{gt text='An e-mail has been sent to the registered e-mail address, but the user has not yet responded.'}</td>
                            </tr>
                            <tr class="z-odd">
                                <td>{img modname='core' set='icons/extrasmall' src='mail_delete.gif' __title='Verification e-mail message not yet sent' __alt='Verification e-mail message not yet sent'}</td>
                                <td>{gt text='A verification e-mail has not been sent to the registered e-mail address. If it is not yet approved, then it will be sent on approval. If it is approved, then the administrator chose not to send the verification e-mail.'}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    {else}
    <p class="z-informationmsg">{gt text='There are no pending registration applications to review.'}</p>
    {/if}
</div>
