{include file='theme_admin_menu.tpl'}
{gt text="Delete page configuration assignment" assign=templatetitle}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=14_layer_deletelayer.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle} - {$name|safetext} - {$pcname|safetext}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to delete this page configuration assignment?"}</p>
    <form class="z-form" action="{modurl modname=Theme type=admin func=deletepageconfigurationassignment}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module=Theme}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="themename" value="{$name|safetext}" />
            <input type="hidden" name="pcname" value="{$pcname|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.gif set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname=Theme type=admin func=pageconfigurations themename=$name}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
