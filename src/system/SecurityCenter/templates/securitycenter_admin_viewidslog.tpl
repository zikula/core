{include file="securitycenter_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="View IDS Log"}</div>
    {gt text="All" assign=lblAll}
    <h2>{gt text="View IDS Log"}</h2>
    <form id="securitycenter_logfilter" class="z-form" action="{modurl modname="SecurityCenter" type="admin" func="viewidslog"}" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <legend>{gt text="Filter"}</legend>
            <label>{gt text="User Name"}</label>
            {selector_object_array name="filter[uid]" modname="SecurityCenter" class="intrusion" field="uid" displayField="username" selectedValue=$filter.uid defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            <label>{gt text="Name"}</label>
            {selector_object_array name="filter[name]" modname="SecurityCenter" class="intrusion" field="name" displayField="name" selectedValue=$filter.name defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            <label>{gt text="Tag"}</label>
            {selector_object_array name="filter[tag]" modname="SecurityCenter" class="intrusion" field="tag" displayField="tag" selectedValue=$filter.tag defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            <label>{gt text="Value"}</label>
            {selector_object_array name="filter[value]" modname="SecurityCenter" class="intrusion" field="value" displayField="value" selectedValue=$filter.value defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            <label>{gt text="Page"}</label>
            {selector_object_array name="filter[page]" modname="SecurityCenter" class="intrusion" field="page" displayField="page" selectedValue=$filter.page defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            <label>{gt text="IP Address"}</label>
            {selector_object_array name="filter[ip]" modname="SecurityCenter" class="intrusion" field="ip" displayField="ip" selectedValue=$filter.ip defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            <label>{gt text="Impact"}</label>
            {selector_object_array name="filter[impact]" modname="SecurityCenter" class="intrusion" field="impact" displayField="impact" selectedValue=$filter.impact defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
            {if ($filter.uid || $filter.name || $filter.tag || $filter.value || $filter.page || $filter.ip || $filter.impact)}
            <a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog"}">{img src=cancel.gif modname=core set=icons/extrasmall __alt="Clear filter" __title="Clear filter"}</a>
            {/if}
        </fieldset>
    </form>

    <table class="z-admintable">
        <thead>
            <tr>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="name"}">{gt text="Name"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="tag"}">{gt text="Tag"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="value"}">{gt text="Value"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="page"}">{gt text="Page"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="username"}">{gt text="User Name"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="ip"}">{gt text="IP"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="impact"}">{gt text="Impact"}</a></th>
                <th><a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="date DESC"}">{gt text="Date"}</a></th>
                <th class="z-right">{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody>
            {secgenauthkey module="SecurityCenter" assign="authkey"}
            {foreach from=$objectArray item=event}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$event.name|safetext}</td>
                <td>{$event.tag|safetext}</td>
                <td>{$event.value|safetext}</td>
                <td>{$event.page|safetext}</td>
                <td>{$event.username|safetext}</td>
                <td>{$event.ip|safetext}</td>
                <td>{$event.impact|safetext}</td>
                <td>{$event.date|safetext}</td>
                <td class="z-right"><a href="{modurl modname="SecurityCenter" type="adminform" func="delete" id=$event.id authid=$authkey}">{img src=cancel.gif modname=core set=icons/extrasmall __alt="Delete" __title="Delete"}</a></td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="9">{gt text="No logged intrusions found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
</div>
