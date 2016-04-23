{insert name='csrftoken' assign='csrftoken'}
{section name="usersitems" loop=$usersitems}
    <tr class="user">
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
                {case expr='Zikula\UsersModule\Constant::ACTIVATED_ACTIVE'|const}
                    <span class="label label-success">{gt text='Active'}</span>
                {/case}
                {case expr='Zikula\UsersModule\Constant::ACTIVATED_INACTIVE'|const}
                    <span class="label label-danger">{gt text='Inactive'}</span>
                {/case}
                {case expr='Zikula\UsersModule\Constant::ACTIVATED_PENDING_DELETE'|const}
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
                <a class="fa fa-user tooltips" href="{route name='zikulausersmodule_admin_lostusername' userid=$usersitems[usersitems].uid csrftoken=$csrftoken}" title="{$title}"></a>
            {else}
                {img modname='core' set='icons/extrasmall' src='lostusername.png' class=" hidden "}
            {/if}
            {if $available_options.lostPassword}
                {if $usersitems[usersitems].options.lostPassword}
                    {gt text="Send password recovery code to '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                    <a class="fa fa-key tooltips" href="{route name='zikulausersmodule_admin_lostpassword' userid=$usersitems[usersitems].uid csrftoken=$csrftoken}" title="{$title}"></a>
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
                    <a href="{route name='zikulausersmodule_admin_toggleforcedpasswordchange' userid=$usersitems[usersitems].uid}">{img modname='core' set='icons/extrasmall' src=$image title=$title alt=$title class='tooltips'}</a>
                {else}
                    <span class="fa-fw"></span>
                {/if}
            {/if}
            {if $available_options.modify}
                {if $usersitems[usersitems].options.modify}
                    {gt text="Edit '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                    <a class="fa fa-pencil tooltips" href="{route name='zikulausersmodule_useradministration_modify' user=$usersitems[usersitems].uid}" title="{$title}"></a>
                {else}
                    <span class="fa-fw"></span>
                {/if}
            {/if}
            {if $available_options.deleteUsers}
                {if $usersitems[usersitems].options.deleteUsers}
                    {gt text="Delete '%s'" tag1=$usersitems[usersitems].uname assign='title'}
                    <a class="fa fa-trash-o tooltips" href="{route name='zikulausersmodule_admin_deleteusers' userid=$usersitems[usersitems].uid}" title="{$title}"></a>
                {else}
                    <span class="fa-fw"></span>
                {/if}
            {/if}
        </td>
        {/if}
    </tr>
{/section}
