{include file="securitycenter_admin_menu.htm"}
<div class="z-admincontainer">
    {gt text="Hacking attempts list" assign=templatetitle}
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <table class="z-admintable">
        <thead>
            <tr>
                <th>{gt text="Internal ID"}</th>
                <th>{gt text="File"}</th>
                <th>{gt text="Line"}</th>
                <th>{gt text="Type"}</th>
                <th>{gt text="Time"}</th>
                <th>{gt text="User ID"}</th>
                <th>{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$hackattempts item=hackattempt}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$hackattempt.hid|safetext}</td>
                <td>{$hackattempt.hackfile|safetext}</td>
                <td>{$hackattempt.hackline|safetext}</td>
                <td>{$hackattempt.hacktype|safetext}</td>
                <td>{$hackattempt.hacktime|safetext}</td>
                <td>{$hackattempt.userid|safetext}</td>
                <td>
                    {assign var="options" value=$hackattempt.options}
                    <form action="{modurl modname="SecurityCenter" type="admin" func="main"}" method="get">
                        <div>
                            <select name="viewhackattempt" onchange="location.href=this.options[this.selectedIndex].value">
                                <option value="{modurl modname="SecurityCenter" type="admin" func="main"}">{gt text="Additional information"}</option>
                                {foreach item=option from=$options}
                                <option value="{$option.url|safetext}">{$option.title|safetext}</option>
                                {/foreach}
                            </select>
                        </div>
                    </form>
                </td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="7">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar=startnum shift=1}
</div>
