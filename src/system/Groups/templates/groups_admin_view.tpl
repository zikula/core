{ajaxheader modname=Groups filename=groups.js ui=true}
{pageaddvarblock}
<script type="text/javascript">
    // some defines
    var updatinggroup = '...{{gt text="Updating group"}}...';
    var deletinggroup = '...{{gt text="Deleting group"}}...';
    var confirmDeleteGroup = '{{gt text="Do you really want to delete this group?"}}';

    document.observe("dom:loaded", function() {
        groupinit({{$defaultgroup}},{{$groups[0].gid}},{{$primaryadmingroup}});
        Zikula.UI.Tooltips($$('.tooltips'));

        {{foreach item='group' from=$groups}}
        //$('insert_{{$group.gid}}').addClassName('z-hide');
        $('modify_{{$group.gid}}').addClassName('z-hide');
        $('delete_{{$group.gid}}').addClassName('z-hide');
        $('modifyajax_{{$group.gid}}').removeClassName('z-hide');
        $('modifyajax_{{$group.gid}}').observe('click', function() {
            groupmodifyinit({{$group.gid}});
        });
        {{/foreach}}
    });
</script>
{/pageaddvarblock}

{adminheader}
{include file="groups_admin_header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Groups list"}</h3>
</div>

{checkpermissionblock component='Groups::' instance='::' level=ACCESS_ADD}
<a id="appendajax" onclick="groupappend();" style="margin-bottom: 1em;" class="z-floatleft z-icon-es-new z-hide" title="{gt text="Create new group"}" href="javascript:void(0);">{gt text="Create new group"}</a>
{/checkpermissionblock}

<input type="hidden" id="csrftoken" name="csrftoken" value="{insert name="csrftoken"}" />

