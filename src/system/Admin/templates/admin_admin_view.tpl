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
        <tr class="{cycle values="z-odd,z-even"}">
            <td><a href="{modurl modname=Admin type=admin func=adminpanel acid=$categories[category].cid}">{$categories[category].catname|safetext}</a></td>
            <td>
                {assign var="options" value=$categories[category].options}
                {section name=option loop=$options}
                <a href="{$options[option].url|safetext}">{img modname=core set=icons/extrasmall src=$options[option].image title=$options[option].title alt=$options[option].title class='tooltips'}</a>
                {/section}
            </td>
        </tr>
        {sectionelse}
        <tr class="z-datatableempty"><td colspan="2">{gt text="No items found."}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}