{adminheader}
{include file='Admin/header.tpl'}

<div class="z-admin-content-pagetitle">
    {icon type='delete' size='small'}
    <h3>{gt text='Remove user from group'}</h3>
</div>

<p class="alert alert-warning">{gt text='Do you really want to remove user "%1$s" from group "%2$s"?' tag1=$uname tag2=$group.name}</p>

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaGroupsModule' type='admin' func='removeuser' gid=$gid|safetext uid=$uid|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />

        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class='z-btgreen' src='button_ok.png' set='icons/extrasmall' __alt='Remove' __title='Remove' __text='Remove'}
                    <a class="btn btn-default" class="z-btred" href="{modurl modname='ZikulaGroupsModule' type='admin' func='groupmembership' gid=$gid}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}