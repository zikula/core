{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="ZikulaCategoriesModule" type="adminform" func="preferences"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="userrootcat">{gt text="Root category for user categories"}</label>
                <div class="col-lg-9">
                {selector_category category=1 name="userrootcat" field="path" selectedValue=$userrootcat defaultValue="0" defaultText="ChooseOne" includeLeaf=0 doReplaceRootCat=false editLink=0}
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="categories_allowusercatedit">{gt text="Allow users to edit their own categories"}</label>
                <div class="col-lg-9">
                <input id="categories_allowusercatedit" type="checkbox" name="allowusercatedit" value="1"{if ($allowusercatedit)} checked="checked"{/if} />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="categories_autocreateusercat">{gt text="Automatically create user category root folder"}</label>
                <div class="col-lg-9">
                <input id="categories_autocreateusercat" type="checkbox" name="autocreateusercat" value="1"{if ($autocreateusercat)} checked="checked"{/if} />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="categories_autocreateuserdefaultcat">{gt text="Automatically create user default category"}</label>
                <div class="col-lg-9">
                <input id="categories_autocreateuserdefaultcat" type="checkbox" name="autocreateuserdefaultcat" value="1"{if ($autocreateuserdefaultcat)} checked="checked"{/if} />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="categories_permissionsall">{gt text="Require access to all categories for one item (relevant when using multiple categories per content item)"}</label>
                <div class="col-lg-9">
                <input id="categories_permissionsall" type="checkbox" name="permissionsall" value="1"{if ($permissionsall)} checked="checked"{/if} />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="categories_userdefaultcatname">{gt text="Default user category"}</label>
                <div class="col-lg-9">
                <input id="categories_userdefaultcatname" type="text" class="form-control" name="userdefaultcatname" value="{$userdefaultcatname}" />
            </div>
        </div>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{modurl modname="ZikulaCategoriesModule" type="admin" func="preferences"}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}