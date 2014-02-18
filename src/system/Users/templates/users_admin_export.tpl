{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="export" size="small"}
    <h3>{gt text='Export users'}</h3>
</div>

<form class="z-form" action="{modurl modname='Users' type='admin' func='exporter'}" method="post" enctype="multipart/form-data">
    <div>
	<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmed" value="1" />
        <fieldset>
            <legend>Export Options</legend>
            <div class="z-formrow">
                <label for="users_export_titles">{gt text="Export Title Row"}</label>
                <input id="users_export_titles" type="checkbox" name="exportTitles" value="1" checked="checked" />
            </div>
            <div class="z-formrow">
                <label for="users_export_email">{gt text="Export Email Address"}</label>
                <input id="users_export_email" type="checkbox" name="exportEmail" value="1" checked="checked" />
            </div>
            <div class="z-formrow">
                <label for="users_export_regdate">{gt text="Export Registration Date"}</label>
                <input id="users_export_regdate" type="checkbox" name="exportRegDate" value="1" checked="checked" />
            </div>
            <div class="z-formrow">
                <label for="users_export_lastlogin">{gt text="Export Last Login Date"}</label>
                <input id="users_export_lastlogin" type="checkbox" name="exportLastLogin" value="1" checked="checked" />
            </div>
            {if isset($groups) && $groups == '1'}
            <div class="z-formrow">
                <label for="users_export_groups">{gt text="Export Group Membership"}</label>
                <input id="users_export_groups" type="checkbox" name="exportGroups" value="1"/>
            </div>
            {/if}
        </fieldset>
        <fieldset>
            <legend>{gt text="CSV Export File"}</legend>
            <div class="z-formrow">
                <label for="users_export">{gt text="CSV filename"}</label>
                <input id="users_export" type="text" name="exportFile" size="30" />
            </div>
            <div class="z-formrow">
                <label for="users_export_delimiter">{gt text="CSV delimiter"}</label>
                <select id="users_export_delimiter" name="delimiter">
                    <option value="1">{gt text="Comma"} (,)</option>
                    <option value="2">{gt text="Semicolon"} (;)</option>
                    <option value="3">{gt text="Colon"} (:)</option>
                    <option value="4">{gt text="Tab"}</option>
                </select>
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.png' set='icons/extrasmall' __alt='Export' __title='Export' __text='Export'}
            <a href="{modurl modname='Users' type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
