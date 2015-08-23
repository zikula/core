{adminheader}
<h3>
    <span class="fa fa-archive"></span>
    {gt text='View IDS Log'}
</h3>

<ul class="navbar navbar-default navbar-modulelinks">
    <li><a href="{route name='zikulasecuritycentermodule_admin_exportidslog'}" title="{gt text='Download the entire log to a CSV file.'}" class="fa fa-arrow-circle-o-down"> {gt text='Export IDS Log'}</a></li>
    <li><a href="{route name='zikulasecuritycentermodule_admin_purgeidslog'}" title="{gt text='Delete the entire log.'}" class="fa fa-trash-o"> {gt text='Purge IDS Log'}</a></li>
</ul>

{if !empty($objectArray)}
    {gt text='All' assign='lblAll'}
    <form id="securitycenter_logfilter" class="form-horizontal" role="form" action="{route name='zikulasecuritycentermodule_admin_viewidslog'}" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <legend>{gt text='Filter'}</legend>
            <label>{gt text='User Name'}</label>
            {selector_object_array entity=1 name='filter[uid]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='uid' displayField='username' selectedValue=$filter.uid defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            <label>{gt text='Name'}</label>
            {selector_object_array entity=1 name='filter[name]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='name' displayField='name' selectedValue=$filter.name defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            <label>{gt text='Tag'}</label>
            {selector_object_array entity=1 name='filter[tag]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='tag' displayField='tag' selectedValue=$filter.tag defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            <label>{gt text='Value'}</label>
            {selector_object_array entity=1 name='filter[value]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='value' displayField='value' selectedValue=$filter.value defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            <label>{gt text='Page'}</label>
            {selector_object_array entity=1 name='filter[page]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='page' displayField='page' selectedValue=$filter.page defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            <label>{gt text='IP Address'}</label>
            {selector_object_array entity=1 name='filter[ip]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='ip' displayField='ip' selectedValue=$filter.ip defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            <label>{gt text='Impact'}</label>
            {selector_object_array entity=1 name='filter[impact]' modname='SecurityCenter' class='Zikula\SecurityCenterModule\Entity\IntrusionEntity' field='impact' displayField='impact' selectedValue=$filter.impact defaultValue='0' defaultText=$lblAll distinct='1' submit='1'}
            {if ($filter.uid || $filter.name || $filter.tag || $filter.value || $filter.page || $filter.ip || $filter.impact)}
            <a href="{route name='zikulasecuritycentermodule_admin_viewidslog'}">{img src='button_cancel.png' modname='core' set='icons/extrasmall' __alt='Clear filter' __title='Clear filter'}</a>
            {/if}
        </fieldset>
    </form>

    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken' assign='csrftoken'}{$csrftoken}" />
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><a class="{if $sort eq 'name'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='name'}">{gt text='Name'}</a></th>
                <th><a class="{if $sort eq 'tag'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='tag'}">{gt text='Tag'}</a></th>
                <th><a class="{if $sort eq 'value'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='value'}">{gt text='Value'}</a></th>
                <th><a class="{if $sort eq 'page'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='page'}">{gt text='Page'}</a></th>
                <th><a class="{if $sort eq 'username'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='username'}">{gt text='User Name'}</a></th>
                <th><a class="{if $sort eq 'ip'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='ip'}">{gt text='IP'}</a></th>
                <th><a class="{if $sort eq 'impact'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='impact'}">{gt text='Impact'}</a></th>
                <th>{gt text='PHPIDS filters used'}</th>
                <th><a class="{if empty($sort) || $sort eq 'date DESC'}z-order-desc{else}z-order-unsorted{/if}" href="{route name='zikulasecuritycentermodule_admin_viewidslog' sort='date+DESC'}">{gt text='Date'}</a></th>
                <th class="text-right">{gt text='Actions'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach item='event' from=$objectArray}
                <tr>
                    <td>{$event.name|safetext}</td>
                    <td>{$event.tag|safetext}</td>
                    <td>{$event.value|htmlentities}</td>
                    <td>{$event.page|safetext}</td>
                    <td>{$event.username|safetext}</td>
                    <td>{$event.ip|safetext}</td>
                    <td>{$event.impact|safetext}</td>
                    <td>
                        {foreach from=$event.filters item=filter name=filterloop}
                        <a id="f_{$event.id}_{$smarty.foreach.filterloop.iteration}" href="#f_{$event.id}_{$smarty.foreach.filterloop.iteration}_content" title="{gt text='PHPIDS filter %s' tag1=$filter.id}" class="eventfilter">{$filter.id}</a>{if $smarty.foreach.filterloop.iteration lt $smarty.foreach.filterloop.total}, {/if}
                        <div id="f_{$event.id}_{$smarty.foreach.filterloop.iteration}_content" style="display: none">
                            <p><strong>{gt text='Impact'}:</strong> {$filter.impact}</p>
                            <p><strong>{gt text='Description'}:</strong> {$filter.description}</p>
                            <p><strong>{gt text='Rule'}:</strong> {$filter.rule}</p>
                        </div>
                        {/foreach}
                    </td>
                    <td>{$event.date|dateformat|safetext}</td>
                    <td class="text-right"><a href="{route name='zikulasecuritycentermodule_admin_deleteidsentry' id=$event.id csrftoken=$csrftoken}">{img src='button_cancel.png' modname='core' set='icons/extrasmall' __alt='Delete' __title='Delete' class='tooltips'}</a></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{else}
    <p>{gt text='No logged intrusions found.'}</p>
{/if}
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulasecuritycentermodule_admin_viewidslog'}
{adminfooter}
