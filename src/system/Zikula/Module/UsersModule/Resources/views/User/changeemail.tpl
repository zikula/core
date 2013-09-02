{gt text='E-mail address manager' assign='templatetitle'}
{include file='User/menu.tpl'}

<p class="alert alert-info">
    {gt text="Notice: Please enter your new e-mail address, the same address again for verification, and then click 'Save'. The site uses this address to send you mail (when you request a new password, for instance). Your currently-recorded e-mail address is <strong>'%s'</strong>." tag1=$coredata.user.email}
    {gt text="You will receive an e-mail to your new e-mail address to confirm the change."}
</p>

<form id="changeemail" class="form-horizontal" role="form" action="{modurl modname="Users" type="user" func="updateemail"}" method="post">
    <div>
        <input type="hidden" id="changeemailcsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Update e-mail address"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_newemail">{gt text="New e-mail address"}</label>
                <div class="col-lg-9">
                    <input id="users_newemail" class="form-control" type="text" name="newemail" value="" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_newemailagain">{gt text="New e-mail address again (for verification)"}</label>
                <div class="col-lg-9">
                    <input id="users_newemailagain" class="form-control" type="text" name="newemailagain" value="" />
                </div>
            </div>
        </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text='Save'}
            <a href="{modurl modname='ZikulaUsersModule' type='user' func='index'}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.png set=icons/extrasmall  __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
