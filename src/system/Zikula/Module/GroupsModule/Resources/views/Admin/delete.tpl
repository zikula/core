{adminheader}
{include file="Admin/header.tpl"}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete"}</h3>
</div>

<p class="alert alert-warning">{gt text="Do you really want to delete group '%s'?" tag1=$item.name|safetext}</p>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="delete" gid=$item.gid}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="confirmation" value="1" />

        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="btn btn-default" class="z-btred" href="{modurl modname='ZikulaGroupsModule' type='admin' func='view'}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}