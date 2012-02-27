{ajaxheader ui=true}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Module categories list"}</h3>
</div>

<table class="z-datatable">
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
        <tr class="{cycle values="z-odd,z-even"}">
            <td><a href="{modurl modname=Admin type=admin func=adminpanel acid=$category_id}">{$category_name}</a></td>
            <td>
                {if $access_edit}
                <a href="{modurl modname=$module type='admin' func='modify' cid=$category_id}">{img modname='core' set='icons/extrasmall' src='xedit.png' __title='Edit' __alt='Edit' class='tooltips'}</a>
                {/if}
                {if $access_delete}
                <a href="{modurl modname=$module type='admin' func='delete' cid=$category_id}">{img modname='core' set='icons/extrasmall' src='14_layer_deletelayer.png' __title='Delete' __alt='Delete' class='tooltips'}</a>
                {/if}
            </td>
        </tr>
        {sectionelse}
        <tr class="z-datatableempty"><td colspan="2">{gt text="No items found."}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}