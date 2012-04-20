{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="export" size="small"}
    <h3>{gt text="Export IDS Log"}</h3>
</div>

<ul class="z-menulinks">
    <li><span class="z-icon-es-export">{gt text="Export IDS Log"}</span></li>
    <li><a href="{modurl modname=SecurityCenter type=admin func="purgeidslog"}" title="{gt text="Delete the entire log"}" class="z-icon-es-delete">{gt text="Purge IDS Log"}</a></li>
</ul>

<form class="z-form" action="{modurl modname='SecurityCenter' type='admin' func='exportidslog'}" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="confirmed" value="1" />
        <fieldset>
            <legend>{gt text="Export Options"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_export_titles">{gt text="Export Title Row"}</label>
                <input id="securitycenter_export_titles" type="checkbox" name="exportTitles" value="1" checked="checked" />
            </div>
            <div class="z-formrow">
                <label for="securitycenter_export_file">{gt text="CSV filename"}</label>
                <input id="securitycenter_export_file" type="text" name="exportFile" size="30" />
            </div>
            <div class="z-formrow">
                <label for="securitycenter_export_delimiter">{gt text="CSV delimiter"}</label>
                <select id="securitycenter_export_delimiter" name="delimiter">
                    <option value="1">{gt text="Comma"} (,)</option>
                    <option value="2">{gt text="Semicolon"} (;)</option>
                    <option value="3">{gt text="Colon"} (:)</option>
                    <option value="4">{gt text="Tab"}</option>
                </select>
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Export' __title='Export' __text='Export'}
            <a href="{modurl modname='SecurityCenter' type='admin' func='viewidslog'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}