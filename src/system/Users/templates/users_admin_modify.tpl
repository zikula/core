{gt text='Edit user account of %s' tag1=$userinfo._UREALNAME|default:$userinfo.uname assign='templatetitle'}
{include file='users_admin_menu.tpl'}

{if $legal}
{gt text="'Terms of use'" assign=touString}
{gt text="'Privacy policy'" assign=ppString}
{if $tou_active && $pp_active}
{gt text='%1$s and %2$s' tag1=$touString tag2=$ppString assign=touppTextString}
{elseif  $tou_active}
{assign var='touppTextString' value=$touString}
{elseif $pp_active}
{assign var='touppTextString' value=$ppString}
{/if}
{/if}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='xedit.gif' set='icons/large' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {if ($userinfo.uid == $coredata.user.uid)}
    <div class="z-informationmsg">{gt text='You are editing your own record, therefore you are not permitted to change your membership in certain system groups, and you are not permitted to change your activated state. These fields are disabled below.'}</div>
    {/if}

    <form class="z-form" id="form_users_modify" action="{modurl modname='Users' type='admin' func='update'}" method="post">
        <div>
            {capture name='authid' assign='usersModifyFormAuthId'}{insert name='generateauthkey' module='Users'}{/capture}
            <input type="hidden" name="authid" value="{$usersModifyFormAuthId}" />
            <input type="hidden" name="userinfo[uid]" value="{$userinfo.uid}" />
            <fieldset>
                <legend>{gt text='Group membership'}</legend>
                <table class="z-datatable">
                    <thead>
                        <tr>
                            <th>{gt text='Group'}</th>
                            <th>{gt text='Member'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach key='group_id' item='group' from=$accessPermissions}
                        <tr class="{cycle values='z-odd,z-even'}">
                            <td>{$group.name}</td>
                            <td style="text-align:right;">{if ($userinfo.uid == $coredata.user.uid) && ((($group_id == $defaultgroupid) && $group.access) || ($group_id == $primaryadmingroupid))}<input type="hidden" name="access_permissions[]" value="{$group_id}" />{/if}<input type="checkbox" {if ($userinfo.uid == $coredata.user.uid) && ((($group_id == $defaultgroupid) && $group.access) || ($group_id == $primaryadmingroupid))}disabled="disabled"{else}name="access_permissions[]" value="{$group_id}"{/if} {if $group.access}checked="checked" {/if}/></td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend>{gt text='Personal information'}</legend>
                <div class="z-formrow">
                    <label for="users_uname">{gt text='User name'}</label>
                    <input id="users_uname" type="text" name="userinfo[uname]" value="{$userinfo.uname|safetext}" size="30" maxlength="60" />
                    <em class="z-formnote z-sub">{gt text='User names can contain letters, numbers, underscores, and/or periods.'}</em>
                </div>
                <div class="z-formrow">
                    <label for="password1">{gt text='Password'}</label>
                    <input id="password1" type="password" name="userinfo[pass]" size="15" />
                </div>
                <div class="z-formrow">
                    <label for="password2">{gt text='Password (repeat for verification)'}</label>
                    <input id="password2" type="password" name="passagain" size="15" />
                </div>
                <div class="z-formrow">
                    <label for="users_email">{gt text='E-mail address'}</label>
                    <input id="users_email" type="text" name="userinfo[email]" value="{$userinfo.email|safetext}" size="30" maxlength="60" />
                </div>
                <div class="z-formrow">
                    <label for="users_emailagain">{gt text='E-mail address (repeat for verification)'}</label>
                    <input id="users_emailagain" type="text" name="emailagain" value="{$userinfo.email|safetext}" size="30" maxlength="60" />
                </div>
                <div class="z-formrow">
                    <label for="users_activated">{gt text='User status'}</label>
                    {if $userinfo.uid == $coredata.user.uid}<input type="hidden" name="userinfo[activated]" value="{$userinfo.activated}" />{/if}
                    <select id="users_activated" {if $userinfo.uid != $coredata.user.uid}name="userinfo[activated]"{else}name="displayonly_activated" disabled="disabled"{/if}>
                        <option value="{'UserUtil::ACTIVATED_INACTIVE'|constant}" {if $userinfo.activated eq 'UserUtil::ACTIVATED_INACTIVE'|constant}selected="selected"{/if}>{gt text="Inactive"}</option>
                        {if $legal && ($tou_active || $pp_active eq true)}
                        <option value="{'UserUtil::ACTIVATED_INACTIVE_TOUPP'|constant}" {if $userinfo.activated eq 'UserUtil::ACTIVATED_INACTIVE_TOUPP'|constant}selected="selected"{/if}>{gt text="Inactive until %s accepted" tag1=$touppTextString}</option>
                        {/if}
                        <option value="{'UserUtil::ACTIVATED_INACTIVE_PWD'|constant}" {if $userinfo.activated eq 'UserUtil::ACTIVATED_INACTIVE_PWD'|constant}selected="selected"{/if}>{gt text="Force password change on login"}</option>
                        {if $legal && ($tou_active || $pp_active eq true)}
                        <option value="{'UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP'|constant}" {if $userinfo.activated eq 'UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP'|constant}selected="selected"{/if}>{gt text="Force password change and accept %s" tag1=$touppTextString}</option>
                        {/if}
                        <option value="{'UserUtil::ACTIVATED_ACTIVE'|constant}" {if $userinfo.activated eq 'UserUtil::ACTIVATED_ACTIVE'|constant}selected="selected"{/if}>{gt text="Active"}</option>
                    </select>
                    {if !$legal}
                        <p class="z-formnote z-warningmsg">{gt text="Notice: The option \"Inactive until 'Terms of use' and 'Privacy policy' accepted\" is not possible because the module Legal is not available."}</p>
                    {/if}
                </div>
                <div class="z-formrow">
                    <label for="users_theme">{gt text='Theme'}</label>
                    <select id="users_theme" name="userinfo[theme]">
                        <option value="">{gt text="Site's default theme"}</option>
                        {html_select_themes selected=$userinfo.theme state=PNTHEME_STATE_ACTIVE filter=PNTHEME_FILTER_USER}
                    </select>
                </div>
            </fieldset>

            {if !empty($modvars.ZConfig.profilemodule)}
                {modfunc modname=$modvars.ZConfig.profilemodule type='form' func='edit' userid=$userinfo.uid}
            {/if}

            {notifydisplayhooks eventname='users.hook.user.ui.edit' area='modulehook_area.users.user' subject=$userinfo id=$userinfo.uid caller="Users"}

            <div class="z-center z-buttons">
                {button src='button_ok.gif' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
                <a href="{modurl modname='Users' type='admin' func='view'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
                {if $userinfo.uid != $coredata.user.uid}<a href="{modurl modname='Users' type='admin' func='deleteusers' userid=$userinfo.uid}">{img modname='core' set='icons/extrasmall' src="delete_user.gif" __alt='Delete' __title='Delete'} {gt text='Delete'}</a>{/if}
                <a href="{modurl modname='Users' type='admin' func='lostUsername' uid=$userinfo.uid authid=$usersModifyFormAuthId}">{img modname='core' set='icons/extrasmall' src="lostusername.gif" __alt='Send user name' __title='Send user name'} {gt text='Send user name'}</a>
                <a href="{modurl modname='Users' type='admin' func='lostPassword' uid=$userinfo.uid authid=$usersModifyFormAuthId}">{img modname='core' set='icons/extrasmall' src="lostpassword.gif" __alt='Send password recovery code' __title='Send password recovery code'} {gt text='Send password recovery code'}</a>
            </div>
        </div>
    </form>
</div>
