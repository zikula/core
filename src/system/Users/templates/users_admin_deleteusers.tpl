{include file='users_admin_menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="delete" size="large"}</div>
    <h2>{gt text='Delete user account'}</h2>
    <p class="z-warningmsg">{gt text='Do you really want to delete this user account?'}</p>

    <form class="z-form" action="{modurl modname='Users' type='admin' func='deleteUsers'}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <fieldset>
                <legend>{gt text='Confirmation prompt'}</legend>
                <input type="hidden" name="userid[]" value="{$userid|safetext}" />
                <div class="z-formrow">
                    <label>{gt text='User name'}</label>
                    <span>{$uname}</span>
                </div>
            </fieldset>
            {notifyevent eventname='module.users.ui.form_delete' eventsubject=null id=$userid assign="eventData"}
            {foreach item='eventDisplay' from=$eventData}
                {$eventDisplay}
            {/foreach}
            <div class="z-formbuttons z-buttons">
                {button class="z-btgreen" src='button_ok.png' set='icons/extrasmall' __alt='Delete user account' __title='Delete user account' __text='Delete user account'}
                <a class="z-btred" href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
</div>

