{ajaxheader ui=false}
{pageaddvar name='javascript' value='jquery'}
{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulaextensionsmodule_admin_updateconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    {insert name='csrftoken' assign='csrftoken'}
    <input type="hidden" name="csrftoken" value="{$csrftoken}" />
    <fieldset>
        <legend>{gt text='General settings'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="modules_itemsperpage">{gt text='Items per page'}</label>
            <div class="col-sm-9">
                <input id="modules_itemsperpage" type="text" class="form-control" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Module defaults'}</label>
            <div class="col-sm-9">
                <div class="form-control-static">
                    <a id="restoreDefaults" href="{route name='zikulaextensionsmodule_admin_view' defaults=true csrftoken=$csrftoken}">{gt text='Hard module regenerate to reset displayname, url and description to defaults'}</a>
                </div>
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
            <a class="btn btn-danger" href="{route name='zikulaextensionsmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}

<script type="text/javascript">
    (function ($) {
        $(document).ready(function() {
            $('#restoreDefaults').click(function(event) {
                event.preventDefault();
                if (confirm(Zikula.__('Do you really want to reset displayname, url and description to defaults? This may break your existing indexed URLs.')) == false) {
                    return;
                }
                window.location = $('#restoreDefaults').attr('href');
            });
        });
    })(jQuery);
</script>
