{if ($callbackFunc == 'mailUsers')}
{gt text='Find and e-mail users' assign='templatetitle'}
{else}
{gt text='Find users' assign='templatetitle'}
{/if}

{adminheader}
<div class="z-admin-content-pagetitle">
    {if ($callbackFunc == 'composeMail')}{icon type='mail' size='small'}{/if}{icon type='search' size='small'}
    <h3>{$templatetitle}</h3>
</div>

<form id="users_search" class="z-form" method="post" action="{modurl modname='Users' type='admin' func=$callbackFunc|default:'search'}">
    <div>
        <input id="users_search_csrftoken" name="csrftoken" type="hidden" value="{insert name='csrftoken'}" />
        <input id="users_search_formid" name="formid" type="hidden" value="users_search" />
        <fieldset>
            <legend>{gt text="Find users"}</legend>
            <div class="z-formrow">
                <label for="users_uname">{gt text="User name"}</label>
                <input id="users_uname" type="text" name="uname" size="40" maxlength="40" />
            </div>
            <div class="z-formrow">
                <label for="users_email">{gt text="E-mail address"}</label>
                <input id="users_email" type="text" name="email" size="40" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_ugroup">{gt text="Group membership"}</label>
                <select id="users_ugroup" name="ugroup">
                    <option value="0" selected="selected">{gt text="Any group"}</option>
                    {section name=group loop=$groups}
                    <option value="{$groups[group].gid}">{$groups[group].name}</option>
                    {/section}
                </select>
            </div>
            <div class="z-formrow">
                <label for="users_regdateafter">{gt text="Registration date after (yyyy-mm-dd)"}</label>
                <input id="users_regdateafter" type="text" name="regdateafter" size="40" maxlength="10" />
            </div>
            <div class="z-formrow">
                <label for="users_regdatebefore">{gt text="Registration date before (yyyy-mm-dd)"}</label>
                <input id="users_regdatebefore" type="text" name="regdatebefore" size="40" maxlength="10" />
            </div>
        </fieldset>

        {if $callbackFunc == 'mailUsers'}
        {notifyevent eventname='module.users.ui.form_edit.mail_users_search' assign="eventData"}
        {else}
        {notifyevent eventname='module.users.ui.form_edit.search' assign="eventData"}
        {/if}

        {foreach item='eventDisplay' from=$eventData}
        {$eventDisplay}
        {/foreach}

        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Search' __title='Search' __text='Search'}
            <a href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
