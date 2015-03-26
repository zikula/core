{if $templatetitle|default:'' eq ''}
    {gt text='My account' assign='templatetitle'}
{/if}
{moduleheader modname='Users' type='user' title=$templatetitle setpagetitle=true insertstatusmsg=true}
