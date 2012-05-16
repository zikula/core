{if $nogroups}
<div class="z-warningmsg">{gt text="There are currently no groups that can be joined."}</div>
{else}
{gt text="Groups manager" assign=templatetitle}
{include file="User/menu.tpl"}
<table class="z-datatable">
    <thead>
        <tr>
            <th> {gt text="Name"} </th>
            <th> {gt text="Description"} </th>
            <th> {gt text="Type"} </th>
            <th> {gt text="State"} </th>
            <th> {gt text="Members"} </th>
            <th> {gt text="Maximum membership"} </th>
            {if $state gt 0}
            <th> {gt text="Functions"} </th>
            {/if}
        </tr>
    </thead>
    {if $items}
    <tbody>
        {foreach item=item from=$items}
        <tr class="{cycle values="z-odd,z-even"}">
            {$item}
        </tr>
        {/foreach}
    </tbody>
    {/if}
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{/if}