{pageaddvarblock}
    <script type="text/javascript">
        // some defines
        var adminpermission = {{$adminid}};
        var lockadmin = {{$lockadmin}};

        {{if $enablefilter eq true}}
            var permgrp = '{{$permgrp}}';
        {{else}}
            var permgrp = -1;
        {{/if}}
        var updatingpermission = '{{gt text="Updating permission rule..."}}';
        var deletingpermission = '{{gt text="Deleting permission rule..."}}';
        var confirmdeleteperm  = '{{gt text="Do you really want to delete this permission rule?"}}';
        var testingpermission  = '{{gt text="Testing permission..."}}';
        var permissionlocked   = '{{gt text="This permission rule has been locked. If you need to unlock it, go to the Permission rules manager Settings page."}}';

        document.observe("dom:loaded", function() {
            permissioninit();
            Zikula.UI.Tooltips($$('.tooltips'));
        });

    </script>
{/pageaddvarblock}

{adminheader}
{include file="permissions_admin_header.tpl"}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Permission rules list"}</h3>
</div>

<p class="z-informationmsg z-hide" id="permissiondraganddrophint">
    {gt text="Notice: Arrange your permission rules in the desired order of evaluation, using drag and drop. The sort order will be saved immediately and automatically."}
    {if $lockadmin == 1}
    {gt text="The permission rule you have defined as your main administration permission rule (highlighted in the list below) has been <strong>locked</strong>, which means you cannot edit it, move it or delete it. If this permission rule is not at the top of the list, other permission rules can be moved around it. If you need to perform an operation on the main administration permission rule then you must go to the Permission rules manager Settings page to unlock it beforehand. Otherwise, it is safer to keep it locked."}
    {else}
    {gt text="The permission rule you have defined as your main administration permission rule (highlighted in the list below) is <strong>unlocked</strong>, which means you can currently edit it, move it or delete it. Once you have finished any operations you want to perform on it, you are recommended to go to the Permission rules manager Settings page and lock it again."}
    {/if}
</p>

{if $enablefilter eq true}
<form class="z-form" id="permgroupfilterform" action="{modurl modname=Permissions type=admin func=view}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Filter permission rules list"}</legend>
            <span class="z-nowrap">
                <label for="permgrp">{gt text="Choose filter"}</label>
                <select id="permgrp" name="permgrp">
                    <optgroup label="{gt text="Group"}">
                        {foreach item=groupname from=$permgrps key=groupid}
                        <option value="g+{$groupid}">{$groupname}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{gt text="Component"}">
                        {foreach item=component from=$components key=compkey}
                        <option value="c+{$compkey}">{$component}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </span>
            <span class="z-nowrap z-buttons">
                <button id="permgroupfiltersubmit" class="z-button z-bt-small" name="permgroupfiltersubmit" type="submit">{img modname=core src=filter.png set=icons/extrasmall  __alt="Filter" __title="Filter"} {gt text="Filter"}</button>
                <button id="permgroupfiltersubmitajax" class="z-button z-bt-small z-hide" onclick="javascript:permgroupfilter();">{img modname=core src=filter.png set=icons/extrasmall  __alt="Filter" __title="Filter"} {gt text="Filter"}</button>
            </span>
        </fieldset>
    </div>
</form>
{/if}

{checkpermissionblock component='Permissions::' instance='::' level=ACCESS_ADMIN}
<div id="permissions-header" class="z-clearfix">
    <a id="appendajax" onclick="javascript:permappend();" class="z-floatleft z-icon-es-new z-hide" title="{gt text="Create new permission rule"}" href="javascript:void(0);">{gt text="Create new permission rule"}</a>
    <strong id="filterwarning" class="z-floatright z-icon-es-warning" style="{if $permgrp eq -1}display: none;{/if}color: red; ">{gt text="Caution! Filter is active!"}</strong>
</div>
{/checkpermissionblock}

