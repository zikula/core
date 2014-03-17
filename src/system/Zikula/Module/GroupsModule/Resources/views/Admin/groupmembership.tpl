{adminheader}
{ajaxheader modname=Groups filename=groups.js ui=true}

<h3>
    <span class="fa fa-users"></span>
    {gt text="Group membership"} ({$group.name|safetext})
</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text="User ID"}</th>
            <th>{gt text="User name"}</th>
            <th>{gt text="Email"}</th>
            <th class="text-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {section name=groupmembers loop=$groupmembers}
        <tr>
            <td>{$groupmembers[groupmembers].uid|safetext}</td>
            <td>{$groupmembers[groupmembers].uname|safetext} {if $groupmembers[groupmembers].name neq ''}({$groupmembers[groupmembers].name|safetext}){/if}</td>
            <td>{$groupmembers[groupmembers].email|safetext}</td>
            <td class="text-right">
                {assign var="options" value=$groupmembers[groupmembers].options}
                {section name=options loop=$options}
                {if !empty($options[options])}
                <a href="{$options[options].url|safetext}" id="user-{$options[options].uid}" class="group-membership-removeuser" rel="{$group.gid}:{$options[options].uid}">{img src=$options[options].imgfile modname=core set=icons/extrasmall title=$options[options].title alt=$options[options].title}</a>
                {/if}
                {/section}
            </td>
        </tr>
        {sectionelse}
        <tr class="table table-borderedempty"><td colspan="4">{gt text="No items found."}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}

{if $uids}
<h3>{gt text="Add users to group"}</h3>
    {pagerabc posvar="letter" forwardvars="module,type,func,gid" printempty=true}
<br />

<p class="alert alert-info">{gt text="Notice: Please select one or more users to add to the group. To select multiple users, use 'Shift-Click' or 'Control-Click'."}</p>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="adduser"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$group.gid|safetext}" />
        <fieldset>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_uid">{gt text="Users to add"}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="groups_uid" name="uid[]" multiple="multiple" size="10" placeholder="{gt text='Select...'}">
                        {html_options options=$uids}
                    </select>
                </div>
            </div>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button class="btn btn-success" __alt="Add" __title="Add" __text="Add"}
            </div>
        </div>
    </div>
</form>
{/if}
{adminfooter}