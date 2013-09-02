{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="copy" size="small"}
    <h3>{gt text="Copy category"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaCategoriesModule" type="adminform" func="copy"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="cid" value="{$category.id}" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Category"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Name"}</label>
                <div class="col-lg-9">
                <span>{$category.name}</span>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Path"}</label>
                <div class="col-lg-9">
                <span>{$category.path}</span>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="subcat_copy">{gt text="Copy this category and all sub-categories of this category"}</label>
                <div class="col-lg-9">
                {$categorySelector}
            </div>
        </div>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src=button_ok.png set=icons/extrasmall __alt="Copy" __title="Copy" __text="Copy"}
                <a class="btn btn-default" href="{modurl modname=ZikulaCategoriesModule type=admin func=index}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}