{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{gt text="Create new block position"}</h3>
</div>

<p class="z-informationmsg">{gt text="After create this block position, you will be able to assign some blocks for it, and adjust the order you want them to be displayed."}</p>

<form class="z-form" action="{modurl modname="Blocks" type="admin" func="createposition"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="New block position"}</legend>
            <div class="z-formrow">
                <label for="blocks_positionname">{gt text="Name"}</label>
                <input type="text" id="blocks_positionname" name="position[name]" value="{$name|safetext}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="Characters allowed: a-z, A-Z, 0-9, dash (-) and underscore (_)."}</em>
            </div>
            <div class="z-formrow">
                <label for="blocks_positiondescription">{gt text="Description"}</label>
                <textarea name="position[description]" id="blocks_positiondescription" rows="5" cols="30"></textarea>
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}