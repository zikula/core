{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit module"} - {modgetinfo modid=$id info=displayname}</h3>
</div>

<form class="z-form" action="{modurl modname="Extensions" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="id" value="{$id|safetext}" />
        <fieldset>
            <legend>{gt text="Module"}</legend>
            <div class="z-formrow">
                <label for="modules_newdisplayname">{gt text="Module display name"}</label>
                <input id="modules_newdisplayname" name="newdisplayname" type="text" size="30" maxlength="64" value="{$displayname|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="modules_newurl">{gt text="Module URL"}</label>
                <input id="modules_newurl" name="newurl" type="text" size="30" maxlength="64" value="{$url|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="modules_newdescription">{gt text="Description"}</label>
                <textarea id="modules_newdescription" name="newdescription" cols="50" rows="10">{$description|safetext}</textarea>
            </div>
            <div class="z-formrow">
                <label>{gt text="Defaults"}</label>
                <span><a id="restore_defaults" href="{modurl modname="Extensions" type="admin" func="modify" id=$id restore=true}">{gt text="Restore now"}</a> ({gt text="This may break your existing indexed URLs"})</span>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Extensions type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}

<script type="text/javascript">
    $('restore_defaults').observe('click',function(event){
        event.preventDefault();
        Zikula.UI.Confirm(Zikula.__('Do you really want to reset displayname, url and description to defaults? This may break your existing indexed URLs.'),Zikula.__('Confirmation prompt'),function(res){
            if (res) {
                window.location = $('restore_defaults').readAttribute('href');
            }
        });
    });
</script>