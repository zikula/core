{include file="securitycenter_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="Purge IDS Log"}</div>
    
    <h2>{gt text="Purge IDS Log"}</h2>
   
    <p class="z-warningmsg">{gt text="Do you really want to delete the entire IDS log?"}</p>

    <form class="z-form" action="{modurl modname="SecurityCenter" type="admin" func="purgeidslog"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="SecurityCenter"}" />
            <input type="hidden" name="confirmation" value="1" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.gif set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname=SecurityCenter type=admin func=viewidslog}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
    
</div>
