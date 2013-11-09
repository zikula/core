{pageaddvar name='javascript' value='system/Zikula/Module/GroupsModule/Resources/public/js/groups.js'}
{adminheader}

<h3>
    <span class="icon-list"></span>
    {gt text="Groups list"}
</h3>

    
{checkpermissionblock component='ZikulaGroupsModule::' instance='::' level=ACCESS_ADD}
<br />
<a id="appendajax" {*onclick="groupappend();"*} title="{gt text="Create new group"}" href="{modurl modname='ZikulaGroupsModule' type='admin' func='newgroup'}"><span class="icon-plus-sign"></span> {gt text="Create new group"}</a>
<br /><br />
{/checkpermissionblock}

<input type="hidden" id="csrftoken" name="csrftoken" value="{insert name="csrftoken"}" />

<table id="grouplist" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text="Internal ID"}</th>
            <th>{gt text="Name"}</th>
            <th>{gt text="Type"}</th>
            <th>{gt text="Description"}</th>
            <th>{gt text="State"}</th>
            <th class="text-center">{gt text="Members"}</th>
            <th class="text-center">{gt text="Maximum membership"}</th>
            <th>{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach item='group' from=$groups}
        <tr id="group_{$group.gid}">
            <td id="groupgid_{$group.gid}" class="z-itemcell z-w05">
                <input type="hidden" id="gtypeid_{$group.gid}" value="{$group.gtype}" />
                <input type="hidden" id="stateid_{$group.gid}" value="{$group.state}" />
                <input type="hidden" id="modifystatus_{$group.gid}" value="0" />
                {$group.gid|safetext}
            </td>
            <td id="groupname_{$group.gid}">
                {$group.name|safetext}
                {if $group.gid eq $defaultgroup}
                *
                {elseif $group.gid eq $primaryadmingroup}
                **
                {/if}
            </td>
            <td id="groupgtype_{$group.gid}" class="zikulagroupsmodule-edit ">
                <span>
                    {gt text=$group.gtypelbl|safetext}
                </span>
            </td>
            <td id="groupdescription_{$group.gid}">
                {$group.description|safehtml}&nbsp;
            </td>
            <td id="groupstate_{$group.gid}">
                {gt text=$group.statelbl|safetext}
            </td>
            <td id="groupnbuser_{$group.gid}" class="text-center">
                {$group.nbuser|safetext}
            </td>
            <td id="groupnbumax_{$group.gid}" class="text-center">
                {$group.nbumax|safetext}
            </td>
            {assign var="options" value=$group.options}
            {gt text='Edit: %s' tag1=$group.name assign=strEditGroup}
            {gt text='Delete: %s' tag1=$group.name assign=strDeleteGroup}
            {gt text='Membership of: %s' tag1=$group.name assign=strMembershipGroup}
            {gt text='Save: %s' tag1=$group.name assign=strSaveGroup}
            {gt text='Delete: %s' tag1=$group.name assign=strDeleteGroup}
            {gt text='Cancel: %s' tag1=$group.name assign=strCancelGroup}
            <td id="groupaction_{$group.gid}" class="actions">
                <a id="modify_{$group.gid}" class="tooltips icon-pencil" href="{$group.editurl|safetext}" title="{$strEditGroup}"></a>
                <a href="{$group.membersurl|safetext}" class="icon-group tooltips" title="{gt text="Group membership"}"></a>
                {if $group.gid neq $defaultgroup && $group.gid neq $primaryadmingroup}
                <a href="{modurl modname='ZikulaGroupsModule' type='admin' func='delete' gid=$group.gid}" class="icon-trash con-fixed-width tooltips" data-gid="{$group.gid}" title="{$strDeleteGroup}" data-confirm="{gt text="Do you really want to delete this group?"}"></a>
                {else}
                <span class="icon-fixed-width"></span>
                {/if}
            </td>
        </tr>
        {foreachelse}

        {/foreach}
    </tbody>
</table>
<div class="italic">* {gt text="Default user group. Cannot be deleted."}</div>
<div class="italic">** {gt text="Primary administrators group. Cannot be deleted."}</div>

{if $useritems}
<h3>{gt text="Pending apptrcations"}</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text="User ID"}</th>
            <th>{gt text="User name"}</th>
            <th>{gt text="Group name to join"}</th>
            <th>{gt text="Comment"}</th>
            <th>{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach item=useritem from=$useritems}
        <tr>
            <td>{$useritem.userid}</td>
            <td>{$useritem.username|profilelinkbyuname}</td>
            <td>{$useritem.gname}</td>
            <td>{$useritem.apptrcation|safehtml}</td>
            <td>
                <a href="{modurl modname='ZikulaGroupsModule' type='admin' func='userpending' gid=$useritem.appgid userid=$useritem.userid action='accept'}" title="{gt text="Accept"} {$useritem.username}">{img src=add_user.png modname=core set=icons/extrasmall __alt="Accept" __title="Accept" class='tooltips'}</a>&nbsp;
                <a href="{modurl modname='ZikulaGroupsModule' type='admin' func='userpending' gid=$useritem.appgid userid=$useritem.userid action='deny'}" title="{gt text="Deny"} {$useritem.username}">{img src=delete_user.png modname=core set=icons/extrasmall __alt="Deny" __title="Accept" class='tooltips'}</a>
            </td>
        </tr>
        {foreachelse}
        <tr><td colspan="5">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{/if}

{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}
