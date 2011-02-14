{include file="admin_admin_menu.tpl"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="delete" size="large"}</div>
    <h2>{gt text="Delete module category"}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to delete this module category?"}</p>
    <form class="z-form" action="{modurl modname="Admin" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Admin"}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="cid" value="{$cid|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname=Admin type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
