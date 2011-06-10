{gt text="Rebuild paths" assign=templatetitle}
{include file="categories_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="regenerate" size="small"}</div>
    <h3>{$templatetitle}</h3>

    <p class="z-warningmsg">{gt text="Are you sure you want to rebuild all the internal paths for categories?"}&nbsp;{gt text="Warning! If you have a large number of categories then this action may time out, or may exceed the memory limit configured within your PHP installation."}</p>
    <form class="z-form" action="{modurl modname="Categories" type="adminform" func="rebuild_paths"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Rebuild paths" __title="Rebuild paths" __text="Rebuild paths"}
                    <a class="z-btred" href="{modurl modname=Categories type=admin func=main}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
