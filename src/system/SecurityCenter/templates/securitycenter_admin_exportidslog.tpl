{include file="securitycenter_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="Export IDS Log"}</div>
    
    <h2>{gt text="Export IDS Log"}</h2>

    TO-DO
    
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
            </tr>
        </thead>
        <tbody>
            {secgenauthkey module="SecurityCenter" assign="authkey"}
            {foreach from=$objectArray item=event}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$event.name|safetext}</td>
                <td>{$event.tag|safetext}</td>
                <td>{$event.value|htmlentities}</td>
                <td>{$event.page|safetext}</td>
                <td>{$event.username|safetext}</td>
                <td>{$event.ip|safetext}</td>
                <td>{$event.impact|safetext}</td>
                <td>{$event.date|safetext}</td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="9">{gt text="No logged intrusions found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    
</div>
