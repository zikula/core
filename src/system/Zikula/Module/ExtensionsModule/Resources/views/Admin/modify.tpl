{adminheader}
<h3>
    <span class="icon icon-pencil"></span>
    {gt text="Edit module"} - {modgetinfo modid=$id info=displayname}
</h3>

<form class="form-horizontal" role="form" action="{modurl modname="Extensions" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="id" value="{$id|safetext}" />
        <fieldset>
            <legend>{gt text="Module"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="modules_newdisplayname">{gt text="Module display name"}</label>
                <div class="col-lg-9">
                <input id="modules_newdisplayname" name="newdisplayname" type="text" class="form-control" size="30" maxlength="64" value="{$displayname|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="modules_newurl">{gt text="Module URL"}</label>
                <div class="col-lg-9">
                <input id="modules_newurl" name="newurl" type="text" class="form-control" size="30" maxlength="64" value="{$url|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="modules_newdescription">{gt text="Description"}</label>
                <div class="col-lg-9">
                <textarea class="form-control" id="modules_newdescription" name="newdescription" cols="50" rows="10">{$description|safetext}</textarea>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Defaults"}</label>
                <div class="col-lg-9">
                <span><a id="restore_defaults" href="{modurl modname="Extensions" type="admin" func="modify" id=$id restore=true}">{gt text="Restore now"}</a> ({gt text="This may break your existing indexed URLs"})</span>
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
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