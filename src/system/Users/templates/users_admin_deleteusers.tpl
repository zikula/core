{include file='users_admin_menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="delete" size="large"}</div>
    <h2>{gt text='Delete user account' plural='Delete user accounts' count=$users|@count}</h2>
    <p class="z-warningmsg">{gt text='Do you really want to delete this user account?' plural='Do you really want to delete these user accounts?' count=$users|@count}</p>

    <form class="z-form" action="{modurl modname='Users' type='admin' func='deleteUsers'}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
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
                {button class="z-btgreen" src='button_ok.png' set='icons/extrasmall' __alt='Delete user account' __title='Delete user account' __text='Delete user account'}
                <a class="z-btred" href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
</div>
