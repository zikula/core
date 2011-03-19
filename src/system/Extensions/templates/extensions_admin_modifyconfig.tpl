{include file="extensions_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="config" size="large"}</div>
    <h2>{gt text="Settings"}</h2>
    <form class="z-form" action="{modurl modname="Extensions" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            {insert name='csrftoken' assign='csrftoken'}
            <input type="hidden" name="csrftoken" value="{$csrftoken}" />
            <fieldset>
                <legend>{gt text="General settings"}</legend>
                <div class="z-formrow">
                    <label for="modules_itemsperpage">{gt text="Items per page"}</label>
                    <input id="modules_itemsperpage" type="text" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
                </div>
                <div class="z-formrow">
                    <label>{gt text="Module defaults"}</label>
                    <span><a href="{modurl modname="Extensions" type="admin" func="view" defaults=true csrftoken=$csrftoken}">{gt text="Hard module regenerate to reset displayname, url and description to defaults"}</a></span>
                </div>
            </fieldset>

            <div class="z-buttons z-formbuttons">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Extensions type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
