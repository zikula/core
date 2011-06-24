{adminheader}
{include file='groups_admin_header.tpl'}

<div class="z-admin-content-pagetitle">
    {icon type='delete' size='small'}
    <h3>{gt text='Remove user from group'}</h3>
</div>

<p class="z-warningmsg">{gt text='Do you really want to remove user "%1$s" from group "%2$s"?' tag1=$uname tag2=$group.name}</p>

<form class="z-form" action="{modurl modname='Groups' type='admin' func='removeuser'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="gid" value="{$gid|safetext}" />
        <input type="hidden" name="uid" value="{$uid|safetext}" />
        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            <div class="z-buttons z-formbuttons">
                {button class='z-btgreen' src='button_ok.png' set='icons/extrasmall' __alt='Remove' __title='Remove' __text='Remove'}
                <a class="z-btred" href="{modurl modname='Groups' type='admin' func='groupmembership' gid=$gid}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}