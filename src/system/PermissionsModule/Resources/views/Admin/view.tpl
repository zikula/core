{pageaddvarblock}
    <script type="text/javascript">
        // some defines
        var adminpermission = {{$adminid}};
        var lockadmin = {{$lockadmin}};
    </script>
{/pageaddvarblock}

{pageaddvar name='javascript' value='web/bootstrap-jqueryui/bootstrap-jqueryui.min.js'}
{pageaddvar name='javascript' value='system/PermissionsModule/Resources/public/js/Zikula.Permission.Admin.View.js'}
{adminheader}

<h3>
    <span class="fa fa-list"></span>
    {gt text='Permission rules list'}
</h3>

{if $enablefilter eq true}
<form class="form-inline" role="form" action="{route name='zikulapermissionsmodule_admin_view'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <legend>{gt text="Filter permission rules list"}</legend>
        <span class="nowrap">
            <select id="filter-group" name="filter-group">
                {foreach key='groupid' item='groupname' from=$permgrps}
                <option value="{$groupid}"{if $groupid eq $filterGroup} selected="selected"{/if}>{$groupname}</option>
                {/foreach}
            </select>
            <select id="filter-component" name="filter-component">
                {foreach key='compkey' item='component' from=$components}
                <option value="{$compkey}"{if $compkey eq $filterComponent} selected="selected"{/if}>{$component}</option>
                {/foreach}
            </select>
        </span>
        <span class="nowrap">
            <button id="reset-filter" type="reset"><i class="fa fa-times"></i> {gt text='Reset'}</button>
        </span>
        <span class="nowrap no-script">
            <button name="permgroupfiltersubmit" type="submit"><i class="fa fa-filter"></i> {gt text='Filter'}</button>
        </span>
    </fieldset>
</form>
{/if}

