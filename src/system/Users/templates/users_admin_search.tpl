{gt text="Find and e-mail users" assign=templatetitle}
{include file="users_admin_menu.htm"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='Users' src='admin.gif' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    <form class="z-form" method="post" action="{modurl modname="Users" type="admin"  func="listusers"}">
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

        {configgetvar name='profilemodule' assign='profilemodule'}
        {if $profilemodule}
            {modfunc modname=$profilemodule type='form' func='search'}
        {/if}

        <div class="z-formbuttons z-buttons">
            {button src=button_ok.gif set=icons/extrasmall __alt="Search" __title="Search" __text="Search"}
            <a href="{modurl modname=Users type=admin func=view}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </form>
</div>
