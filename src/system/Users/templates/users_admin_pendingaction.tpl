{if $action eq 'approve'}
    {gt text='Approve registration application for %s' tag1=$item.uname assign='templatetitle'}
{else}
    {gt text='Deny registration application for %s' tag1=$item.uname assign='templatetitle'}
{/if}

{include file='users_admin_menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="user" size="large"}</div>
    <h2>{$templatetitle}</h2>

    {if $action neq 'approve' and $action neq 'deny'}
        <p class="z-errormsg">{gt text='Error! Could not load data.'}</p>
    {else}
    <form class="z-form" action="{modurl modname='Users' type='admin' func='processusers' op=$action}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Users'}" />
            <input type="hidden" name="userid" value="{$userid|safetext}" />
            <input type="hidden" name="action" value="{$action|safetext}" />
            <fieldset>
                <legend>{$templatetitle}</legend>
                <div class="z-formrow">
                    <label for="users_tag">{gt text='Confirm action for registration application'}</label>
                    <input id="users_tag" name="tag" type="checkbox" />
                </div>
            </fieldset>
            <div class="z-formbuttons z-buttons">
                {button src='button_ok.png' set='icons/extrasmall' __alt='Accept' __title='Accept' __text='Accept'}
            </div>
        </div>
    </form>
    {/if}
</div>
