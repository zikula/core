{include file="modules_admin_menu.htm"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=configure.gif set=icons/large __alt="Settings"}</div>
    <h2>{gt text="Settings"}</h2>
    <form class="z-form" action="{modurl modname="Modules" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            {securityutil_generateauthkey name="generateauthkey" module="Modules" assign="authid"}
            <input type="hidden" name="authid" value="{$authid}" />
            <fieldset>
                <legend>{gt text="General settings"}</legend>
                <div class="z-formrow">
                    <label for="modules_itemsperpage">{gt text="Items per page"}</label>
                    <input id="modules_itemsperpage" type="text" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
                </div>
                <div class="z-formrow">
                    <label>{gt text="Module defaults"}</label>
                    <span><a href="{modurl modname="Modules" type="admin" func="view" defaults=true authid=$authid}">{gt text="Hard module regenerate to reset displayname, url and description to defaults"}</a></span>
                </div>
            </fieldset>
            {modcallhooks hookobject=module hookaction=modifyconfig module=Modules}
            <div class="z-buttons z-formbuttons">
                {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Modules type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
