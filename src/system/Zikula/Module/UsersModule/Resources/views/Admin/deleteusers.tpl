{adminheader}
<h3>
    <span class="icon icon-trash"></span>
    {gt text='Delete user account' plural='Delete user accounts' count=$users|@count}
</h3>

<p class="alert alert-warning">{gt text='Do you really want to delete this user account?' plural='Do you really want to delete these user accounts?' count=$users|@count}</p>

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='admin' func='deleteUsers'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" name="process_delete" value="true" />
    {foreach from=$users item='user' key='key' name='users'}
    <fieldset>
        <input type="hidden" name="userid[{$key}]" value="{$user.uid|safetext}" />
        <div class="form-group">
            <label class="col-lg-3 control-label">{gt text='User name'}</label>
            <div class="col-lg-9">
                <div class="form-control-static">
                    <strong>{$user.uname|safetext}</strong>
                </div>
            </div>
            {notifyevent eventname='module.users.ui.form_delete' eventsubject=null id=$user.uid assign="eventData"}
            {foreach item='eventDisplay' from=$eventData}
            {$eventDisplay}
            {/foreach}
            {notifydisplayhooks eventname='users.ui_hooks.user.form_delete' id=$user.uid}
        </div>
    </fieldset>
    {/foreach}
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {gt text='Delete user account' plural='Delete user accounts' count=$users|@count assign='buttonText'}
            <button class="btn btn-success" title="{$buttonText}">
                {$buttonText}
            </button>
            <a class="btn btn-danger" href="{modurl modname='ZikulaUsersModule' type='admin' func='view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
