{adminheader}
{ajaxheader modname=Groups filename=groups.js ui=true}
{include file="groups_admin_header.tpl"}

<div class="z-admin-content-pagetitle">
    {icon type="group" size="small"}
    <h3>{gt text="Group membership"} ({$name|safetext})</h3>
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="User name"}</th>
            <th>{gt text="User ID"}</th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {section name=groupmembers loop=$groupmembers}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$groupmembers[groupmembers].uname|safetext} {if $groupmembers[groupmembers].name neq ''}({$groupmembers[groupmembers].name|safetext}){/if}</td>
            <td>{$groupmembers[groupmembers].uid|safetext}</td>
            <td class="z-right">
                {assign var="options" value=$groupmembers[groupmembers].options}
                {section name=options loop=$options}
                {if !empty($options[options])}
                <a href="{$options[options].url|safetext}" id="user-{$options[options].uid}" class="group-membership-removeuser" rel="{$gid}:{$options[options].uid}">{img src=$options[options].imgfile modname=core set=icons/extrasmall title=$options[options].title alt=$options[options].title}</a>
                {/if}
                {/section}
            </td>
        </tr>
        {sectionelse}
        <tr class="z-datatableempty"><td colspan="4">{gt text="No items found."}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}

<h3>{gt text="Add users to group"}</h3>
<div class="group-membership-alphanav">
    [{pagerabc posvar="letter" separator="&nbsp;|&nbsp;" names="*;A;B;C;D;E;F;G;H;I;J;K;L;M;N;O;P;Q;R;S;T;U;V;W;X;Y;Z;?" forwardvars="module,type,func,gid"}&nbsp;]
</div>
<br />

{if $uids}
<form class="z-form" action="{modurl modname="Groups" type="admin" func="adduser"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$gid|safetext}" />
        <fieldset>
            <div class="z-formrow">
                <label for="groups_uid">{gt text="Users to add"}</label>
                <select id="groups_uid" name="uid[]" multiple="multiple" size="10">
                    {html_options options=$uids}
                </select>
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Add" __title="Add" __text="Add"}
        </div>
    </div>
</form>
{else}
<p class="z-informationmsg">{gt text="Notice: Please select one or more users to add to the group. To select multiple users, use 'Shift-Click' or 'Control-Click'."}</p>
{/if}
{adminfooter}