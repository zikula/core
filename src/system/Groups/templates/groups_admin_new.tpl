{adminheader}
{include file="groups_admin_header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{gt text="Create new group"}</h3>
</div>

<form class="z-form" action="{modurl modname="Groups" type="admin" func="create"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" id="csrftoken" name="csrftoken" value="{insert name="csrftoken"}" />
        <fieldset>
            <legend>{gt text="New group"}</legend>
            <div class="z-formrow">
                <label for="groups_name">{gt text="Name"}</label>
                <input id="groups_name" name="name" type="text" size="30" maxlength="30" />
            </div>
            <div class="z-formrow">
                <label for="groups_gtype">{gt text="Type"}</label>
                <select id="groups_gtype" name="gtype">
                    {html_options options=$grouptype default='0'}
                </select>
            </div>
            <div class="z-formrow">
                <label for="groups_state">{gt text="State"}</label>
                <select id="groups_state" name="state">
                    {html_options options=$groupstate default='0'}
                </select>
            </div>
            <div class="z-formrow">
                <label for="groups_nbumax">{gt text="Maximum membership"}</label>
                <input id="groups_nbumax" name="nbumax" type="text" size="10" maxlength="10" value="0" />
            </div>
            <div class="z-formrow">
                <label for="groups_description">{gt text="Description"}</label>
                <textarea id="groups_description" name="description" cols="50" rows="5"></textarea>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Groups type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}