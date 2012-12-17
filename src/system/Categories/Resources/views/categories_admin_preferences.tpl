{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Categories" type="adminform" func="preferences"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="userrootcat">{gt text="Root category for user categories"}</label>
                {selector_category category=1 name="userrootcat" field="path" selectedValue=$userrootcat defaultValue="0" defaultText="ChooseOne" includeLeaf=0 doReplaceRootCat=false editLink=0}
            </div>
            <div class="z-formrow">
                <label for="categories_allowusercatedit">{gt text="Allow users to edit their own categories"}</label>
                <input id="categories_allowusercatedit" type="checkbox" name="allowusercatedit" value="1"{if ($allowusercatedit)} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="categories_autocreateusercat">{gt text="Automatically create user category root folder"}</label>
                <input id="categories_autocreateusercat" type="checkbox" name="autocreateusercat" value="1"{if ($autocreateusercat)} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="categories_autocreateuserdefaultcat">{gt text="Automatically create user default category"}</label>
                <input id="categories_autocreateuserdefaultcat" type="checkbox" name="autocreateuserdefaultcat" value="1"{if ($autocreateuserdefaultcat)} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="categories_permissionsall">{gt text="Require access to all categories for one item (relevant when using multiple categories per content item)"}</label>
                <input id="categories_permissionsall" type="checkbox" name="permissionsall" value="1"{if ($permissionsall)} checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="categories_userdefaultcatname">{gt text="Default user category"}</label>
                <input id="categories_userdefaultcatname" type="text" name="userdefaultcatname" value="{$userdefaultcatname}" />
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname="Categories" type="admin" func="preferences"}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}