{adminheader}

<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Purge IDS Log"}</h3>
</div>

<ul class="navbar navbar-default">
    <li><a href="{modurl modname=SecurityCenter type=admin func="exportidslog"}" title="{gt text="Download the entire log to a csv file"}" class="smallicon smallicon-export">{gt text="Export IDS Log"}</a></li>
    <li><span class="smallicon smallicon-delete">{gt text="Purge IDS Log"}</span></li>
</ul>

<p class="alert alert-warning">{gt text="Do you really want to delete the entire IDS log?"}</p>

<form class="form-horizontal" role="form" action="{modurl modname="SecurityCenter" type="admin" func="purgeidslog"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="confirmation" value="1" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class="z-btgreen" class="btn btn-success" __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="btn btn-danger" class="z-btred" href="{modurl modname=SecurityCenter type=admin func=viewidslog}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}
