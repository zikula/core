{adminheader}
<h3>
    <span class="fa fa-download"></span>
    {gt text='Export IDS Log'}
</h3>

<ul class="navbar navbar-default navbar-modulelinks">
    <li class="active">
        <span class="fa fa-download"> {gt text='Export IDS Log'}</span>
    </li>
    <li>
        <a href="{route name='zikulasecuritycentermodule_admin_purgeidslog'}" title="{gt text='Delete the entire log'}" class="fa fa-trash-o"> {gt text='Purge IDS Log'}</a>
    </li>
</ul>

<form class="form-horizontal" role="form" action="{route name='zikulasecuritycentermodule_admin_exportidslog'}" method="post" enctype="multipart/form-data">
    <fieldset>
        <input type="hidden" name="confirmed" value="1" />
        <legend>{gt text='Export Options'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_export_titles">{gt text='Export Title Row'}</label>
            <div class="col-sm-9">
                <input id="securitycenter_export_titles" type="checkbox" name="exportTitles" value="1" checked="checked" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_export_file">{gt text='CSV filename'}</label>
            <div class="col-sm-9">
                <input id="securitycenter_export_file" type="text" class="form-control" name="exportFile" size="30" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_export_delimiter">{gt text='CSV delimiter'}</label>
            <div class="col-sm-9">
                <select class="form-control" id="securitycenter_export_delimiter" name="delimiter">
                    <option value="1">{gt text='Comma'} (,)</option>
                    <option value="2">{gt text='Semicolon'} (;)</option>
                    <option value="3">{gt text='Colon'} (:)</option>
                    <option value="4">{gt text='Tab'}</option>
                </select>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-success" title="{gt text='Export'}">{gt text='Export'}</button>
            <a class="btn btn-default" href="{route name='zikulasecuritycentermodule_admin_viewidslog'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
