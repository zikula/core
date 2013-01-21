{adminheader}
<div class="z-admin-content-pagetitle">
    <h3>{$title|safetext}</h3>
</div>
<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Variable"}</th>
            <th>{gt text="Variable value"}</th>
        </tr>
    </thead>
    <tbody>
        {section name=arrayvariables loop=$arrayvariables}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$arrayvariables[arrayvariables].key|safetext}</td>
            <td>{$arrayvariables[arrayvariables].value|safetext}</td>
        </tr>
        {/section}
    </tbody>
</table>
{adminfooter}
