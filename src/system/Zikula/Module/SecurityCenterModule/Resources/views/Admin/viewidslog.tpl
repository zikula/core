{ajaxheader ui=true}
{pageaddvarblock}
<script type="text/javascript">
    document.observe('dom:loaded', function() {
        $$('.eventfilter').each(function(element) {
            new Zikula.UI.Window(element);
        });
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="log" size="small"}
    <h3>{gt text="View IDS Log"}</h3>
</div>

<ul class="z-menulinks">
    <li><a href="{modurl modname=SecurityCenter type=admin func="exportidslog"}" title="{gt text="Download the entire log to a csv file"}" class="z-icon-es-export">{gt text="Export IDS Log"}</a></li>
    <li><a href="{modurl modname=SecurityCenter type=admin func="purgeidslog"}" title="{gt text="Delete the entire log"}" class="z-icon-es-delete">{gt text="Purge IDS Log"}</a></li>
</ul>

{gt text="All" assign=lblAll}
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
        <a href="{modurl modname="SecurityCenter" type="admin" func="viewidslog"}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Clear filter" __title="Clear filter"}</a>
        {/if}
    </fieldset>
</form>

<div>
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th><a class="{if $sort eq 'name'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="name"}">{gt text="Name"}</a></th>
            <th><a class="{if $sort eq 'tag'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="tag"}">{gt text="Tag"}</a></th>
            <th><a class="{if $sort eq 'value'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="value"}">{gt text="Value"}</a></th>
            <th><a class="{if $sort eq 'page'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="page"}">{gt text="Page"}</a></th>
            <th><a class="{if $sort eq 'username'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="username"}">{gt text="User Name"}</a></th>
            <th><a class="{if $sort eq 'ip'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="ip"}">{gt text="IP"}</a></th>
            <th><a class="{if $sort eq 'impact'}z-order-asc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="impact"}">{gt text="Impact"}</a></th>
            <th>{gt text="PHPIDS filters used"}</th>
            <th><a class="{if empty($sort) || $sort eq 'date DESC'}z-order-desc{else}z-order-unsorted{/if}" href="{modurl modname="SecurityCenter" type="admin" func="viewidslog" sort="date+DESC"}">{gt text="Date"}</a></th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$objectArray item=event}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$event.name|safetext}</td>
            <td>{$event.tag|safetext}</td>
            <td>{$event.value|htmlentities}</td>
            <td>{$event.page|safetext}</td>
            <td>{$event.username|safetext}</td>
            <td>{$event.ip|safetext}</td>
            <td>{$event.impact|safetext}</td>
            <td>
                {foreach from=$event.filters item=filter name=filterloop}
                <a id="f_{$event.id}_{$smarty.foreach.filterloop.iteration}" href="#f_{$event.id}_{$smarty.foreach.filterloop.iteration}_content" title="{gt text="PHPIDS filter %s" tag1=$filter.id}" class="eventfilter">{$filter.id}</a>{if $smarty.foreach.filterloop.iteration < $smarty.foreach.filterloop.total}, {/if}
                <div id="f_{$event.id}_{$smarty.foreach.filterloop.iteration}_content" style="display: none;">
                    <p><strong>{gt text="Impact"}:</strong> {$filter.impact}</p>
                    <p><strong>{gt text="Description"}:</strong> {$filter.description}</p>
                    <p><strong>{gt text="Rule"}:</strong> {$filter.rule}</p>
                </div>
                {/foreach}
            </td>
            <td>{$event.date|safetext}</td>
            <td class="z-right"><a href="{modurl modname="SecurityCenter" type="adminform" func="deleteidsentry" id=$event.id}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Delete" __title="Delete" class='tooltips'}</a></td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="10">{gt text="No logged intrusions found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}
