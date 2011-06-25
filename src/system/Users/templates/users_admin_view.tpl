{ajaxheader modname=$modinfo.name filename='users.js' ui=true}
{strip}
{insert name='csrftoken' assign='csrftoken'}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        liveusersearch();
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}
{/strip}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Users list"}</h3>
</div>

<div id="liveusersearch" class="z-hide z-form">
    <fieldset>
        <label for="username">{gt text="Search"}:</label>&nbsp;<input size="25" maxlength="25" type="text" id="username" value="" />
        <a id="modifyuser" href="javascript:void(0);" style="vertical-align:middle;">{img modname=core set=icons/extrasmall src="xedit.png" __title="Edit" __alt="Edit" class='tooltips'}</a>
        <a id="deleteuser" href="javascript:void(0);" style="vertical-align:middle;">{img modname=core set=icons/extrasmall src="14_layer_deletelayer.png" __title="Delete" __alt="Delete" class='tooltips'}</a>
        {img id="ajax_indicator" style="display: none;" modname=core set="ajax" src="indicator_circle.gif" alt=""}
        <div id="username_choices" class="autocomplete_user"></div>
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
            <th class="z-center">
                {sortlink __linktext='Status' sort='activated' currentsort=$sort sortdir=$sortdir modname='Users' type='admin' func='view'}
            </th>
            <th colspan="{$available_options|@array_sum}">
                {gt text="Actions"}
            </th>
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
                {foreach item='group' from=$usersitems[usersitems].userGroupsView}
                <div>{$allGroups[$group.gid].name}</div>
                {/foreach}
            </td>
            {/if}
            <td class="users_activated">{strip}
                {switch expr=$usersitems[usersitems].activated}
                {case expr='Users_Constant::ACTIVATED_ACTIVE'|const}
                {img modname=core set=icons/extrasmall src='greenled.png' __title='Active' __alt='Active' class='tooltips'}
                {/case}
                {case expr='Users_Constant::ACTIVATED_INACTIVE'|const}
                {img modname=core set=icons/extrasmall src='yellowled.png' __title='Inactive' __alt='Inactive' class='tooltips'}
                {/case}
                {case expr='Users_Constant::ACTIVATED_PENDING_DELETE'|const}
                {img modname=core set=icons/extrasmall src='14_layer_deletelayer.png' __title='Inactive, marked for deletion' __alt='Inactive, marked for deletion' class='tooltips'}
                {/case}
                {case}
                {img modname=core set=icons/extrasmall src='error.png' __title='Status unknown' __alt='Status unknown' class='tooltips'}
                {/case}
                {/switch}
            {/strip}</td>
            {if $available_options.lostUsername}
            <td class="users_action">
                {if $usersitems[usersitems].options.lostUsername}
                {gt text="Send user name to '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a href="{modurl modname='Users' type='admin' func='lostUsername' userid=$usersitems[usersitems].uid csrftoken=$csrftoken}">{img modname='core' set='icons/extrasmall' src='lostusername.png' title=$title alt=$title class='tooltips'}</a>
                {else}
                {img modname='core' set='icons/extrasmall' src='lostusername.png' class="z-invisible"}
                {/if}
            </td>
            {/if}
            {if $available_options.lostPassword}
            <td class="users_action">
                {if $usersitems[usersitems].options.lostPassword}
                {gt text="Send password recovery code to '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a href="{modurl modname='Users' type='admin' func='lostPassword' userid=$usersitems[usersitems].uid csrftoken=$csrftoken}">{img modname='core' set='icons/extrasmall' src='lostpassword.png' title=$title alt=$usersitems[usersitems].options.lostPassword.title class='tooltips'}</a>
                {else}
                {img modname='core' set='icons/extrasmall' src='lostpassword.png' class="z-invisible"}
                {/if}
            </td>
            {/if}
            {if $available_options.toggleForcedPasswordChange}
            <td class="users_action">
                {if $usersitems[usersitems].options.toggleForcedPasswordChange}
                {if $usersitems[usersitems]._Users_mustChangePassword}
                {gt text="Cancel required change of password for '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                {assign var='image' value='password.png'}
                {else}
                {gt text="Require '%s' to change password at next login" tag1=$usersitems[usersitems].uname assign='title'}
                {assign var='image' value='password_expire.png'}
                {/if}
                <a href="{modurl modname='Users' type='admin' func='toggleForcedPasswordChange' userid=$usersitems[usersitems].uid}">{img modname='core' set='icons/extrasmall' src=$image title=$title alt=$title class='tooltips'}</a>
                {else}
                {assign var='image' value='password_expire.png'}
                {img modname='core' set='icons/extrasmall' src=$image class="z-invisible"}
                {/if}
            </td>
            {/if}
            {if $available_options.modify}
            <td class="users_action">
                {if $usersitems[usersitems].options.modify}
                {gt text="Edit '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a href="{modurl modname='Users' type='admin' func='modify' userid=$usersitems[usersitems].uid}">{img modname='core' set='icons/extrasmall' src='xedit.png' title=$title alt=$title class='tooltips'}</a>
                {else}
                {img modname='core' set='icons/extrasmall' src='xedit.png' class="z-invisible"}
                {/if}
            </td>
            {/if}
            {if $available_options.deleteUsers}
            <td class="users_action">
                {if $usersitems[usersitems].options.deleteUsers}
                {gt text="Delete '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a href="{modurl modname='Users' type='admin' func='deleteUsers' userid=$usersitems[usersitems].uid}">{img modname='core' set='icons/extrasmall' src='14_layer_deletelayer.png' title=$title alt=$title class='tooltips'}</a>
                {else}
                {img modname='core' set='icons/extrasmall' src='14_layer_deletelayer.png' title=$usersitems[usersitems].options.deleteUsers.title alt=$usersitems[usersitems].options.deleteUsers.title class="z-invisible"}
                {/if}
            </td>
            {/if}
        </tr>
        {/section}
    </tbody>
</table>

{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}