{gt text='Memberships' assign='templatetitle'}
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
            {if $group.state gt 0}
            <th>{gt text='Functions'}</th>
            {/if}
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{$group.name}</td>
            <td>{$group.description|safehtml}</td>
            <td>{$group.typelbl}</td>
            <td>{$group.statelbl}</td>
            <td>{$group.nbuser}</td>
            <td>
                {if $group.nbumax eq false or $group.nbumax eq 0}
                    {gt text='Unlimited'}
                {else}
                    {$group.nbumax}
                {/if}
            </td>
            {if $coredata.logged_in eq false}
                {route name='zikulagroupsmodule_user_memberslist' gid=$group.gid assign='return_page'}
                <td><a href="{route name='zikulausersmodule_access_login' returnUrl=$return_page|urlencode}" title="{gt text='Sorry! You must register for a user account on this site before you can apply for membership of a group.'}"> {gt text='Log in or register'}</a></td>
            {elseif $group.state gt 0}
                {if $group.status eq true}
                    <td>[ <strong>{gt text='Pending'}</strong> | <a href="{route name='zikulagroupsmodule_user_membership' action='cancel' gid=$group.gid}" title="{gt text='Cancel'}">{gt text='Cancel'}</a> ]</td>
                {else}
                    {if $group.nbumax eq 0 || $group.nbumax gt $group.nbuser}
                        {if $ismember eq true}
                            {assign var='funcaction' value='unsubscribe'}
                            {gt text='Resign' assign='mbfunctitle'}
                        {else}
                            {assign var='funcaction' value='subscribe'}
                            {gt text='Apply' assign='mbfunctitle'}
                        {/if}
                        <td><a href="{route name='zikulagroupsmodule_user_membership' action=$funcaction gid=$group.gid}" title="{$mbfunctitle}">{$mbfunctitle}</a></td>
                    {else}
                        <td>{gt text='Group has reached its maximum capacity'}</td>
                    {/if}
                {/if}
            {/if}
        </tr>
    </tbody>
</table>

<br />

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text='User name'}</th>
            <th>{gt text='Status'}</th>
        </tr>
    </thead>
    <tbody>
        {section name='members' loop=$members}
            <tr>
                <td>{$members[members].uname|profilelinkbyuname}</td>
                <td class="actions">
                    {if $members[members].isonline}
                    <span class="label label-success">{gt text='on-line'}</span>
                    {else}
                    <span class="label label-danger">{gt text='off-line'}</span>
                    {/if}
                </td>
            </tr>
        {sectionelse}
            <tr class="table table-borderedempty">
                <td colspan="2">{gt text='No group members found.'}</td>
            </tr>
        {/section}
    </tbody>
</table>

{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulagroupsmodule_user_memberlist'}
