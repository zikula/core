<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Groups_Controller_User extends Zikula_AbstractController
{
    /**
     * Groups Module main user function
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     * @return string HTML output string
     */
    public function main()
    {
        // Security check will be done in view()
        $this->redirect(ModUtil::url('Groups', 'user', 'view'));
    }

    /**
     * Display items
     * This is a standard function to provide detailed information
     * available from the module.
     * @return string HTML string
     */
    public function view()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Groups::', '::', ACCESS_OVERVIEW));

        // Get parameters from whatever input we need.
        $startnum = (int)FormUtil::getPassedValue('startnum', null, 'GET');
        $show = FormUtil::getPassedValue('show', null, 'GET');
        $showgid = FormUtil::getPassedValue('showgid', null, 'GET');

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

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

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('mainpage', true);

        // The return value of the function is checked here, and if the function
        // failed then an appropriate message is posted.
        if ($groups == false) {
            $this->view->assign('nogroups', true);

            return $this->view->fetch('groups_user_view.tpl');
        }

        $groupitems = array();
        $typelabel  = array();
        $statelabel = array();

        $groupsCommon = new Groups_Helper_Common();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        foreach ($groups as $group) {

            if (SecurityUtil::checkPermission('Groups::', $group['gid'].'::', ACCESS_OVERVIEW)) {

                $group['typelbl']  = $typelabel[$group['gtype']];
                $group['statelbl'] = $statelabel[$group['state']];

                $this->view->assign($group);

                if ($islogged == true && SecurityUtil::checkPermission('Groups::', $group['gid'].'::', ACCESS_READ)) {
                    // The right to apply
                    $groupitems[] = $this->view->fetch('groups_user_grouprow_read.tpl', $group['gid']);
                } else {
                    // No right to apply
                    $groupitems[] = $this->view->fetch('groups_user_grouprow_overview.tpl', $group['gid']);
                }
            }
        }

        $this->view->assign('nogroups', false)
                   ->assign('items', $groupitems);

        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('Groups', 'user', 'countitems'),
                                           'itemsperpage' => $itemsperpage));

        return $this->view->fetch('groups_user_view.tpl');
    }

    /**
     * display the membership of a public group
     *
     */
    public function membership()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Groups::', '::', ACCESS_OVERVIEW));

        $gid = (int)FormUtil::getPassedValue('gid', null, 'GET');
        $action = FormUtil::getPassedValue('action', null, 'GET');

        if (empty($gid) || !is_numeric($gid) || empty($action)) {
            return LogUtil::registerArgsError();
        }

        if (!UserUtil::isLoggedIn()) {
            return LogUtil::registerError($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }

        $uid = UserUtil::getVar('uid');

        // Check if the group exists
        $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $gid));

        if (!$group) {
            return DataUtil::formatForDisplay($this->__("Error! That group does not exist."));
        }

        // And lastly, we must check if he didn't rewrote the url,
        // that is he applying to an open group and that the group is open
        // $isopen = ModUtil::apiFunc('Groups', 'user', 'getginfo', array('gid' => $gid));
        if ($action == 'subscribe') {
            if (ModUtil::apiFunc('Groups', 'user', 'isgroupmember',array('gid' => $gid, 'uid' => $uid))) {
                return DataUtil::formatForDisplay($this->__('Error! You are already a member of this group.'));
            }

            if ($group['gtype'] == Groups_Helper_Common::GTYPE_CORE) {
                return DataUtil::formatForDisplay($this->__('Sorry! You cannot apply for membership of that group.'));
            }

            if ($group['nbumax'] != 0) {
                if (($group['nbumax'] - $group['nbuser']) <= 0) {
                    return DataUtil::formatForDisplay($this->__('Sorry! That group has reached full membership.'));
                }
            }

            if ($group['state'] == Groups_Helper_Common::STATE_CLOSED) {
                return DataUtil::formatForDisplay($this->__('Sorry! That group is closed.'));
            }
        }

        $this->view->assign('mainpage',     false)
                   ->assign('gid',          $gid)
                   ->assign('gname',        $group['name'])
                   ->assign('gtype',        $group['gtype']) // Can't use type as it is a reserved word.
                   ->assign('action',       $action)
                   ->assign('description',  $group['description']);

        return $this->view->fetch('groups_user_membership.tpl');
    }

    /*
 * update a users group applications
 *
    */
    public function userupdate()
    {
        $this->checkCsrfToken();

        $gid = (int)FormUtil::getPassedValue('gid', null, 'POST');
        $action = FormUtil::getPassedValue('action', null, 'POST');
        $gtype = FormUtil::getPassedValue('gtype', null, 'POST');
        $tag = FormUtil::getPassedValue('tag', null, 'POST');

        if (empty($gid) || !is_numeric($gid) || empty($action)) {
            return LogUtil::registerArgsError();
        }

        if (empty($tag)) return DataUtil::formatForDisplay($this->__('Error! You must click on the checkbox to confirm your action.'));

        $applytext = '';
        if ($action == 'subscribe' && $gtype == Groups_Helper_Common::GTYPE_PRIVATE) {
            $applytext = FormUtil::getPassedValue('applytext', null, 'POST');
        }

        $result = ModUtil::apiFunc('Groups', 'user', 'userupdate',
                array('gid'       => $gid,
                'action'    => $action,
                'gtype'     => $gtype,
                'applytext' => $applytext));

        if ($result == true) {
            LogUtil::registerStatus($this->__('Done! Saved the action.'));
        }

        $this->view->clear_cache('groups_user_memberslist.tpl');

        $this->redirect(ModUtil::url('Groups', 'user', 'view'));
    }

    /**
     * display the membership of a group
     *
     */
    public function memberslist()
    {
        $gid = (int)FormUtil::getPassedValue('gid', null, 'GET');
        $startnum = (int)FormUtil::getPassedValue('startnum', 1, 'GET');

        if (!is_numeric($startnum)) {
            return LogUtil::registerArgsError();
        }

        $itemsperpage = $this->getVar('itemsperpage');

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Groups::memberslist', '::', ACCESS_OVERVIEW));

        $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid'      => $gid,
                'numitems' => $itemsperpage,
                'startnum' => $startnum));

        if (!$group) {
            return DataUtil::formatForDisplay($this->__('Error! Could not load data.'));
        }

        $uid = UserUtil::getVar('uid');

        $typelabel  = array();
        $statelabel = array();

        $groupsCommon = new Groups_Helper_Common();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        $group['typelbl']  = $typelabel[$group['gtype']];
        $group['statelbl'] = $statelabel[$group['state']];

        $this->view->assign('mainpage', false);

        $this->view->assign('group', $group);

        if ($group['members']) {
            $onlines = ModUtil::apiFunc('Groups', 'user', 'whosonline', array());
            $members = array();
            foreach ($group['members'] as $userid) {
                $userinfo = UserUtil::getVars($userid['uid']);

                $isonline = false;
                if (is_array($onlines)) {
                    foreach ($onlines as $online) {
                        if ($online['uid'] == $userid['uid']) {
                            $isonline = true;
                        }
                    }
                }

                if ($isonline) {
                    $userinfo['isonline']    = 'greenled.png';
                    $userinfo['isonlinelbl'] = $this->__('on-line');
                } else {
                    $userinfo['isonline']     = 'redled.png';
                    $userinfo['isonlinelbl'] = $this->__('off-line');
                }

                $members[] = $userinfo;
            }

            // test of sorting data
            if (!empty($members)) {
                foreach ($members as $res) {
                    $sortAarr[] = strtolower($res['uname']);
                }
                array_multisort($sortAarr, SORT_ASC, $members);
            }
            $this->view->assign('members', $members);
        } else {
            $this->view->assign('members', false);
        }

        if (UserUtil::isLoggedIn()) {
            $this->view->assign('ismember', ModUtil::apiFunc('Groups', 'user', 'isgroupmember', array('gid' => $gid, 'uid' => $uid)));
        } else {
            $this->view->assign('ismember', false);
        }

        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('Groups', 'user', 'countgroupmembers', array('gid' => $gid)),
                                           'itemsperpage' => $itemsperpage));

        $profileModule = System::getVar('profilemodule', '');
        $this->view->assign('useProfileModule', (!empty($profileModule) && $profileModule == 'Profile' && ModUtil::available($profileModule)));

        return $this->view->fetch('groups_user_memberslist.tpl');
    }
}