<table id="permission-list" class="table table-striped">
    <thead>
        <tr>
            <th></th>
            <th>{gt text='ID'}</th>
            <th>
                {gt text='Group'}
                <em id="filter-warning-group" style="{if $filterGroup eq -1}display: none;{/if} color: red; vertical-align: top;"> (filtered)</em>
            </th>
            <th>
                {gt text='Component'}
                <em id="filter-warning-component" style="{if $filterComponent eq -1}display: none;{/if} color: red; vertical-align: top;"> (filtered)</em></th>
            <th>
                {gt text='Instance'}
            </th>
            <th>
                {gt text='Permission level'}
            </th>
            <th>
                {gt text='Actions'}
            </th>
        </tr>
    </thead>
    <tbody>
        {foreach item='permission' from=$permissions}
        <tr{if $lockadmin && $adminid eq $permission.permid} class="warning"{/if} data-id="{$permission.permid}">

            <td style="width: 1px; white-space: nowrap">
                {if $lockadmin && $adminid eq $permission.permid}
                <i class="fa fa-lock" title="{gt text='This permission rule has been locked. If you need to unlock it, go to the Permission rules manager Settings page.'}"></i>
                {else}

                {strip}
                {if $permission.arrows.up eq 1}
                    <a href="{$permission.up.url|safetext}" class="no-script fa fa-chevron-up" title="{$permission.up.title}"></a>
                {/if}
                {if $permission.arrows.down eq 1}
                    <a href="{$permission.down.url|safetext}" class="no-script fa fa-chevron-down" title="{$permission.down.title}"></a>
                {/if}
                {/strip}

                <i class="fa fa-arrows ajax hidden"></i>
                {/if}
            </td>
            <td class="text-right" style="width:1px;white-space:nowrap;">{$permission.permid}</td>
            <td id="permission-group-{$permission.permid}" data-id="{$permission.groupid}">{$permission.group|safetext}</td>
            <td id="permission-component-{$permission.permid}">{$permission.component|safetext}</td>
            <td id="permission-instance-{$permission.permid}">{$permission.instance|safetext}</td>
            <td id="permission-level-{$permission.permid}" data-id="{$permission.accesslevelid}">{$permission.accesslevel|safetext}</td>

            <td class="actions">
                <a class="pointer insertBefore create-new-permission tooltips" href="{$permission.inserturl|safetext}" title="{gt text='Insert permission rule before %s' tag1=$permission.permid}"><i class="fa fa-plus"></i></a>
                {if !$lockadmin || $adminid ne $permission.permid}
                <a class="pointer edit-permission tooltips" href="{$permission.editurl|safetext}" title="{gt text='Edit permission %s' tag1=$permission.permid}"><i class="fa fa-pencil"></i></a>
                <a class="delete-permission tooltips" href="{$permission.deleteurl|safetext}" title="{gt text='Delete permission %s' tag1=$permission.permid}"><i class="fa fa-trash-o"></i></a>
                {/if}
                <i class="test-permission pointer ajax hidden tooltips" title="{gt text='Check a users permission'}"><i class="fa fa-key"></i></i>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<form id="testpermform" class="form-horizontal" role="form" action="{route name='zikulapermissionsmodule_admin_view'}" method="post">
    <fieldset>
        <legend>{gt text='User permission check'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="test_user">{gt text='User name'}</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" size="40" maxlength="50" name="test_user" id="test_user" value="{$testuser|safetext}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="test_component">{gt text='Component to check'}</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" size="40" maxlength="50" name="test_component" id="test_component" value="{$testcomponent|safetext}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="test_instance">{gt text='Instance to check'}</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" size="40" maxlength="50" name="test_instance" id="test_instance" value="{$testinstance|safetext}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="test_level">{gt text='Permission level'}</label>
            <div class="col-sm-9">
                <select name="test_level" id="test_level" class="form-control" >
                    {html_options options=$permissionlevels selected=$testlevel}
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="help-block col-sm-offset-3 col-sm-9" id="permission-test-info" data-testing="{gt text='Testing permission...'}">
                {if $testresult ne ''}
                    {gt text='Permission check result:'} {$testresult}
                {else}
                    &nbsp;
                {/if}
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-default" id="test-permission" title="{gt text='Check permission'}">{gt text='Check permission'}</button>
                <button class="btn btn-danger" type="reset" title="{gt text='Reset'}">{gt text='Reset'}</button>
            </div>
        </div>
    </fieldset>
</form>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title" id="editModalLabel">{gt text='Permission'}</h4>
            </div>
            <div class="modal-body">
                <form role="form">
                    <div class="form-group">
                        <input id="permission-id" type="hidden" readonly="readonly" placeholder="id" />
                        <label for="permission-group">{gt text='Group'}</label>
                        <select name="permission-group" id="permission-group" class="form-control">
                            {html_options options=$groups}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="permission-component">{gt text='Component'}</label>
                        <textarea id="permission-component" class="no-editor form-control" rows="2" cols="20">{$permission.component|safetext}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="permission-instance">{gt text='Instance'}</label>
                        <textarea id="permission-instance" class="no-editor form-control" rows="2" cols="20">{$permission.instance|safetext}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="permission-level">{gt text='Level'}</label>
                        <select id="permission-level" class="form-control">
                            {html_options options=$permissionlevels}
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class='fa fa-times'></i> {gt text='Close'}</button>
                <button id="save-permission-changes" type="button" class="btn btn-success" data-dismiss="modal"><i class='fa fa-check'></i> {gt text='Save changes'}</button>
                <button id="save-new-permission" type="button" class="btn btn-success" data-dismiss="modal"><i class='fa fa-plus-square'></i> {gt text='Create new'}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="instanceInfoModal" tabindex="-1" role="dialog" aria-labelledby="instanceInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title" id="instanceInfoModalLabel">{gt text='Permission rule information'}</h4>
            </div>
            <div class="modal-body">
                {include file='Admin/instanceinfo.tpl'}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title" id="deleteModalLabel">{gt text='Do you really want to delete this permission rule?'}</h4>
            </div>
            <div class="modal-footer">
                <button id="confirm-delete-permission" type="button" class="btn btn-danger" data-dismiss="modal">{gt text='Yes'}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='No'}</button>
            </div>
        </div>
    </div>
</div>

{adminfooter}
