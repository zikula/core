{if $templatetitle|default:'' eq ''}
    {gt text='My account' assign='templatetitle'}
{/if}

{pagesetvar name='title' value=$templatetitle}

<h2>{$templatetitle}</h2>
{modulelinks modname='ZikulaUsersModule' type='user'}
{insert name='getstatusmsg'}
