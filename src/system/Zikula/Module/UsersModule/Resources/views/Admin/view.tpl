{pageaddvar name="javascript" value="javascript/select2/select2.min.js"}
{pageaddvar name="stylesheet" value="javascript/select2/select2.css"}
{pageaddvar name="javascript" value="system/Zikula/Module/UsersModule/Resources/public/js/Zikula.Users.Admin.View.js"}


{insert name='csrftoken' assign='csrftoken'}

{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text="Users list"}
</h3>

<div id="liveusersearch" class="hide z-form">
    <fieldset>
        <input size="25" maxlength="25" type="text" id="username" value="" style="width: 250px" title="{gt text="Search for a user name"}"/>
        <a id="modifyuser" style="vertical-align:middle;color:black;font-size:120%;margin-left:10px" class="tooltips bold hide" title="{gt text="Edit"}"><i class="fa fa-edit"></i></a>
        <a id="deleteuser" style="vertical-align:middle;color:black;font-size:120%" class="tooltips bold hide" title="{gt text="Delete"}"><i class="fa fa-trash-o"></i></a>
    </fieldset>
</div>

<p id="users-alphafilter">
    {pagerabc posvar="letter" forwardvars="sortby" printempty=true}
</p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>
                {sortlink __linktext='User name' sort='uname' currentsort=$sort sortdir=$sortdir modname='ZikulaUsersModule' type='admin' func='view'}
            </th>
            <th>
                {sortlink __linktext='Internal ID' sort='uid' currentsort=$sort sortdir=$sortdir modname='ZikulaUsersModule' type='admin' func='view'}
            </th>
            <th>
                {sortlink __linktext='Registration date' sort='user_regdate' currentsort=$sort sortdir=$sortdir modname='ZikulaUsersModule' type='admin' func='view'}
            </th>
            <th>
                {sortlink __linktext='Last login' sort='lastlogin' currentsort=$sort sortdir=$sortdir modname='ZikulaUsersModule' type='admin' func='view'}
            </th>
            {if $canSeeGroups}
            <th>{gt text="User's groups"}</th>
            {/if}
            <th class="text-center">
                {sortlink __linktext='Status' sort='activated' currentsort=$sort sortdir=$sortdir modname='ZikulaUsersModule' type='admin' func='view'}
            </th>
            <th>
                {gt text="Actions"}
            </th>
        </tr>
    </thead>
    <tbody>
        {section name="usersitems" loop=$usersitems}
        <tr>
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
                {case expr='Zikula\Module\UsersModule\Constant::ACTIVATED_ACTIVE'|const}
                <span class="label label-success">{gt text='Active'}</span>
                {/case}
                {case expr='Zikula\Module\UsersModule\Constant::ACTIVATED_INACTIVE'|const}
                <span class="label label-danger">{gt text='Inactive'}</span>
                {/case}
                {case expr='Zikula\Module\UsersModule\Constant::ACTIVATED_PENDING_DELETE'|const}
                {img modname=core set=icons/extrasmall src='14_layer_deletelayer.png' __title='Inactive, marked for deletion' __alt='Inactive, marked for deletion' class='tooltips'}
                {/case}
                {case}
                {img modname='core' set='icons/extrasmall' src='error.png' __title='Status unknown' __alt='Status unknown' class='tooltips'}
                {/case}
                {/switch}
            {/strip}</td>
            {if $available_options.lostUsername}
            <td class="actions">
                {if $usersitems[usersitems].options.lostUsername}
                {gt text="Send user name to '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a class="fa fa-user tooltips" href="{modurl modname='ZikulaUsersModule' type='admin' func='lostUsername' userid=$usersitems[usersitems].uid csrftoken=$csrftoken}"></a>
                {else}
                {img modname='core' set='icons/extrasmall' src='lostusername.png' class=" hidden "}
                {/if}
            {/if}
            {if $available_options.lostPassword}
                {if $usersitems[usersitems].options.lostPassword}
                {gt text="Send password recovery code to '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a class="fa fa-key tooltips" href="{modurl modname='ZikulaUsersModule' type='admin' func='lostPassword' userid=$usersitems[usersitems].uid csrftoken=$csrftoken}" title="{$title}"></a>
                {else}
                <span class="fa-fw"></span>
                {/if}
            {/if}
            {if $available_options.toggleForcedPasswordChange}
                {if $usersitems[usersitems].options.toggleForcedPasswordChange}
                {if $usersitems[usersitems]._Users_mustChangePassword}
                {gt text="Cancel required change of password for '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                {assign var='image' value='password.png'}
                {else}
                {gt text="Require '%s' to change password at next login" tag1=$usersitems[usersitems].uname assign='title'}
                {assign var='image' value='password_expire.png'}
                {/if}
                <a href="{modurl modname='ZikulaUsersModule' type='admin' func='toggleForcedPasswordChange' userid=$usersitems[usersitems].uid}">{img modname='core' set='icons/extrasmall' src=$image title=$title alt=$title class='tooltips'}</a>
                {else}
                <span class="fa-fw"></span>
                {/if}
            {/if}
            {if $available_options.modify}
                {if $usersitems[usersitems].options.modify}
                {gt text="Edit '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a class="fa fa-pencil tooltips" href="{modurl modname='ZikulaUsersModule' type='admin' func='modify' userid=$usersitems[usersitems].uid}" title="{$title}"></a>
                {else}
                <span class="fa-fw"></span>
                {/if}
            {/if}
            {if $available_options.deleteUsers}
                {if $usersitems[usersitems].options.deleteUsers}
                {gt text="Delete '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                <a class="fa fa-trash-o fa-fw tooltips" href="{modurl modname='ZikulaUsersModule' type='admin' func='deleteUsers' userid=$usersitems[usersitems].uid}" title="{$title}"></a>
                {else}
                <span class="fa-fw"></span>
                {/if}
            </td>
            {/if}
        </tr>
        {/section}
    </tbody>
</table>

{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}