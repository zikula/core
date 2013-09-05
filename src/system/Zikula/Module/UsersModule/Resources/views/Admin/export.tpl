{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="export" size="small"}
    <h3>{gt text='Export users'}</h3>
</div>

<form class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='admin' func='exporter'}" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="confirmed" value="1" />
        <fieldset>
            <legend>Export Options</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export_titles">{gt text="Export Title Row"}</label>
                <div class="col-lg-9">
                    <input id="users_export_titles" type="checkbox" name="exportTitles" value="1" checked="checked" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export_email">{gt text="Export Email Address"}</label>
                <div class="col-lg-9">
                    <input id="users_export_email" type="checkbox" name="exportEmail" value="1" checked="checked" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export_regdate">{gt text="Export Registration Date"}</label>
                <div class="col-lg-9">
                    <input id="users_export_regdate" type="checkbox" name="exportRegDate" value="1" checked="checked" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export_lastlogin">{gt text="Export Last Login Date"}</label>
                <div class="col-lg-9">
                    <input id="users_export_lastlogin" type="checkbox" name="exportLastLogin" value="1" checked="checked" />
                </div>
            </div>
            {if isset($groups) && $groups == '1'}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export_groups">{gt text="Export Group Membership"}</label>
                <div class="col-lg-9">
                    <input id="users_export_groups" type="checkbox" name="exportGroups" value="1"/>
                </div>
            </div>
            {/if}
        </fieldset>
        <fieldset>
            <legend>{gt text="CSV Export File"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export">{gt text="CSV filename"}</label>
                <div class="col-lg-9">
                    <input id="users_export" type="text" class="form-control" name="exportFile" size="30" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="users_export_delimiter">{gt text="CSV delimiter"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="users_export_delimiter" name="delimiter">
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
            <a href="{modurl modname='ZikulaUsersModule' type='admin' func='view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}