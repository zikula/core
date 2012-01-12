{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text='Delete user account' plural='Delete user accounts' count=$users|@count}</h3>
</div>

<p class="z-warningmsg">{gt text='Do you really want to delete this user account?' plural='Do you really want to delete these user accounts?' count=$users|@count}</p>

<form class="z-form" action="{modurl modname='Users' type='admin' func='deleteUsers'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="process_delete" value="true" />
        {foreach from=$users item='user' key='key' name='users'}
        <fieldset>
            <input type="hidden" name="userid[{$key}]" value="{$user.uid|safetext}" />
            <div class="z-formrow">
                <label>{gt text='User name'}</label>
                <span><strong>{$user.uname|safetext}</strong></span>
            </div>
            {notifyevent eventname='module.users.ui.form_delete' eventsubject=null id=$user.uid assign="eventData"}
            {foreach item='eventDisplay' from=$eventData}
            {$eventDisplay}
            {/foreach}
            {notifydisplayhooks eventname='users.ui_hooks.user.form_delete' id=$user.uid}
        </fieldset>
        {/foreach}
        <div class="z-formbuttons z-buttons">
            {gt text='Delete user account' plural='Delete user accounts' count=$users|@count assign='buttonText'}
            {button class="z-btgreen" src='button_ok.png' set='icons/extrasmall' alt=$buttonText title=$buttonText text=$buttonText}
            <a class="z-btred" href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