<div class="groupbox z-clearer">
    <ol id="grouplist" class="z-itemlist">
        <li class="z-itemheader z-clearfix">
            <span class="z-itemcell z-w05">{gt text="Internal ID"}</span>
            <span class="z-itemcell z-w15">{gt text="Name"}</span>
            <span class="z-itemcell z-w10">{gt text="Type"}</span>
            <span class="z-itemcell z-w30">{gt text="Description"}</span>
            <span class="z-itemcell z-w10">{gt text="State"}</span>
            <span class="z-itemcell z-w10 z-center">{gt text="Members"}</span>
            <span class="z-itemcell z-w10 z-center">{gt text="Maximum membership"}</span>
            <span class="z-itemcell z-w10">{gt text="Actions"}</span>
        </li>
        {foreach item='group' from=$groups}
        <li id="group_{$group.gid}" class="{cycle values='z-odd,z-even'} z-clearfix">
            <div id="groupcontent_{$group.gid}">
                <input type="hidden" id="gtypeid_{$group.gid}" value="{$group.gtype}" />
                <input type="hidden" id="stateid_{$group.gid}" value="{$group.state}" />
                <input type="hidden" id="modifystatus_{$group.gid}" value="0" />
                <span id="groupgid_{$group.gid}" class="z-itemcell z-w05">
                    {$group.gid|safetext}
                </span>
                <span id="groupname_{$group.gid}" class="z-itemcell z-w15">
                    {$group.name|safetext}
                    {if $group.gid eq $defaultgroup}
                    *
                    {elseif $group.gid eq $primaryadmingroup}
                    **
                    {/if}
                </span>
                {* Hidden until called *}
                <span id="editgroupname_{$group.gid}" class="z-itemcell z-w15 z-hide">
                    <input type="text" id="name_{$group.gid}" name="name_{$group.gid}" value="{$group.name|safetext}" size="15" />
                </span>
                {* *}
                <span id="groupgtype_{$group.gid}" class="z-itemcell z-w10">
                    {gt text="$group.gtypelbl|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupgtype_{$group.gid}" class="z-itemcell z-w10 z-hide">
                    <select id="gtype_{$group.gid}" name="gtype_{$group.gid}">
                        {html_options options=$grouptypes selected=$group.gtype}
                    </select>
                </span>
                {* *}
                <span id="groupdescription_{$group.gid}" class="z-itemcell z-w30">
                    {$group.description|safehtml}&nbsp;
                </span>
                {* Hidden until called *}
                <span id="editgroupdescription_{$group.gid}" class="z-itemcell z-w30 z-hide">
                    <textarea id="description_{$group.gid}" rows="2" cols="20" name="description_{$group.gid}">{$group.description|safehtml}</textarea>
                </span>
                {* *}
                <span id="groupstate_{$group.gid}" class="z-itemcell z-w10">
                    {gt text="$group.statelbl|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupstate_{$group.gid}" class="z-itemcell z-w10 z-hide">
                    <select id="state_{$group.gid}" name="state_{$group.gid}">
                        {html_options options=$states selected=$group.state}
                    </select>
                </span>
                {* *}
                <span id="groupnbuser_{$group.gid}" class="z-itemcell z-w10 z-center">
                    {$group.nbuser|safetext}
                </span>
                <span id="groupnbumax_{$group.gid}" class="z-itemcell z-w10 z-center">
                    {$group.nbumax|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupnbumax_{$group.gid}" class="z-itemcell z-w10 z-hide z-center">
                    <input type="text" id="nbumax_{$group.gid}" size="5" name="nbumax_{$group.gid}" value="{$group.nbumax|safetext}" />
                </span>
                {* *}
                {assign var="options" value=$group.options}
                {gt text='Edit: %s' tag1=$group.name assign=strEditGroup}
                {gt text='Delete: %s' tag1=$group.name assign=strDeleteGroup}
                {gt text='Membership of: %s' tag1=$group.name assign=strMembershipGroup}
                {gt text='Save: %s' tag1=$group.name assign=strSaveGroup}
                {gt text='Delete: %s' tag1=$group.name assign=strDeleteGroup}
                {gt text='Cancel: %s' tag1=$group.name assign=strCancelGroup}
                <span id="groupaction_{$group.gid}" class="z-itemcell z-w10">
                    <button class="z-imagebutton z-hide tooltips" id="modifyajax_{$group.gid}" title="{$strEditGroup}">{img src=xedit.png modname=core set=icons/extrasmall __title="Edit" __alt="Edit"}</button>
                    <a id="modify_{$group.gid}" href="{$group.editurl|safetext}" title="{gt text="Edit"}">{img src=xedit.png modname=core set=icons/extrasmall title=$strEditGroup alt="Edit" class='tooltips'}</a>
                    <a id="delete_{$group.gid}" href="{$group.deleteurl|safetext}" title="{gt text="Delete"}">{img src=14_layer_deletelayer.png modname=core set=icons/extrasmall title=$strDeleteGroup __alt="Delete" class='tooltips'}</a>
                    <a id="members_{$group.gid}" href="{$group.membersurl|safetext}" title="{gt text="Group membership"}">{img src=group.png modname=core set=icons/extrasmall title=$strMembershipGroup __alt="Group membership" class='tooltips'}</a>
                </span>
                <span id="editgroupaction_{$group.gid}" class="z-itemcell z-w10 z-hide">
                    <button class="z-imagebutton tooltips" id="groupeditsave_{$group.gid}" title="{$strSaveGroup}">{img src=button_ok.png modname=core set=icons/extrasmall __alt="Save" __title="Save"}</button>
                    <button class="z-imagebutton tooltips" id="groupeditdelete_{$group.gid}" title="{$strDeleteGroup}">{img src=14_layer_deletelayer.png modname=core set=icons/extrasmall __alt="Delete" __title="Delete"}</button>
                    <button class="z-imagebutton tooltips" id="groupeditcancel_{$group.gid}" title="{$strCancelGroup}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Cancel" __title="Cancel"}</button>
                </span>
            </div>
            <div id="groupinfo_{$group.gid}" class="z-hide z-groupinfo">
                &nbsp;
            </div>
        </li>
        {foreachelse}
        <li id="group_1" class="z-hide z-clearfix">
            <div id="groupcontent_1" class="groupcontent">
                <input type="hidden" id="gtypeid_1" value="" />
                <input type="hidden" id="stateid_1" value="" />
                <input type="hidden" id="groupgid_1" value="{$group.gid}" />
                <input type="hidden" id="modifystatus_{$group.gid}" value="0" />
                <span id="groupgid_1" class="z-itemcell z-w05">
                    {$group.gid|safetext}
                </span>
                <span id="groupname_1" class="z-itemcell z-w15 z-hide">
                    {$group.name|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupname_1" class="z-itemcell z-w15">
                    <input type="text" id="name_1" name="name_1" value="" size="15" />&nbsp;
                </span>
                {* *}
                <span id="groupgtype_1" class="z-itemcell z-w10 z-hide">
                    {gt text="$group.gtypelbl|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupgtype_1" class="z-itemcell z-w15">
                    <select id="gtype_1" name="gtype_1">
                        {html_options options=$grouptypes selected=$group.gtype}
                    </select>
                </span>
                {* *}
                <span id="groupdescription_1" class="z-itemcell z-w30 z-hide">
                    {$group.description}&nbsp;
                </span>
                {* Hidden until called *}
                <span id="editgroupdescription_1" class="z-itemcell z-w30">
                    <textarea id="description_1" rows="2" cols="20" name="description_1">{$group.description|safetext}</textarea>&nbsp;
                </span>
                {* *}
                <span id="groupstate_1" class="z-itemcell z-w10 z-hide">
                    {gt text="$group.statelbl|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupstate_1" class="z-itemcell z-w10">
                    <select id="state_1" name="state_1">
                        {html_options options=$states selected=$group.state}
                    </select>
                </span>
                {* *}
                <span id="groupnbuser_1" class="z-itemcell z-w10 z-hide z-center">
                    {*$group.nbuser|safetext*}&nbsp;
                </span>
                {* *}
                <span id="groupnbumax_1" class="z-itemcell z-w10 z-hide z-center">
                    {$group.nbumax|safetext}
                </span>
                {* Hidden until called *}
                <span id="editgroupnbumax_1" class="z-itemcell z-w10 z-center">
                    <input type="text" id="nbumax_1" size="5" name="nbumax_1" value="{$group.nbumax|safetext}" />
                </span>
                {* *}
                <span id="groupaction_1" class="z-itemcell z-w12 z-hide">
                    <button class="z-imagebutton" id="modifyajax_1" title="{gt text="Edit"}">{img src=xedit.png modname=core set=icons/extrasmall __title="Edit" __alt="Edit"}</button>
                </span>
                <span id="editgroupaction_1" class="z-itemcell z-w12">
                    <button class="z-imagebutton" id="groupeditsave_1"   title="{gt text="Save"}">{img src=button_ok.png modname=core set=icons/extrasmall __alt="Save" __title="Save"}</button>
                    <button class="z-imagebutton" id="groupeditdelete_1" title="{gt text="Delete"}">{img src=14_layer_deletelayer.png modname=core set=icons/extrasmall __alt="Delete" __title="Delete:"}</button>
                    <button class="z-imagebutton" id="groupeditcancel_1" title="{gt text="Cancel"}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Cancel" __title="Cancel"}</button>
                </span>
            </div>
            <div id="groupinfo_1" class="z-hide z-groupinfo">&nbsp;</div>
        </li>
        {/foreach}
    </ol>
</div>
<div class="z-italic">* {gt text="Default user group. Cannot be deleted."}</div>
<div class="z-italic">** {gt text="Primary administrators group. Cannot be deleted."}</div>

{if $useritems}
<h3>{gt text="Pending applications"}</h3>
<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="User ID"}</th>
            <th>{gt text="User name"}</th>
            <th>{gt text="Name"}</th>
            <th>{gt text="Comment"}</th>
            <th>{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach item=useritem from=$useritems}
        <tr class="{cycle values='z-odd,z-even' name='pending'}">
            <td>{$useritem.userid}</td>
            <td><strong>{$useritem.username|profilelinkbyuname}</strong></td>
            <td>{$useritem.gname}</td>
            <td>{$useritem.application|safehtml}</td>
            <td>
                <a href="{modurl modname='Groups' type='admin' func='userpending' gid=$useritem.appgid userid=$useritem.userid action='accept'}" title="{gt text="Accept"} {$useritem.username}">{img src=add_user.png modname=core set=icons/extrasmall __alt="Accept" __title="Accept" class='tooltips'}</a>&nbsp;
                <a href="{modurl modname='Groups' type='admin' func='userpending' gid=$useritem.appgid userid=$useritem.userid action='deny'}" title="{gt text="Deny"} {$useritem.username}">{img src=delete_user.png modname=core set=icons/extrasmall __alt="Deny" __title="Accept" class='tooltips'}</a>
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="5">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{/if}

{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}
