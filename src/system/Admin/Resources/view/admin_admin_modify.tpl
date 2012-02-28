{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit module category"}</h3>
</div>

<form class="z-form" action="{modurl modname="Admin" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="category[cid]" value="{$category.cid|safetext}" />
        <fieldset>
            <legend>{gt text="Module category"}</legend>
            <div class="z-formrow">
                <label for="admin_name">{gt text="Name"}</label>
                <input id="admin_name" name="category[name]" type="text" size="30" maxlength="50" value="{$category.name|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="admin_description">{gt text="Description"}</label>
                <textarea id="admin_description" name="category[description]" cols="50" rows="10">{$category.description|safetext}</textarea>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Admin type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            <a class="z-btblue" href="{modurl modname=Admin type=admin func=help fragment=modify}" title="{gt text="Help"}">{img modname=core src=agt_support.png set=icons/extrasmall __alt="Help" __title="Help"} {gt text="Help"}</a>
        </div>
    </div>
</form>
{adminfooter}
