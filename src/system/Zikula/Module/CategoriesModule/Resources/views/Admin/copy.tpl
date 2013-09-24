{adminheader}
<h3>
    {icon type="copy" size="small"}
    {gt text="Copy category"}
</h3>

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
                {button class="btn btn-success" __alt="Copy" __title="Copy" __text="Copy"}
                <a class="btn btn-danger" href="{modurl modname=ZikulaCategoriesModule type=admin func=index}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}