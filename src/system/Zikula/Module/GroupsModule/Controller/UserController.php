<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\GroupsModule\Controller;

use ModUtil;
use SecurityUtil;
use UserUtil;
use Zikula_View;
use Zikula\Module\GroupsModule\Helper\CommonHelper;
use LogUtil;
use DataUtil;
use System;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * User controllers for the groups module
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * Groups Module main user function
     *
     * @return void
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
    }

    /**
     * Display items
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have overview access to the module
     */
    public function viewAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need.
        $startnum = (int)$this->request->query->get('startnum', null);

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

        if (UserUtil::isLoggedIn()) {
            $islogged = true;
        } else {
            $islogged = false;
        }

        // get groups (not core, only private and public ones)
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getallgroups',
                array('startnum' => $startnum,
                      'numitems' => $itemsperpage));

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('mainpage', true);

        // The return value of the function is checked here, and if the function
        // failed then an appropriate message is posted.
        if (!$groups) {
            $this->view->assign('nogroups', true);
            return $this->response($this->view->fetch('User/view.tpl'));
        }

        $groupitems = array();
        $typelabel  = array();
        $statelabel = array();

        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        foreach ($groups as $group) {

            if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_OVERVIEW)) {

                $group['typelbl']  = $typelabel[$group['gtype']];
                $group['statelbl'] = $statelabel[$group['state']];

                $this->view->assign($group);

                if ($islogged == true && SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_READ)) {
                    // The right to apply
                    $groupitems[] = $this->view->fetch('User/grouprow_read.tpl', $group['gid']);
                } else {
                    // No right to apply
                    $groupitems[] = $this->view->fetch('User/grouprow_overview.tpl', $group['gid']);
                }
            }
        }

        $this->view->assign('nogroups', false)
                   ->assign('items', $groupitems);

        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countitems'),
                                           'itemsperpage' => $itemsperpage));

        return $this->response($this->view->fetch('User/view.tpl'));
    }

    /**
     * display the membership of a public group
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the group isn't set or isn't numeric or
     *                                          if the action isn't one of subscribe|unsubscribe|cancel
     * @throws AccessDeniedException Thrown if the user isn't logged in or 
     *                                          if the user doesn't have overview access to the module
     * @throws NotFoundHttpException Thrown if the group cannot be found
     */
    public function membershipAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $gid = (int)$this->request->query->get('gid', null);
        $action = $this->request->query->get('action', null);

        if (empty($gid) || !is_numeric($gid) || empty($action)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if ($action != 'subscribe' && $action != 'unsubscribe' && $action != 'cancel') {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }

        $uid = UserUtil::getVar('uid');

        // Check if the group exists
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        if (!$group) {
            throw new NotFoundHttpException($this->__('Error! That group does not exist.'));
        }

        // And lastly, we must check if he didn't rewrote the url,
        // that is he applying to an open group and that the group is open
        // $isopen = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getginfo', array('gid' => $gid));
        if ($action == 'subscribe') {
            if (ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isgroupmember',array('gid' => $gid, 'uid' => $uid))) {
                LogUtil::registerError($this->__('Error! You are already a member of this group.'));
                return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
            }

            if ($group['gtype'] == CommonHelper::GTYPE_CORE) {
               LogUtil::registerError($this->__('Sorry! You cannot apply for membership of that group.'));
                return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
            }

            if ($group['nbumax'] != 0) {
                if (($group['nbumax'] - $group['nbuser']) <= 0) {
                    LogUtil::registerError($this->__('Sorry! That group has reached full membership.'));
                    return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
                }
            }

            if ($group['state'] == CommonHelper::STATE_CLOSED) {
                LogUtil::registerError($this->__('Sorry! That group is closed.'));
                return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
            }
        }

        $this->view->assign('mainpage',     false)
                   ->assign('gid',          $gid)
                   ->assign('gname',        $group['name'])
                   ->assign('gtype',        $group['gtype']) // Can't use type as it is a reserved word.
                   ->assign('action',       $action)
                   ->assign('description',  $group['description']);

        return $this->response($this->view->fetch('User/membership.tpl'));
    }

    /**
     * update a users group applications
     *
     * @return void
     *
     * @throws \InvalidArgumentsException Thrown if the group id isn't set or isn't numeric or
     *                                           if no action is requested
     */
    public function userupdateAction()
    {
        $this->checkCsrfToken();

        $gid = (int)$this->request->request->get('gid', null);
        $action = $this->request->request->get('action', null);
        $gtype = $this->request->request->get('gtype', null);
        $tag = $this->request->request->get('tag', null);

        if (empty($gid) || !is_numeric($gid) || empty($action)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (empty($tag)) {
            LogUtil::registerError($this->__('Error! You must click on the checkbox to confirm your action.'));
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
        }

        $applytext = '';
        if ($action == 'subscribe' && $gtype == CommonHelper::GTYPE_PRIVATE) {
            $applytext = $this->request->request->get('applytext', null);
        }

        $result = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'userupdate',
                array('gid'       => $gid,
                      'action'    => $action,
                      'gtype'     => $gtype,
                      'applytext' => $applytext));

        if ($result == true) {
            LogUtil::registerStatus($this->__('Done! Saved the action.'));
        }

        $this->view->clear_cache('User/memberslist.tpl');

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'view')));
    }

    /**
     * display the membership of a group
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the startnum parameter isn't numeric
     * @throws AccessDeniedException Thrown if the user doesn't have overview access to the memberslist component of the module
     */
    public function memberslistAction()
    {
        $gid = (int)$this->request->query->get('gid', null);
        $startnum = (int)$this->request->query->get('startnum', 0);

        if (!is_numeric($startnum)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $itemsperpage = $this->getVar('itemsperpage');

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::memberslist', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid,
                'numitems' => $itemsperpage,
                'startnum' => $startnum));

        if (!$group) {
            return DataUtil::formatForDisplay($this->__('Error! Could not load data.'));
        }

        $uid = UserUtil::getVar('uid');

        $typelabel  = array();
        $statelabel = array();

        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        $group['typelbl']  = $typelabel[$group['gtype']];
        $group['statelbl'] = $statelabel[$group['state']];

        $this->view->assign('mainpage', false);

        $this->view->assign('group', $group);

        if ($group['members']) {
            $onlines = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'whosonline');

            $members = array();
            foreach ($group['members'] as $userid) {
                $userinfo = UserUtil::getVars($userid['uid']);

                $userinfo['isonline'] = false;
                if (is_array($onlines)) {
                    foreach ($onlines as $online) {
                        if ($online['uid'] == $userid['uid']) {
                            $userinfo['isonline'] = true;
                            break;
                        }
                    }
                }
                $members[] = $userinfo;
            }

            // test of sorting data
            if (!empty($members)) {
                $sortAarr = array();
                foreach($members as $res) {
                    $sortAarr[] = strtolower($res['uname']);
                }
                array_multisort($sortAarr, SORT_ASC, $members);
            }
            $this->view->assign('members', $members);
        } else {
            $this->view->assign('members', false);
        }

        if (UserUtil::isLoggedIn()) {
            $this->view->assign('ismember', ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isgroupmember', array('gid' => $gid, 'uid' => $uid)));
        } else {
            $this->view->assign('ismember', false);
        }

        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', array('gid' => $gid)),
                                           'itemsperpage' => $itemsperpage));

        return $this->response($this->view->fetch('User/memberslist.tpl'));
    }
}
