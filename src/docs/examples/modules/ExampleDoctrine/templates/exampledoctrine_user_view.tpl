<h3>List of users and their passwords :-P</h3>

{foreach from=$users item='u'}
Username: {$u.username}, Password: {$u.password}<br />
{/foreach}

<form class="z-form" action="{modurl modname='ExampleDoctrine' type='user' func='add'}" method="post">
    <fieldset>
        <legend>{gt text='New user'}</legend>
        <div class="z-formrow">
            <label for="exampledoctrine_username">{gt text='User name'}</label>
            <input id="exampledoctrine_username" type="text" name="user[username]" size="21" maxlength="25" value="" />
        </div>
        <div class="z-formrow">
            <label for="exampledoctrine_password">{gt text='Password'}</label>
            <input id="exampledoctrine_password" type="text" name="user[password]" size="21" maxlength="60" value="" />
        </div>
    </fieldset>

    <div class="z-formbuttons">
        {button src='button_ok.gif' set='icons/small' __alt='Save' __title='Save'}
    </div>
</form>