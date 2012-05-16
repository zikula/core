{adminheader}
{include file="Admin/header.tpl"}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete"}</h3>
</div>

<p class="z-warningmsg">{gt text="Do you really want to delete group '%s'?" tag1=$item.name|safetext}</p>

<form class="z-form" action="{modurl modname="GroupsModule" type="admin" func="delete" gid=$item.gid}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="confirmation" value="1" />

        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-buttons z-formbuttons">
                {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                <a class="z-btred" href="{modurl modname='GroupsModule' type='admin' func='view'}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}