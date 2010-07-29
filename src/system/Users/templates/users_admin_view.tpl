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

    <table class="z-admintable">
        <thead>
            <tr>
                <th><span class="z-floatleft">{gt text="User name"}</span><span class="z-floatright"><a href="{modurl modname='Users' type='admin' func='view' sort='uname' sortdir='ASC'}">{img modname='core' set='icons' src='extrasmall/14_layer_raiselayer.gif' __alt='+' __title='+'}</a><a href="{modurl modname='Users' type='admin' func='view' sort='uname' sortdir='DESC'}">{img modname='core' set='icons' src='extrasmall/14_layer_lowerlayer.gif' __alt='-' __title='-'}</a></span></th>
                <th><span class="z-floatleft">{gt text="Internal ID"}</span><span class="z-floatright"><a href="{modurl modname='Users' type='admin' func='view' sort='uid' sortdir='ASC'}">{img modname='core' set='icons' src='extrasmall/14_layer_raiselayer.gif' __alt='+' __title='+'}</a><a href="{modurl modname='Users' type='admin' func='view' sort='uid' sortdir='DESC'}">{img modname='core' set='icons' src='extrasmall/14_layer_lowerlayer.gif' __alt='-' __title='-'}</a></span></th>
                <th><span class="z-floatleft">{gt text="Registration date"}</span><span class="z-floatright"><a href="{modurl modname='Users' type='admin' func='view' sort='user_regdate' sortdir='ASC'}">{img modname='core' set='icons' src='extrasmall/14_layer_raiselayer.gif' __alt='+' __title='+'}</a><a href="{modurl modname='Users' type='admin' func='view' sort='user_regdate' sortdir='DESC'}">{img modname='core' set='icons' src='extrasmall/14_layer_lowerlayer.gif' __alt='-' __title='-'}</a></span></th>
                <th><span class="z-floatleft">{gt text="Last login"}</span><span class="z-floatright"><a href="{modurl modname='Users' type='admin' func='view' sort='lastlogin' sortdir='ASC'}">{img modname='core' set='icons' src='extrasmall/14_layer_raiselayer.gif' __alt='+' __title='+'}</a><a href="{modurl modname='Users' type='admin' func='view' sort='lastlogin' sortdir='DESC'}">{img modname='core' set='icons' src='extrasmall/14_layer_lowerlayer.gif' __alt='-' __title='-'}</a></span></th>
                {if $canSeeGroups}
                <th><span class="z-floatleft">{gt text="User's groups"}</span></th>
                {/if}
                <th><span class="z-floatleft">{gt text="Status"}</span><span class="z-floatright"><a href="{modurl modname='Users' type='admin' func='view' sort='activated' sortdir='ASC'}">{img modname='core' set='icons' src='extrasmall/14_layer_raiselayer.gif' __alt='+' __title='+'}</a><a href="{modurl modname='Users' type='admin' func='view' sort='activated' sortdir='DESC'}">{img modname='core' set='icons' src='extrasmall/14_layer_lowerlayer.gif' __alt='-' __title='-'}</a></span></th>
                <th><span class="z-floatright">{gt text="Actions"}</span></th>
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
