{gt text="Users list" assign=templatetitle}
{include file="users_admin_menu.tpl"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    <div id="liveusersearch" class="z-hide z-form">
        <fieldset>
            <label for="username">{gt text="Search"}:</label>&nbsp;<input size="25" maxlength="25" type="text" id="username" value="" />
            <a id="modifyuser" href="javascript:void(0);" style="vertical-align:middle;">{img modname=core set=icons/extrasmall src="xedit.gif" __title="Edit" __alt="Edit" class='tooltips'}</a>
            <a id="deleteuser" href="javascript:void(0);" style="vertical-align:middle;">{img modname=core set=icons/extrasmall src="14_layer_deletelayer.gif" __title="Delete" __alt="Delete" class='tooltips'}</a>
            {img id="ajax_indicator" style="display: none;" modname=core set="ajax" src="indicator_circle.gif" alt=""}
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
                    {sortlink __linktext='User name' sort='uname' currentsort=$sort sortdir=$sortdir modname='Users' type='admin' func='view'}
                </th>
                <th>
                    {sortlink __linktext='Internal ID' sort='uid' currentsort=$sort sortdir=$sortdir modname='Users' type='admin' func='view'}
                </th>
                <th>
                    {sortlink __linktext='Registration date' sort='user_regdate' currentsort=$sort sortdir=$sortdir modname='Users' type='admin' func='view'}
                </th>
                <th>
                    {sortlink __linktext='Last login' sort='lastlogin' currentsort=$sort sortdir=$sortdir modname='Users' type='admin' func='view'}
                </th>
                {if $canSeeGroups}
                <th>{gt text="User's groups"}</th>
                {/if}
                <th>
                    {sortlink __linktext='Status' sort='activated' currentsort=$sort sortdir=$sortdir modname='Users' type='admin' func='view'}
                </th>
                <th class="z-right">{gt text="Actions"}</th>
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
                <td class="z-center">{img modname=core set=icons/extrasmall src=$usersitems[usersitems].activation.image title=$usersitems[usersitems].activation.title alt=$usersitems[usersitems].activation.title class='tooltips'}</td>
                <td class="z-right">
                    {assign var="options" value=$usersitems[usersitems].options}
                    {section name=options loop=$options}
                    <a href="{$options[options].url|safetext}">{img modname=core set=icons/extrasmall src=$options[options].image title=$options[options].title alt=$options[options].title class='tooltips'}</a>
                    {/section}
                </td>
            </tr>
            {/section}
        </tbody>
    </table>

    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
</div>

<script type="text/javascript">
    Zikula.UI.Tooltips($$('.tooltips'));
</script>
