{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete page configuration assignment"} - {$name|safetext} - {$pcname|safetext}</h3>
</div>

<p class="z-warningmsg">{gt text="Do you really want to delete this page configuration assignment?"}</p>
<form class="z-form" action="{modurl modname=Theme type=admin func=deletepageconfigurationassignment}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="themename" value="{$name|safetext}" />
        <input type="hidden" name="pcname" value="{$pcname|safetext}" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-buttons z-formbuttons">
                {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                <a class="z-btred" href="{modurl modname=Theme type=admin func=pageconfigurations themename=$name}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}