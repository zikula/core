{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type='delete' size='small'}
    <h3>{gt text='Delete registry entry'}</h3>
</div>

<p class="z-warningmsg">
    {gt text='Do you really want to delete this registry entry?'}<br />
    {gt text='Module'}: <strong>{$data.modname}</strong><br />
    {gt text='Table'}: <strong>{$data.table}</strong><br />
    {gt text='Property name'}: <strong>{$data.property}</strong>
</p>

<form class="z-form" action="{modurl modname='Categories' type='adminform' func='editregistry' mode='delete'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="id" value="{$data.id}" />
        <input type="hidden" name="mode" value="delete" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            <div class="z-buttons z-formbuttons">
                {button class='z-btgreen' src='button_ok.png' set='icons/extrasmall' __alt='Delete' __title='Delete' __text='Delete'}
                <a class="z-btred" href="{modurl modname='Categories' type='admin' func='editregistry'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}