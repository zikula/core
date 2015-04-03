{pageaddvar name='javascript' value='polyfill'}

{gt text='Account information recovery' assign='templatetitle'}

{include file='User/menu.tpl'}
<p class="alert alert-info">{gt text="Please enter your e-mail address below and click the 'Submit' button. You will be sent an e-mail with your account information."}</p>
<form class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_lostuname'}" method="post">
    <fieldset>
        <input type="hidden" id="lostunamecsrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <div class="form-group">
            <label class="col-sm-3 control-label" for="users_email">{gt text='E-mail address'}</label>
            <div class="col-sm-9">
                <input id="users_email" type="email" class="form-control" name="email" size="40" maxlength="60" value="{$email|safetext}" required="required" />
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            {button class="btn btn-success" __alt='Submit' __title='Submit' __text='Submit'}
        </div>
    </div>
</form>