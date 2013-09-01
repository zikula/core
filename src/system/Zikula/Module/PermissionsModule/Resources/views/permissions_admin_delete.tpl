{adminheader}
{include file="permissions_admin_header.tpl"}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete permission rule"}</h3>
</div>

<p class="alert alert-warning">{gt text="Do you really want to delete this permission rule?"}</p>
<form class="form-horizontal" role="form" action="{modurl modname="Permissions" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="pid" value="{$pid|safetext}" />
        <input type="hidden" name="permgrp" value="{$permgrp|safetext}" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="btn btn-default" class="z-btred" href="{modurl modname=Permissions type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}