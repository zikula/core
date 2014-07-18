{gt text='Account information and password recovery' assign='templatetitle'}

{include file='User/menu.tpl'}
<p>{gt text='Please select one of the following:'}</p>
<ul>
    <li><a href="{modurl modname='ZikulaUsersModule' type='user' func='lostuname'}">{gt text='I have forgotten my account information (for example, my user name).'}</a></li>
    <li><a href="{modurl modname='ZikulaUsersModule' type='user' func='lostpassword'}">{gt text='I have forgotten my password.'}</a></li>
    <li><a href="{modurl modname='ZikulaUsersModule' type='user' func='lostpasswordcode'}">{gt text='I have received a password recovery code, and would like to enter it.'}</a></li>
</ul>