{include file="groups_admin_menu.htm"}
{gt text="Delete" assign=templatetitle}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=editdelete.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to delete this group?"}</p>
    <form class="z-form" action="{modurl modname="Groups" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Groups"}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="gid" value="{$gid|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.gif set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname=Groups type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
