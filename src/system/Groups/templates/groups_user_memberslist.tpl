{gt text="Memberships" assign=templatetitle}
{include file="groups_user_menu.tpl"}

<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Type"}</th>
            <th>{gt text="Description"}</th>
            <th>{gt text="State"}</th>
            <th>{gt text="Members"}</th>
            <th>{gt text="Maximum membership"}</th>
            <th>{gt text="Functions"}</th>
        </tr>
    </thead>
    <tbody>
        <tr class="z-odd">
            <td><strong>{$group.name}</strong></td>
            <td>{$group.typelbl}</td>
            <td style="text-align:left;">{$group.description|safehtml}</td>
            <td>{$group.statelbl}</td>
            <td>{$group.nbuser}</td>
            <td>{$group.nbumax}</td>
            {if $group.state eq 0}
            <td>[ {gt text="Unavailable"} ]</td>
            {elseif $coredata.logged_in eq false}
            {modurl modname='Groups' type='user' func='memberslist' gid=$group.gid assign='return_page'}
            <td>[ <a href="{modurl modname='Users' type='user' func='login' returnpage=$return_page|urlencode}" title="{gt text='Sorry! You must register for a user account on this site before you can apply for membership of a group.'}"> {gt text="Log in or register"}</a> ]</td>
            {else}
            {if $group.status eq true}
            <td>[ <strong>{gt text="Pending"}</strong> | <a href="{modurl modname='Groups' type='user' func='membership' action='cancel' gid=$group.gid}" title="{gt text='Cancel'}">{gt text="Cancel"}</a> ]</td>
            {else}
            {if $group.nbumax == 0 OR $group.nbumax gt $group.nbuser}
            {if $ismember eq true}
            {assign var="funcaction" value="unsubscribe"}
            {gt text="Resign" assign=mbfunctitle}
            {else}
            {assign var="funcaction" value="subscribe"}
            {gt text="Apply" assign=mbfunctitle}
            {/if}
            <td><a href="{modurl modname="Groups" type="user" func="membership" action=$funcaction gid=$group.gid}" title="{$mbfunctitle}">{$mbfunctitle}</a></td>
            {else}
            <td>{gt text="Group has reached its maximum capacity"}</td>
            {/if}
            {/if}
            {/if}
        </tr>
    </tbody>
</table>
<br />
<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Status"}</th>
            <th>{gt text="User name"}</th>
            {if $useProfileModule eq true}
            <th>{gt text="Avatar"}</th>
            <th>{gt text="Internal name"}</th>
            <th>{gt text="User's website"}</th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {section name='members' loop=$members}
        <tr class="{cycle values='z-odd,z-even'}">
            <td>{img modname='core' set='icons/extrasmall' src=$members[members].isonline alt=$members[members].isonlinelbl}</td>
            <td><strong>{$members[members].uname|profilelinkbyuname}</strong></td>
            {if $useProfileModule eq true}
            <td class="z-center">{useravatar uid=$members[members].uid}</td>
            <td>{$members[members]._UREALNAME|default:''}</td>
            <td>
                {if $members[members]._YOURHOMEPAGE|default:'' eq ''}&nbsp;
                {else}
                <a href="{$members[members]._YOURHOMEPAGE|safetext}" title="{$members[members]._YOURHOMEPAGE}">{img src="agt_internet.png" modname='core' set='icons/small' alt=$members[members]._YOURHOMEPAGE}</a>
                {/if}
            </td>
            {/if}
        </tr>
        {sectionelse}
        <tr class="z-datatableempty"><td colspan="{if $useProfileModule eq true}5{else}2{/if}">{gt text="No group members found."}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
