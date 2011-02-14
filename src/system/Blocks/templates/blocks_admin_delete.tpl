{gt text="Delete block" assign=templatetitle}
{include file="blocks_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=trashcan_empty.png set=icons/large alt=$templatetitle}</div>
    <h2>{$blockname|safetext}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to delete this block?"}</p>
    <form class="z-form" action="{modurl modname="Blocks" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Blocks"}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="bid" value="{$bid|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
