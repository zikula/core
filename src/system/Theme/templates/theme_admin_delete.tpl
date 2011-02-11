{include file='theme_admin_menu.tpl'}
{gt text="Delete theme" assign=templatetitle}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=14_layer_deletelayer.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle} {$name|safetext}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to delete this theme?"}</p>
    <form class="z-form" action="{modurl modname=Theme type=admin func=delete}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module=Theme}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="themename" value="{$name|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-formrow">
                    <label for="deletefiles">{gt text="Also delete theme files, if possible"}</label>
                    <input type="checkbox" id="deletefiles" name="deletefiles" value="1" />
                </div>
                <div class="z-informationmsg">{gt text="Please delete the Theme folder before pressing OK or the Theme will not be deleted."}</div>
            </fieldset>
            <div class="z-buttons z-formbuttons">
                {button class="z-btgreen" src=button_ok.gif set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                <a class="z-btred" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
