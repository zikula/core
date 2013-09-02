{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="export" size="small"}
    <h3>{gt text="Export IDS Log"}</h3>
</div>

<ul class="navbar navbar-default">
    <li><span class="z-icon-es-export">{gt text="Export IDS Log"}</span></li>
    <li><a href="{modurl modname=SecurityCenter type=admin func="purgeidslog"}" title="{gt text="Delete the entire log"}" class="z-icon-es-delete">{gt text="Purge IDS Log"}</a></li>
</ul>

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaSecurityCenterModule' type='admin' func='exportidslog'}" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="confirmed" value="1" />
        <fieldset>
            <legend>{gt text="Export Options"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="securitycenter_export_titles">{gt text="Export Title Row"}</label>
                <div class="col-lg-9">
                <input id="securitycenter_export_titles" type="checkbox" name="exportTitles" value="1" checked="checked" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="securitycenter_export_file">{gt text="CSV filename"}</label>
                <div class="col-lg-9">
                <input id="securitycenter_export_file" type="text" class="form-control" name="exportFile" size="30" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="securitycenter_export_delimiter">{gt text="CSV delimiter"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="securitycenter_export_delimiter" name="delimiter">
                    <option value="1">{gt text="Comma"} (,)</option>
                    <option value="2">{gt text="Semicolon"} (;)</option>
                    <option value="3">{gt text="Colon"} (:)</option>
                    <option value="4">{gt text="Tab"}</option>
                </select>
            </div>
        </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Export' __title='Export' __text='Export'}
            <a href="{modurl modname='ZikulaSecurityCenterModule' type='admin' func='viewidslog'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}