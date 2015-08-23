{if ($callbackFunc == 'mailUsers')}
{gt text='Find and e-mail users' assign='templatetitle'}
{else}
{gt text='Find users' assign='templatetitle'}
{/if}

{adminheader}
<h3>
    {if ($callbackFunc == 'composeMail')}
    <span class="fa fa-envelope"></span>
    {else}
    <span class="fa fa-search"></span>
    {/if}
    {$templatetitle}
</h3>

<form id="users_search" class="form-horizontal" role="form" method="post" action="{route name='zikulausersmodule_admin_'|cat:$callbackFunc|default:'search'|lower}">
    <div>
        <input id="users_search_csrftoken" name="csrftoken" type="hidden" value="{insert name='csrftoken'}" />
        <input id="users_search_formid" name="formid" type="hidden" value="users_search" />
        <fieldset>
            <legend>{gt text="Find users"}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_uname">{gt text="User name"}</label>
                <div class="col-sm-9">
                    <input id="users_uname" class="form-control" type="text" name="uname" size="40" maxlength="40" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_email">{gt text="E-mail address"}</label>
                <div class="col-sm-9">
                    <input id="users_email" class="form-control" type="text" name="email" size="40" maxlength="255" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_ugroup">{gt text="Group membership"}</label>
                <div class="col-sm-9">
                    <select id="users_ugroup" class="form-control" name="ugroup">
                        <option value="0" selected="selected">{gt text="Any group"}</option>
                        {section name=group loop=$groups}
                        <option value="{$groups[group].gid}">{$groups[group].name}</option>
                        {/section}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_regdateafter">{gt text="Registration date after (yyyy-mm-dd)"}</label>
                <div class="col-sm-9">
                    <input id="users_regdateafter" class="form-control" type="text" name="regdateafter" size="40" maxlength="10" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="users_regdatebefore">{gt text="Registration date before (yyyy-mm-dd)"}</label>
                <div class="col-sm-9">
                    <input id="users_regdatebefore" class="form-control" type="text" name="regdatebefore" size="40" maxlength="10" />
                </div>
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

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text="Search"}">
                    {gt text='Search'}
                </button>
                <a class="btn btn-danger" href="{route name='zikulausersmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
