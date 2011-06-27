{ajaxheader ui=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Extensions" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        {insert name='csrftoken' assign='csrftoken'}
        <input type="hidden" name="csrftoken" value="{$csrftoken}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="modules_itemsperpage">{gt text="Items per page"}</label>
                <input id="modules_itemsperpage" type="text" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label>{gt text="Module defaults"}</label>
                <span><a id="restore_defaults" href="{modurl modname="Extensions" type="admin" func="view" defaults=true csrftoken=$csrftoken}">{gt text="Hard module regenerate to reset displayname, url and description to defaults"}</a></span>
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