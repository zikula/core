{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text="Module categories list"}
</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {section name=category loop=$categories}
        {assign var='category_id' value=$categories[category].cid}
        {assign var='category_name' value=$categories[category].name|safetext}
        {checkpermission component="`$module`::" instance="`$category_name`:`$category_id`" level="ACCESS_EDIT" assign="access_edit"}
        {checkpermission component="`$module`::" instance="`$category_name`:`$category_id`" level="ACCESS_DELETE" assign="access_delete"}
        <tr>
            <td><a href="{modurl modname=ZikulaAdminModule type=admin func=adminpanel acid=$category_id}">{$category_name}</a></td>
            <td class="actions">
                {if $access_edit}
                    <a href="{modurl modname=$module type='admin' func='modify' cid=$category_id}" title="{gt text="Edit"}" class="tooltips fa fa-wrench"></a>
                {/if}
                {if $access_delete}
                <a href="{modurl modname=$module type='admin' func='delete' cid=$category_id}" title="{gt text="Delete"}" class="tooltips fa fa-trash"></a>
                {/if}
            </td>
        </tr>
        {sectionelse}
        <tr class="table table-borderedempty"><td colspan="2">{gt text="No items found."}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}