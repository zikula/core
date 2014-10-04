{adminheader}
<h3>
    <span class="fa fa-eye"></span>
    {gt text='Registration for \'%1$s\'' tag1=$reginfo.uname}
</h3>

<div class="form-horizontal">
{include file='Admin/includeregistration.tpl'}


<div class="text-center">
    {strip}
    {assign var="regactions" value=$actions.list[$reginfo.uid]}
    {gt assign='titleIfSent' text='Resend verification code'}
    {gt assign='titleIfNotSent' text='Send verification code'}
    {* For the following, (isset($regactions.optname) == true) means that the current user can, in general, perform the operation; *}
    {* ($regactions.optname == true) means that the operation can be performed for that individual registration record. *}
    {if isset($regactions.approve) && $regactions.approve && !$reginfo.isverified}
    <a class="btn btn-success" href="{$regactions.approve|safetext}"><span class="fa fa-check"></span> {gt text='Approve'}</a>&nbsp;
    {elseif isset($regactions.approve) && $regactions.approve && $reginfo.isverified}
    <a class="btn btn-success" href="{$regactions.approve|safetext}"><span class="fa fa-plus"></span> {gt text='Approve and Add User'}</a>&nbsp;
    {/if}
    {if isset($regactions.deny) && $regactions.deny}
    <a class="btn btn-danger" href="{$regactions.deny|safetext}"><span class="fa fa-trash-o"></span> {gt text='Deny'}</a>&nbsp;
    {/if}
    {if isset($regactions.modify) && $regactions.modify}
    <a class="btn btn-warning" href="{$regactions.modify|safetext}"><span class="fa fa-pencil"></span> {gt text='Edit'}</a>&nbsp;
    {/if}

    {if isset($regactions.verify) && $regactions.verify}
    {if !empty($reginfo.verifycode)}
    {assign var='actionTitle' value=$titleIfSent}
    {else}
    {assign var='actionTitle' value=$titleIfNotSent}
    {/if}
    <a class="btn btn-info" href="{$regactions.verify|safetext}"><span class="fa fa-envelope"></span> {$actionTitle}</a>&nbsp;
    {/if}

    {if isset($regactions.approveForce) && $regactions.approveForce && !$reginfo.isverified}
        <a class="btn btn-success" href="{$regactions.approveForce|safetext}" title="title='Skip verification (approves, and creates a new user account)'}"><span class="fa fa-plus"></span> {gt text='Add user without verification'}</a>&nbsp;
    {/if}

    <a class="btn btn-default" href="{route name='zikulausersmodule_admin_viewregistrations' restoreview='true'}"><span class="fa fa-reply"></span> {gt text='Return to registrations'}</a>&nbsp;
    {/strip}
</div>

    
    
{adminfooter}