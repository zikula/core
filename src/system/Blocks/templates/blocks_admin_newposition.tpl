{gt text="Create new block position" assign=templatetitle}
{include file="blocks_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=filenew.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <form class="z-form" action="{modurl modname="Blocks" type="admin" func="createposition"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Blocks"}" />
            <fieldset>
                <legend>{gt text="New block position"}</legend>
                <div class="z-formrow">
                    <label for="blocks_positionname">{gt text="Name"}</label>
                    <input type="text" id="blocks_positionname" name="position[name]" size="50" maxlength="255" />
                    <em class="z-formnote z-sub">{gt text="Characters allowed: a-z, A-Z, 0-9, dash (-) and underscore (_)."}</em>
                </div>
                <div class="z-formrow">
                    <label for="blocks_positiondescription">{gt text="Description"}</label>
                    <textarea name="position[description]" id="blocks_positiondescription" rows="5" cols="30"></textarea>
                </div>
            </fieldset>
            <div class="z-buttons z-formbuttons">
                {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
