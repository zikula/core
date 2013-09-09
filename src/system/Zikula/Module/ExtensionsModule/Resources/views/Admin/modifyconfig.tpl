{ajaxheader ui=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname="Extensions" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        {insert name='csrftoken' assign='csrftoken'}
        <input type="hidden" name="csrftoken" value="{$csrftoken}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="modules_itemsperpage">{gt text="Items per page"}</label>
                <div class="col-lg-9">
                <input id="modules_itemsperpage" type="text" class="form-control" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Module defaults"}</label>
                <div class="col-lg-9">
                <span><a id="restore_defaults" href="{modurl modname="Extensions" type="admin" func="view" defaults=true csrftoken=$csrftoken}">{gt text="Hard module regenerate to reset displayname, url and description to defaults"}</a></span>
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title={gt text="Save"}>
                    {gt text="Save"}
                </button>
                <a class="btn btn-danger" href="{modurl modname=Extensions type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
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