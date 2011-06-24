{gt text="Membership application" assign=templatetitle}
{adminheader}
{include file="groups_admin_header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{$templatetitle}</h3>
</div>

{if $action != "deny" and $action != "accept"}
{gt text="Error! Could not load data."}
{else}
<form class="z-form" action="{modurl modname="Groups" type="admin" func="userupdate"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$gid|safetext}" />
        <input type="hidden" name="action" value="{$action|safetext}" />
        <input type="hidden" name="userid" value="{$userid|safetext}" />
        <input type="hidden" name="tag" value="1" />
        <fieldset>
            <legend>{$templatetitle}</legend>
            <div class="z-formrow">
                <label>{gt text="User name"}</label>
                <span>{usergetvar name="uname" uid=$userid|safetext}</span>
            </div>
            <div class="z-formrow">
                <label>{gt text="Membership application"}</label>
                <span>{$application|safehtml}</span>
            </div>
            {if $action == "deny"}
            <div class="z-formrow">
                <label for="groups_reason">{gt text="Reason"}</label>
                <textarea id="groups_reason" name="reason" cols="50" rows="8">{gt text="Sorry! This is a message to inform you with regret that your application for membership of the aforementioned private group has been rejected."}</textarea>
            </div>
            {/if}
            <div class="z-formrow">
                <label for="groups_sendtag">{gt text="Send notification"}</label>
                <select id="groups_sendtag" name="sendtag">
                    {html_options options=$sendoptions}
                </select>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {if $action == "deny"}
            <button type="submit">{img modname=core src=14_layer_deletelayer.png set=icons/extrasmall __alt="Deny" __title="Deny"} {gt text="Deny"}</button>
            {else}
            <button type="submit">{img modname=core src=button_ok.png set=icons/extrasmall __alt="Accept" __title="Accept"} {gt text="Accept"}</button>
            {/if}
        </div>
    </div>
</form>
{/if}
{adminfooter}