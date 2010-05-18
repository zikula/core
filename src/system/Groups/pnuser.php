<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Groups
 * @license http://www.gnu.org/copyleft/gpl.html
*/
/**
 * Groups Module main user function
 * This function is the default function, and is called whenever the
 * module is initiated without defining arguments.  As such it can
 * be used for a number of things, but most commonly it either just
 * shows the module menu and returns or calls whatever the module
 * designer feels should be the default function (often this is the
 * view() function)
 * @author Frank Chestnut
 * @return string HTML output string
 */
function Groups_user_main()
{
    // Security check will be done in view()
    return Groups_user_view();
}

/**
 * Display items
 * This is a standard function to provide detailed information
 * available from the module.
 * @author Frank Chestnut
 * @return string HTML string
 */
function Groups_user_view()
{
    // Security check
    if (!SecurityUtil::checkPermission('Groups::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    // Get parameters from whatever input we need.
    $startnum = (int)FormUtil::getPassedValue('startnum', null, 'GET');
    $show = FormUtil::getPassedValue('show', null, 'GET');
    $showgid = FormUtil::getPassedValue('showgid', null, 'GET');

    // we need this value multiple times, so we keep it
    $itemsperpage = ModUtil::getVar('Groups', 'itemsperpage');

    if (UserUtil::isLoggedIn()) {
        $islogged = true;
    } else {
        $islogged = false;
    }

    // The user API function is called.
    $groups = ModUtil::apiFunc('Groups', 'user', 'getallgroups',
                           array('startnum' => $startnum,
                                 'numitems' => $itemsperpage,
                                 'uid'      => UserUtil::getVar('uid'),
                                 'islogged' => $islogged));

    $pnRender = Renderer::getInstance('Groups', false);
    $pnRender->add_core_data();
    $pnRender->assign('mainpage', true);

    // The return value of the function is checked here, and if the function
    // failed then an appropriate message is posted.
    if ($groups == false) {
        $pnRender->assign('nogroups', true);
        return $pnRender->fetch('groups_user_view.htm');
    }

    $groupitems = array();
    $typelabel  = array();
    $statelabel = array();

    $typelabel = array('-1' => __('Core'),
                       '0'  => __('Core'),
                       '1'  => __('Public'),
                       '2'  => __('Private'));

    $statelabel = array('0' => __('Closed'),
                        '1' => __('Open'));

    foreach ($groups as $group) {

        if (SecurityUtil::checkPermission('Groups::', $group['gid'].'::', ACCESS_OVERVIEW)) {

            $group['typelbl']  = $typelabel[$group['gtype']];
            $group['statelbl'] = $statelabel[$group['state']];

            $pnRender->assign($group);

            if ($islogged == true && SecurityUtil::checkPermission('Groups::', $group['gid'].'::', ACCESS_READ)) {
                // The right to apply
                $groupitems[] = $pnRender->fetch('groups_user_grouprow_read.htm', $group['gid']);
            } else {
                // No right to apply
                $groupitems[] = $pnRender->fetch('groups_user_grouprow_overview.htm', $group['gid']);
            }
        }
    }

    $pnRender->add_core_data();
    $pnRender->assign('nogroups', false);
    $pnRender->assign('items', $groupitems);

    $pnRender->assign('pager', array('numitems'     => ModUtil::apiFunc('Groups', 'user', 'countitems'),
                                     'itemsperpage' => $itemsperpage));

    return $pnRender->fetch('groups_user_view.htm');
}

/**
 * display the membership of a public group
 *
 */
function Groups_user_membership()
{
    if (!SecurityUtil::checkPermission('Groups::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    $gid = (int)FormUtil::getPassedValue('gid', null, 'GET');
    $action = FormUtil::getPassedValue('action', null, 'GET');

    if (empty($gid) || !is_numeric($gid) || empty($action)) {
        return LogUtil::registerArgsError();
    }

    if (!UserUtil::isLoggedIn()) {
        return LogUtil::registerError(__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
    }

    $uid = UserUtil::getVar('uid');

    // Check if the group exists
    $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $gid));

    if (!$group) {
        return DataUtil::formatForDisplay(__("Error! That group does not exist."));
    }

    // And lastly, we must check if he didn't rewrote the url,
    // that is he applying to an open group and that the group is open
    // $isopen = ModUtil::apiFunc('Groups', 'user', 'getginfo', array('gid' => $gid));
    if ($action == 'subscribe') {
        if (ModUtil::apiFunc('Groups', 'user', 'isgroupmember',array('gid' => $gid, 'uid' => $uid))) {
            return DataUtil::formatForDisplay(__('Error! You are already a member of this group.'));
        }

        if ($group['gtype'] <= 0) {
            return DataUtil::formatForDisplay(__('Sorry! You cannot apply for membership of that group.'));
        }

        if ($group['nbumax'] != 0) {
            if (($group['nbumax'] - $group['nbuser']) <= 0) {
                return DataUtil::formatForDisplay(__('Sorry! That group has reached full membership.'));
            }
        }

        if ($group['state'] == 0) {
            return DataUtil::formatForDisplay(__('Sorry! That group is closed.'));
        }
    }

    $pnRender = Renderer::getInstance('Groups');

    $pnRender->add_core_data();

    $pnRender->assign('mainpage',     true);
    $pnRender->assign('hooks',        false);
    $pnRender->assign('gid',          $gid);
    $pnRender->assign('gname',        $group['name']);
    $pnRender->assign('gtype',        $group['gtype']); // Can't use type as it is a reserved word.
    $pnRender->assign('action',       $action);
    $pnRender->assign('description',  $group['description']);

    return $pnRender->fetch('groups_user_membership.htm');
}

/*
 * update a users group applications
 *
 */
function Groups_user_userupdate()
{
    $gid = (int)FormUtil::getPassedValue('gid', null, 'POST');
    $action = FormUtil::getPassedValue('action', null, 'POST');
    $gtype = FormUtil::getPassedValue('gtype', null, 'POST');
    $tag = FormUtil::getPassedValue('tag', null, 'POST');

    if (empty($gid) || !is_numeric($gid) || empty($action)) {
        return LogUtil::registerArgsError();
    }

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(ModUtil::url('Groups', 'user', 'main'));
    }

    if (empty($tag)) return DataUtil::formatForDisplay(__('Error! You must click on the checkbox to confirm your action.'));

    $applytext = '';
    if ($action == 'subscribe' && $gtype == 2) {
        $applytext = FormUtil::getPassedValue('applytext', null, 'POST');
    }

    $result = ModUtil::apiFunc('Groups', 'user', 'userupdate',
                           array('gid'       => $gid,
                                 'action'    => $action,
                                 'gtype'     => $gtype,
                                 'applytext' => $applytext));

    if ($result == true) {
        LogUtil::registerStatus(__('Done! Saved the action.'));
    }

    $pnRender = Renderer::getInstance('Groups');
    $pnRender->clear_cache('groups_user_memberslist.htm');

    return pnRedirect(ModUtil::url('Groups', 'user', 'main'));
}

/**
 * display the membership of a group
 *
 */
function Groups_user_memberslist()
{
    $gid = (int)FormUtil::getPassedValue('gid', null, 'GET');
    $startnum = (int)FormUtil::getPassedValue('startnum', 1, 'GET');

    if (!is_numeric($startnum)) {
        return LogUtil::registerArgsError();
    }

    $itemsperpage = ModUtil::getVar('Groups', 'itemsperpage');

    if (!SecurityUtil::checkPermission('Groups::memberslist', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid'      => $gid,
                                                         'numitems' => $itemsperpage,
                                                         'startnum' => $startnum));

    if (!$group) {
        return DataUtil::formatForDisplay(__('Error! Could not load data.'));
    }

    $uid = UserUtil::getVar('uid');

    $typelabel  = array();
    $statelabel = array();

    $typelabel = array('-1' => __('Core'),
                       '0'  => __('Core'),
                       '1'  => __('Public'),
                       '2'  => __('Private'));

    $statelabel = array('0' => __('Closed'),
                        '1' => __('Open'));

    $group['typelbl']  = $typelabel[$group['gtype']];
    $group['statelbl'] = $statelabel[$group['state']];

    $pnRender = Renderer::getInstance('Groups');
    $pnRender->assign('mainpage', false);

    $pnRender->add_core_data();

    $pnRender->assign('group', $group);

    if ($group['members']) {
        $onlines = ModUtil::apiFunc('Groups', 'user', 'whosonline', array());
        $members = array();
        foreach($group['members'] as $userid) {
            $userinfo = UserUtil::getVars($userid['uid']);

            $isonline = false;
            if (is_array($onlines)) {
                foreach($onlines as $online) {
                    if ($online['uid'] == $userid['uid']) {
                        $isonline = true;
                    }
                }
            }

            if ($isonline) {
                $userinfo['isonline']    = 'greenled.gif';
                $userinfo['isonlinelbl'] = __('on-line');
            } else {
                $userinfo['isonline']     = 'redled.gif';
                $userinfo['isonlinelbl'] = __('off-line');
            }

            $members[] = $userinfo;
        }

        // test of sorting data
        if (!empty($members)) {
            foreach($members as $res) {
                $sortAarr[] = strtolower($res['uname']);
            }
             array_multisort($sortAarr, SORT_ASC, $members);
        }
        $pnRender->assign('members', $members);
    } else {
        $pnRender->assign('members', false);
    }

    $pnRender->assign('ismember', ModUtil::apiFunc('Groups', 'user', 'isgroupmember', array('gid' => $gid, 'uid' => $uid)));

    $pnRender->assign('pager', array('numitems'     => ModUtil::apiFunc('Groups', 'user', 'countgroupmembers', array('gid' => $gid)),
                                     'itemsperpage' => $itemsperpage));

    $pnRender->assign('hooks', ModUtil::callHooks('item',
                                              'display',
                                              $gid,
                                              ModUtil::url('Groups',
                                                       'user',
                                                       'memberslist',
                                                       array('gid' => $gid))));

    $profileModule = System::getVar('profilemodule', '');
    $pnRender->assign('useProfileModule', (!empty($profileModule) && $profileModule == 'Profile' && ModUtil::available($profileModule)));

    return $pnRender->fetch('groups_user_memberslist.htm');
}
