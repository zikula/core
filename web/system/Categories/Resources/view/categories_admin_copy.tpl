{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="copy" size="small"}
    <h3>{gt text="Copy category"}</h3>
</div>

<form class="z-form" action="{modurl modname="Categories" type="adminform" func="copy"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="cid" value="{$category.id}" />
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Category"}</legend>
            <div class="z-formrow">
                <label>{gt text="Name"}</label>
                <span>{$category.name}</span>
            </div>
            <div class="z-formrow">
                <label>{gt text="Path"}</label>
                <span>{$category.path}</span>
            </div>
            <div class="z-formrow">
                <label for="subcat_copy">{gt text="Copy this category and all sub-categories of this category"}</label>
                {$categorySelector}
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Copy" __title="Copy" __text="Copy"}
            <a href="{modurl modname=Categories type=admin func=main}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}