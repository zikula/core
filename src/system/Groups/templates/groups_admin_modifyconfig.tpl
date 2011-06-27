{* Do not allow editing of primaryadmingroup. For now it is read-only. *}
{adminheader}
{include file="groups_admin_header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Groups" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="groups_itemsperpage">{gt text="Items per page"}</label>
                <input id="groups_itemsperpage" type="text" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="groups_defaultgroupid">{gt text="Initial user group"}</label>
                <select id="groups_defaultgroupid" name="defaultgroupid">
                    {html_options options=$groups selected=$defaultgroupid}
                </select>
            </div>
            <div class="z-formrow">
                <label for="groups_hideclosed">{gt text="Hide closed groups"}</label>
                <input id="groups_hideclosed" name="hideclosed" type="checkbox"{if $hideclosed eq 1} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="groups_mailwarning">{gt text="Receive e-mail alert when there are new applicants"}</label>
                <input id="groups_mailwarning" name="mailwarning" type="checkbox"{if $mailwarning eq 1} checked="checked"{/if} />
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Groups type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}
