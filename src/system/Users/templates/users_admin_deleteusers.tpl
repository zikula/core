{gt text='Delete user account' assign=templatetitle}
{include file='users_admin_menu.htm'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='editdelete.gif' set='icons/large' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    <p class="z-warningmsg">{gt text='Do you really want to delete this user account?'}</p>

    <form class="z-form" action="{modurl modname='Users' type='admin' func='processusers' op='delete'}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Users'}" />
            <input type="hidden" name="do" value="yes" />
            <fieldset>
                <legend>{gt text='Confirmation prompt'}</legend>
                <input type="hidden" name="userid[]" value="{$userid|safetext}" />
                <div class="z-formrow">
                    <label>{gt text='User name'}</label>
                    <span>{$uname}</span>
                </div>
            </fieldset>
            <div class="z-formbuttons z-buttons">
                {button class="z-btgreen" src='button_ok.gif' set='icons/extrasmall' __alt='Delete user account' __title='Delete user account' __text='Delete user account'}
                <a class="z-btred" href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </form>
</div>