<div class="permbox">

    <ol id="permissionlist" class="z-itemlist">
        <li class="z-itemheader z-itemsortheader z-clearfix">
            <span class="z-itemcell z-w05">&nbsp;</span>
            <span class="z-itemcell z-w15">{gt text="Group"}<em id="filterwarninggroup" style="{if $filtertype neq 'group'}display: none;{/if} color: red; vertical-align: top;"> (filtered)</em></span>
            <span class="z-itemcell z-w25">{gt text="Component"}<em id="filterwarningcomponent" style="{if $filtertype neq 'component'}display: none;{/if} color: red; vertical-align: top;"> (filtered)</em></span>
            <span class="z-itemcell z-w25">{gt text="Instance"}</span>
            <span class="z-itemcell z-w20">{gt text="Permission level"}</span>
            <span class="z-itemcell z-w07">{gt text="Actions"}</span>
        </li>
        {foreach item=permission from=$permissions}
        <li id="permission_{$permission.permid}" class="{cycle values="z-odd,z-even"} z-sortable z-clearfix">
            <div id="permissioncontent_{$permission.permid}" class="permissioncontent">
                <input type="hidden" id="groupid_{$permission.permid}" value="{$permission.groupid}" class="permgroup" />
                <input type="hidden" id="comp_{$permission.permid}" value="{$permission.component|safetext}" class="permgroup" />
                <input type="hidden" id="levelid_{$permission.permid}" value="{$permission.accesslevelid}" class="permlevel" />
                <input type="hidden" id="sequence_{$permission.permid}" value="{$permission.sequence}" class="permsequence" />
                <input type="hidden" id="modifystatus_{$permission.permid}" value="0" />
                <span id="permdrag_{$permission.permid}" class="z-itemcell z-w05">
                    {strip}
                    {if $permission.arrows.up eq 1}
                    <a href="{$permission.up.url|safetext}">{img src="1uparrow.png" modname=core set=icons/extrasmall alt=$permission.up.title title=$permission.up.title}</a>
                    {/if}
                    {if $permission.arrows.down eq 1}
                    <a href="{$permission.down.url|safetext}">{img src="1downarrow.png" modname=core set=icons/extrasmall alt=$permission.down.title title=$permission.down.title}</a>
                    {/if}
                    {/strip}
                </span>
                <span id="permgroup_{$permission.permid}" class="z-itemcell z-w15">
                    {$permission.group|safetext}
                </span>
                <span id="editpermgroup_{$permission.permid}" class="z-itemcell z-w15 z-hide">
                    <select name="group_{$permission.permid}" id="group_{$permission.permid}">
                        {html_options options=$groups}
                    </select>
                </span>

                <span id="permcomp_{$permission.permid}" class="z-itemcell z-w25">{$permission.component|safetext}</span>
                <span id="editpermcomp_{$permission.permid}" class="z-itemcell z-w25 z-hide">
                    <textarea id="component_{$permission.permid}" name="component_{$permission.permid}" rows="2" cols="20">{$permission.component|safetext}</textarea>
                </span>

                <span id="perminst_{$permission.permid}" class="z-itemcell z-w25">{$permission.instance|safetext}</span>
                <span id="editperminst_{$permission.permid}" class="z-itemcell z-w25 z-hide">
                    <textarea id="instance_{$permission.permid}" name="instance_{$permission.permid}" rows="2" cols="20">{$permission.instance|safetext}</textarea>
                </span>

                <span id="permlevel_{$permission.permid}" class="z-itemcell z-w20">
                    {$permission.accesslevel|safetext}
                </span>
                <span id="editpermlevel_{$permission.permid}" class="z-itemcell z-w20 z-hide">
                    <select name="level_{$permission.permid}" id="level_{$permission.permid}">
                        {html_options options=$permissionlevels}
                    </select>
                </span>
                <span id="permaction_{$permission.permid}" class="z-itemcell z-w07">
                    <a id="insert_{$permission.permid}"     href="{$permission.inserturl|safetext}" title="{gt text="Insert permission rule before"}">{img src=insert_table_row.png modname=core set=icons/extrasmall __title="Insert permission rule before" __alt="Insert permission rule before"}</a>
                    <a id="modify_{$permission.permid}"     href="{$permission.editurl|safetext}" title="{gt text="Edit"}">{img src=xedit.png modname=core set=icons/extrasmall __title="Edit" __alt="Edit"}</a>
                    <a id="delete_{$permission.permid}"     href="{$permission.deleteurl|safetext}" title="{gt text="Delete"}">{img src=delete_table_row.png modname=core set=icons/extrasmall __title="Delete" __alt="Delete"}</a>
                    <button class="z-imagebutton z-hide tooltips" id="modifyajax_{$permission.permid}"   title="{gt text="Edit"}">{img src=xedit.png modname=core set=icons/extrasmall __title="Edit" __alt="Edit"}</button>
                    <button class="z-imagebutton z-hide tooltips" id="testpermajax_{$permission.permid}" title="{gt text="User permission check"}">{img src=testbed_protocol.png modname=core set=icons/extrasmall __title="Check a users permission" __alt="Check a users permission"}</button>
                </span>
                <span id="editpermaction_{$permission.permid}" class="z-itemcell z-w07 z-hide">
                    <button class="z-imagebutton tooltips" id="permeditsave_{$permission.permid}"   title="{gt text="Save"}">{img src=button_ok.png modname=core set=icons/extrasmall __alt="Save" __title="Save"}</button>
                    <button class="z-imagebutton tooltips" id="permeditdelete_{$permission.permid}" title="{gt text="Delete"}">{img src=14_layer_deletelayer.png modname=core set=icons/extrasmall __alt="Delete" __title="Delete"}</button>
                    <button class="z-imagebutton tooltips" id="permeditcancel_{$permission.permid}" title="{gt text="Cancel"}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Cancel" __title="Cancel"}</button>
                </span>
            </div>
            <div id="permissioninfo_{$permission.permid}" class="z-hide z-permissioninfo">&nbsp;</div>
        </li>
        {foreachelse}
        <li id="permission_1" class="z-hide z-clearfix">
            <div id="permissioncontent_1" class="permissioncontent">
                <input type="hidden" id="groupid_1" value="" class="permgroup" />
                <input type="hidden" id="levelid_1" value="" class="permlevel" />
                <input type="hidden" id="sequence_1" value="" class="permsequence" />
                <input type="hidden" id="modifystatus_1" value="0" />
                <span id="permdrag_1" class="z-itemcell z-w05">
                    &nbsp;
                </span>
                <span id="permgroup_1" class="z-itemcell z-w15 z-hide">{$permission.group|safetext}</span>
                <span id="editpermgroup_1" class="z-itemcell z-w15">
                    <select name="group_1" id="group_1">
                        {html_options options=$groups}
                    </select>
                </span>
                <span id="permcomp_1" class="z-itemcell z-w25 z-hide">{$permission.component|safetext}</span>
                <span id="editpermcomp_1" class="z-itemcell z-w25">
                    <textarea id="component_1" name="component_1"></textarea>
                </span>
                <span id="perminst_1" class="z-itemcell z-w25 z-hide">{$permission.instance|safetext}</span>
                <span id="editperminst_1" class="z-itemcell z-w25">
                    <textarea id="instance_1" name="instance_1"></textarea>
                </span>
                <span id="permlevel_1" class="z-itemcell z-w20 z-hide">{$permission.accesslevel|safetext}</span>
                <span id="editpermlevel_1" class="z-itemcell z-w20">
                    <select name="level_1" id="level_1">
                        {html_options options=$permissionlevels}
                    </select>
                </span>
                <span id="permaction_1" class="z-itemcell z-w07 z-hide">
                    <button class="z-imagebutton tooltips" id="modifyajax_1"   title="{gt text="Edit"}">{img src=xedit.png modname=core set=icons/extrasmall __title="Edit" __alt="Edit"}</button>
                    <button class="z-imagebutton tooltips" id="testpermajax_1" title="{gt text="User permission check"}">{img src=testbed_protocol.png modname=core set=icons/extrasmall __title="Check a users permission" __alt="Check a users permission"}</button>
                </span>
                <span id="editpermaction_1" class="z-itemcell z-w07">
                    <button class="z-imagebutton tooltips" id="permeditsave_1"   title="{gt text="Save"}">{img src=button_ok.png modname=core set=icons/extrasmall __alt="Save" __title="Save"}</button>
                    <button class="z-imagebutton tooltips" id="permeditdelete_1" title="{gt text="Delete"}">{img src=14_layer_deletelayer.png modname=core set=icons/extrasmall __alt="Delete" __title="Delete"}</button>
                    <button class="z-imagebutton tooltips" id="permeditcancel_1" title="{gt text="Cancel"}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Cancel" __title="Cancel"}</button>
                </span>
            </div>
            <div id="permissioninfo_1" class="z-hide z-permissioninfo">
                &nbsp;
            </div>
        </li>
        {/foreach}
    </ol>
