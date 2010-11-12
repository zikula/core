{gt text="Users list" assign=templatetitle}
{ajaxheader modname='Users'}
{include file="users_admin_menu.tpl"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    <div id="liveusersearch" class="z-hide z-form">
        <fieldset>
            <label for="username">{gt text="Search"}:</label>&nbsp;<input size="25" maxlength="25" type="text" id="username" value="" />
            <a id="modifyuser" href="javascript:void(0);" style="vertical-align:middle;">{img modname=core set=icons/extrasmall src="xedit.gif" __title="Edit" __alt="Edit"}</a>
            <a id="deleteuser" href="javascript:void(0);" style="vertical-align:middle;">{img modname=core set=icons/extrasmall src="14_layer_deletelayer.gif" __title="Delete" __alt="Delete"}</a>
            {img id="ajax_indicator" style="display: none;" modname=core set=icons/extrasmall src="indicator_circle.gif" alt=""}
            <div id="username_choices" class="autocomplete_user"></div>
            <script type="text/javascript">
                liveusersearch();
            </script>
        </fieldset>
    </div>

    <p id="users-alphafilter">
        <strong>[{pagerabc posvar="letter" forwardvars="sortby"}]</strong>
    </p>

    <table class="z-datatable">
        <thead>
            <tr>
                <th>
                    {assign var='currentCol' value='uname'}
                    {gt text="User name" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                <th>
                    {assign var='currentCol' value='uid'}
                    {gt text="Internal ID" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                <th>
                    {assign var='currentCol' value='user_regdate'}
                    {gt text="Registration date" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                <th>
                    {assign var='currentCol' value='lastlogin'}
                    {gt text="Last login" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                {if $canSeeGroups}
                <th>{gt text="User's groups"}</th>
                {/if}
                <th>
                    {assign var='currentCol' value='activated'}
                    {gt text="Status" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname='Users' type='admin' func='view' sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                <th>{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody class="z-clearer">
            {section name="usersitems" loop=$usersitems}
            <tr class="{cycle values='z-odd,z-even'}">
                <td>{$usersitems[usersitems].uname|safehtml}</td>
                <td>{$usersitems[usersitems].uid|safehtml}</td>
                <td>{$usersitems[usersitems].user_regdate|safehtml}</td>
                <td>{$usersitems[usersitems].lastlogin|safehtml}</td>
                {if $canSeeGroups}
                <td>
                    {foreach item=group from=$usersitems[usersitems].userGroupsView}
                    <div>{$allGroups[$group.gid].name}</div>
                    {/foreach}
                </td>
                {/if}
                <td class="z-center">{img modname=core set=icons/extrasmall src=$usersitems[usersitems].activation.image title=$usersitems[usersitems].activation.title alt=$usersitems[usersitems].activation.title}</td>
                <td class="z-right">
                    {assign var="options" value=$usersitems[usersitems].options}
                    {section name=options loop=$options}
                    <a href="{$options[options].url|safetext}">{img modname=core set=icons/extrasmall src=$options[options].image title=$options[options].title alt=$options[options].title}</a>
                    {/section}
                </td>
            </tr>
            {/section}
        </tbody>
    </table>

    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
</div>
