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
    <li><a href="{modurl modname=SecurityCenterModule type=admin func="exportidslog"}" title="{gt text="Download the entire log to a csv file"}" class="z-icon-es-export">{gt text="Export IDS Log"}</a></li>
    <li><a href="{modurl modname=SecurityCenterModule type=admin func="purgeidslog"}" title="{gt text="Delete the entire log"}" class="z-icon-es-delete">{gt text="Purge IDS Log"}</a></li>
</ul>

{gt text="All" assign=lblAll}
<form id="securitycenter_logfilter" class="z-form" action="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog"}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Filter"}</legend>
        <label>{gt text="User Name"}</label>
        {selector_entity_array name="filter[uid]" entity="SecurityCenterModule\Entity\Intrusion" field="user->uid" displayField="user->uname" selectedValue=$filter.uid defaultValue="0" defaultText="$lblAll" distinct="1" submit="1"}
        <label>{gt text="Name"}</label>
        {selector_entity_array name="filter[name]" entity="SecurityCenterModule\Entity\Intrusion" field="name" displayField="name" selectedValue=$filter.name defaultValue="" defaultText="$lblAll" distinct="1" submit="1"}
        <label>{gt text="Tag"}</label>
        {selector_entity_array name="filter[tag]" entity="SecurityCenterModule\Entity\Intrusion" field="tag" displayField="tag" selectedValue=$filter.tag defaultValue="" defaultText="$lblAll" distinct="1" submit="1"}
        <label>{gt text="Value"}</label>
        {selector_entity_array name="filter[value]" entity="SecurityCenterModule\Entity\Intrusion" field="value" displayField="value" selectedValue=$filter.value defaultValue="" defaultText="$lblAll" distinct="1" submit="1"}
        <label>{gt text="Page"}</label>
        {selector_entity_array name="filter[page]" entity="SecurityCenterModule\Entity\Intrusion" field="page" displayField="page" selectedValue=$filter.page defaultValue="" defaultText="$lblAll" distinct="1" submit="1"}
        <label>{gt text="IP Address"}</label>
        {selector_entity_array name="filter[ip]" entity="SecurityCenterModule\Entity\Intrusion" field="ip" displayField="ip" selectedValue=$filter.ip defaultValue="" defaultText="$lblAll" distinct="1" submit="1"}
        <label>{gt text="Impact"}</label>
        {selector_entity_array name="filter[impact]" entity="SecurityCenterModule\Entity\Intrusion" field="impact" displayField="impact" selectedValue=$filter.impact defaultValue="" defaultText="$lblAll" distinct="1" submit="1"}
        {if ($filter.uid || $filter.name || $filter.tag || $filter.value || $filter.page || $filter.ip || $filter.impact)}
        <a href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog"}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Clear filter" __title="Clear filter"}</a>
        {/if}
    </fieldset>
</form>

<div>
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>
                {if $sort eq 'name ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='name+DESC'}
                {elseif $sort eq 'name DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='name+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='name+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="Name"}</a>
            </th>
            <th>
                {if $sort eq 'tag ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='tag+DESC'}
                {elseif $sort eq 'tag DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='tag+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='tag+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="Tag"}</a>
            </th>
            <th>
                {if $sort eq 'value ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='value+DESC'}
                {elseif $sort eq 'value DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='value+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='value+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="Value"}</a>
            </th>
            <th>
                {if $sort eq 'page ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='page+DESC'}
                {elseif $sort eq 'page DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='page+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='page+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="Page"}</a>
            </th>
            <th>
                {if $sort eq 'username ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='username+DESC'}
                {elseif $sort eq 'username DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='username+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='username+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="User Name"}</a>
            </th>
            <th>
                {if $sort eq 'ip ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='ip+DESC'}
                {elseif $sort eq 'ip DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='ip+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='ip+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="IP"}</a>
            </th>
            <th>
                {if $sort eq 'impact ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='impact+DESC'}
                {elseif $sort eq 'impact DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='impact+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='impact+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="Impact"}</a>
            </th>
            <th>
                {gt text="PHPIDS filters used"}
            </th>
            <th>
                {if $sort eq 'date ASC'}
                    {assign var='order_class' value='z-order-asc'}
                    {assign var='order_sort' value='date+DESC'}
                {elseif empty($sort) || $sort eq 'date DESC'}
                    {assign var='order_class' value='z-order-desc'}
                    {assign var='order_sort' value='date+ASC'}
                {else}
                    {assign var='order_class' value='z-order-unsorted'}
                    {assign var='order_sort' value='date+ASC'}
                {/if}
                <a class="{$order_class}" href="{modurl modname="SecurityCenterModule" type="admin" func="viewidslog" sort=$order_sort}">{gt text="Date"}</a>
            </th>
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
            <td>{$event.date->format('d/m/Y H:i:s')}</td>
            <td class="z-right"><a href="{modurl modname="SecurityCenterModule" type="adminform" func="deleteidsentry" id=$event.id csrftoken=$csrftoken}">{img src=button_cancel.png modname=core set=icons/extrasmall __alt="Delete" __title="Delete" class='tooltips'}</a></td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="10">{gt text="No logged intrusions found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}