</div>

<form id="testpermform" class="z-form" action="{modurl modname=permissions type=admin func=view}" method="post">
    <fieldset>
        <legend>{gt text="User permission check"}</legend>
        <div class="z-formrow">
            <label for="test_user">{gt text="User name"}</label>
            <input type="text" size="40" maxlength="50" name="test_user" id="test_user" value="{$testuser|safetext}" />
        </div>
        <div class="z-formrow">
            <label for="test_component">{gt text="Component to check"}</label>
            <input type="text" size="40" maxlength="50" name="test_component" id="test_component" value="{$testcomponent|safetext}" />
        </div>
        <div class="z-formrow">
            <label for="test_instance">{gt text="Instance to check"}</label>
            <input type="text" size="40" maxlength="50" name="test_instance" id="test_instance" value="{$testinstance|safetext}" />
        </div>
        <div class="z-formrow">
            <label for="test_level">{gt text="Permission level"}</label>
            <select name="test_level" id="test_level">
                {html_options options=$permissionlevels selected=$testlevel}
            </select>
        </div>
        <div class="z-formrow">
            <div class="z-formnote" id="permissiontestinfo">
                {if $testresult <> ''}
                {gt text="Permission check result:"} {$testresult}
                {else}
                &nbsp;
                {/if}
            </div>
        </div>
        <div class="z-buttons z-formbuttons">
            <button id="testpermsubmit" type="submit" title="{gt text="Check permission"}">{img modname=core src=button_ok.png set=icons/extrasmall  __alt="Check permission" __title="Check permission"} {gt text="Check permission"}</button>
            <button class="z-hide" id="testpermsubmitajax" onclick="javascript:performpermissiontest(); return false;" title="{gt text="Check permission"}">{img modname=core src=button_ok.png set=icons/extrasmall  __alt="Check permission" __title="Check permission"} {gt text="Check permission"}</button>
            <button id="testpermreset" type="reset" title="{gt text="Reset"}">{img modname=core src=button_cancel.png set=icons/extrasmall  __alt="Reset" __title="Reset"} {gt text="Reset"}</button>
        </div>
    </fieldset>
</form>
{adminfooter}
