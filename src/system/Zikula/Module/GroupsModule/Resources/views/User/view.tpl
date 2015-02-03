{if $nogroups}
    <div class="alert alert-warning">{gt text='There are currently no groups that can be joined.'}</div>
{else}
    {gt text='Groups manager' assign='templatetitle'}
    {include file='User/menu.tpl'}
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>{gt text='Name'}</th>
                <th>{gt text='Description'}</th>
                <th>{gt text='Type'}</th>
                <th>{gt text='State'}</th>
                <th>{gt text='Members'}</th>
                <th>{gt text='Maximum membership'}</th>
                {if $state gt 0}
                <th>{gt text='Functions'}</th>
                {/if}
            </tr>
        </thead>
        {if $items}
        <tbody>
            {foreach item='item' from=$items}
            <tr>
                {$item}
            </tr>
            {/foreach}
        </tbody>
        {/if}
    </table>
    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulagroupsmodule_user_view'}
{/if}
