{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete theme %s" tag1=$name|safetext}</h3>
</div>

<p class="z-warningmsg">{gt text="Do you really want to delete this theme?"}</p>
<form class="z-form" action="{modurl modname=Theme type=admin func=delete}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
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
            {button class="z-btgreen" src=button_ok.png set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
            <a class="z-btred" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}