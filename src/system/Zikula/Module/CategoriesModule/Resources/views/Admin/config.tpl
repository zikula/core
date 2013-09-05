{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="regenerate" size="small"}
    <h3>{gt text="Rebuild paths"}</h3>
</div>

<p class="alert alert-warning">{gt text="Are you sure you want to rebuild all the internal paths for categories?"}&nbsp;{gt text="Warning! If you have a large number of categories then this action may time out, or may exceed the memory limit configured within your PHP installation."}</p>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaCategoriesModule" type="adminform" func="rebuild_paths"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class="z-btgreen" class="btn btn-success" __alt="Rebuild paths" __title="Rebuild paths" __text="Rebuild paths"}
                    <a class="btn btn-danger" class="z-btred" href="{modurl modname=ZikulaCategoriesModule type=admin func=index}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}