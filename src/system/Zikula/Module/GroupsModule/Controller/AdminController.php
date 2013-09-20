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

namespace Zikula\Module\GroupsModule\Controller;

use Zikula_View;
use ModUtil;
use SecurityUtil;
use LogUtil;
use FormUtil;
use Zikula\Module\GroupsModule\Helper\CommonHelper;
use UserUtil;
use Users_Constant;
use System;

class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * Groups Module main administration function
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     *
     * @return string HTML output string
     */
    public function mainAction()
    {
        // Security check
        $any_access = false;

        // get all groups from the API
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
        if (is_array($groups)) {
            foreach ($groups as $group) {
                if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'] . '::', ACCESS_EDIT)) {
                    $any_access = true;
                    break;
                }
            }
        }

        if (!$any_access) {
            // we found no groups that we are allowed to administer
            // return now
            return LogUtil::registerPermissionError();
        }

        // Return the output that has been generated by this function
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
    }

    /**
     * View all groups.
     *
     * This function creates a tabular output of all group items in the module.
     *
     * @return string HTML output string
     */
    public function viewAction()
    {
        // Get parameters from whatever input we need.
        $startnum = (int)$this->request->request->get('startnum', null);

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

        // get the default user group
        $defaultgroup = $this->getVar('defaultgroup');

        // get the primary admin group
        $primaryadmingroup = $this->getVar('primaryadmingroup', 2);

        // The user API function is called.
        $items = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall',
                array('startnum' => $startnum,
                      'numitems' => $itemsperpage));

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $typelabel = $groupsCommon->gtypeLabels();
        $statelabel = $groupsCommon->stateLabels();

        $groups = array();

        foreach ($items as $item) {

            if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_READ)) {

                // Options for the item.
                $options = array();
                if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_EDIT)) {

                    $editurl    = ModUtil::url('ZikulaGroupsModule', 'admin', 'modify', array('gid'     => $item['gid']));
                    $membersurl = ModUtil::url('ZikulaGroupsModule', 'admin', 'groupmembership', array('gid'     => $item['gid']));

                    $options[] = array('url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'modify', array('gid'     => $item['gid'])),
                            'title'   => $this->__('Edit'),
                            'imgfile' => 'xedit.png');

                    if ((SecurityUtil::checkPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_DELETE))
                            && ($item['gid'] != $defaultgroup) && ($item['gid'] != $primaryadmingroup)) {
                        $options[] = array('url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'delete', array('gid'     => $item['gid'])),
                                'title'   => $this->__('Delete'),
                                'imgfile' => '14_layer_deletelayer.png');
                    }

                    $options[] = array('url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'groupmembership', array('gid'     => $item['gid'])),
                            'title'   => $this->__('Group membership'),
                            'imgfile' => 'agt_family.png');

                    $nbuser = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', array('gid' => $item['gid']));

                    $groups[] = array(
                        'name' => $item['name'],
                        'gid'         => $item['gid'],
                        'gtype'       => $item['gtype'],
                        'gtypelbl'    => $typelabel[$item['gtype']],
                        'description' => ((empty($item['description'])== false) ? $item['description'] : ''),
                        'prefix'      => $item['prefix'],
                        'state'       => $item['state'],
                        'statelbl'    => $statelabel[$item['state']],
                        'nbuser'      => (($nbuser <> false) ? $nbuser : 0),
                        'nbumax'      => $item['nbumax'],
                        'link'        => $item['link'],
                        'uidmaster'   => $item['uidmaster'],
                        'options'     => $options,
                        'editurl'     => $editurl,
                        'membersurl'  => $membersurl);
                }
            }
        }

        if (count($groups) == 0) {
            // groups array is empty
            return LogUtil::registerPermissionError();
        }

        // The admin API function is called. This fetch the pending applications if any.
        // permission check for the group is done in this function
        $users = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getapplications',
                array('startnum' => $startnum,
                      'numitems' => $itemsperpage));

        $this->view->assign('groups',       $groups)
                   ->assign('grouptypes',   $typelabel)
                   ->assign('states',       $statelabel)
                   ->assign('useritems',    $users)
                   ->assign('defaultgroup', $defaultgroup)
                   ->assign('primaryadmingroup', $primaryadmingroup);

        // Assign the values for the smarty plugin to produce a pager
        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'countitems'),
                                           'itemsperpage' => $itemsperpage));

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * Add a new group.
     *
     * This is a standard function that is called whenever an administrator
     * wishes to create a new group.
     *
     * @return string HTML output string.
     */
    public function newgroupAction()
    {
        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADD));

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $grouptype = $groupsCommon->gtypeLabels();
        $groupstate = $groupsCommon->stateLabels();

        $this->view->assign('grouptype',  $grouptype)
                   ->assign('groupstate', $groupstate);

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('Admin/new.tpl'));
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by groups admin_new() to create a new group.
     *
     * @param string 'name' the name of the group to be created.
     *
     * @return bool true If group created succesfully, false otherwise.
     */
    public function createAction(array $args = array())
    {
        $this->checkCsrfToken();

        // Get parameters from whatever input we need.
        $name = $this->request->request->get('name', isset($args['name']) ? $args['name'] : null);
        $gtype = $this->request->request->get('gtype', isset($args['gtype']) ? $args['gtype'] : null);
        $state = $this->request->request->get('state', isset($args['state']) ? $args['state'] : null);
        $nbumax = $this->request->request->get('nbumax', isset($args['nbumax']) ? $args['nbumax'] : null);
        $description = $this->request->request->get('description', isset($args['description']) ? $args['description'] : null);

        // The API function is called.
        $check = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getgidbyname', array());

        if ($check != false) {
            // Group already exists
            LogUtil::registerError($this->__('Error! There is already a group with that name.'));
        } else {
            $gid = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'create',
                    array('name'        => $name,
                          'gtype'       => $gtype,
                          'state'       => $state,
                          'nbumax'      => $nbumax,
                          'description' => $description));

            // The return value of the function is checked here
            if ($gid != false) {
                // Success
                LogUtil::registerStatus($this->__('Done! Created the group.'));
            }
        }

        // This function generated no output
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
    }

    /**
     * Modify a group.
     *
     * This is a standard function that is called whenever an administrator
     * wishes to modify a current group item.
     *
     * @param int 'gid' the id of the group to be modified.
     * @param int 'objectid' generic object id mapped onto gid if present.
     *
     * @return string HTML output string.
     */
    public function modifyAction(array $args = array())
    {
        // Get parameters from whatever input we need.
        $gid = (int)$this->request->query->get('gid', isset($args['gid']) ? $args['gid'] : null);

        // get group
        $item = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        if (!$item) {
            return LogUtil::registerError($this->__('Sorry! No such group found.'), 404);
        }

        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_EDIT));

        // assign the item
        $this->view->assign('item', $item);

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $grouptype = $groupsCommon->gtypeLabels();
        $groupstate = $groupsCommon->stateLabels();

        $this->view->assign('grouptype',  $grouptype)
                   ->assign('groupstate', $groupstate);

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('Admin/modify.tpl'));
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by Admin/modify() to update a current group item.
     *
     * @param int 'gid' the id of the group to be modified.
     * @param int 'objectid' generic object id mapped onto gid if present.
     * @param string 'name' the name of the group to be updated.
     *
     * @return bool true If group updated successfully, false otherwise.
     */
    public function updateAction(array $args = array())
    {
        $this->checkCsrfToken();

        // Get parameters from whatever input we need.
        $gid = (int)$this->request->request->get('gid', isset($args['gid']) ? $args['gid'] : null);
        $name = $this->request->request->get('name', isset($args['name']) ? $args['name'] : null);
        $gtype = $this->request->request->get('gtype', isset($args['gtype']) ? $args['gtype'] : null);
        $state = $this->request->request->get('state', isset($args['state']) ? $args['state'] : null);
        $nbumax = $this->request->request->get('nbumax', isset($args['nbumax']) ? $args['nbumax'] : null);
        $description = $this->request->request->get('description', isset($args['description']) ? $args['description'] : null);

        // The API function is called.
        if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'update',
                array('gid' => $gid,
                      'name'        => $name,
                      'gtype'       => $gtype,
                      'state'       => $state,
                      'nbumax'      => $nbumax,
                      'description' => $description))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Saved group changes.'));
        }

        // This function generated no output
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
    }

    /**
     * Delete group.
     *
     * This is a standard function that is called whenever an administrator
     * wishes to delete a current group item.  Note that this function is
     * the equivalent of both of the modify() and update() functions above as
     * it both creates a form and processes its output.  This is fine for
     * simpler functions, but for more complex operations such as creation and
     * modification it is generally easier to separate them into separate
     * functions.  There is no requirement in the Zikula MDG to do one or the
     * other, so either or both can be used as seen appropriate by the module
     * developer.
     *
     * @param int 'gid' the id of the item to be deleted.
     * @param bool 'confirmation' confirmation that this item can be deleted.
     * @param int 'objectid' generic object id mapped onto gid if present.
     *
     * @return mixed HTML output string if no confirmation, true if group deleted succesfully, false otherwise.
     */
    public function deleteAction(array $args = array())
    {
        // Get parameters from whatever input we need.
        $gid = (int)$this->request->query->get('gid', isset($args['gid']) ? $args['gid'] : null);
        $confirmation = (bool)$this->request->request->get('confirmation', isset($args['confirmation']) ? $args['confirmation'] : null);

        // The user API function is called.
        $item = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        if (!$item) {
            LogUtil::registerError($this->__('Sorry! No such group found.'));
            return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_DELETE));

        // get the user default group - we do not allow its deletion
        $defaultgroup = $this->getVar('defaultgroup');
        if ($item['gid'] == $defaultgroup) {
            LogUtil::registerError($this->__('Error! You cannot delete the default user group.'));
            return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        // Check for confirmation.
        if (empty($confirmation)) {

            // No confirmation yet - display a suitable form to obtain confirmation
            // of this action from the user

            // Add a hidden variable for the item id.
            $this->view->assign('item', $item);

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('Admin/delete.tpl'));
        }

        // If we get here it means that the user has confirmed the action

        $this->checkCsrfToken();

        // The API function is called.
        if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'delete', array('gid' => $gid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted the group.'));
        }

        // This function generated no output
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
    }

    /**
     * This is a standard function to display members of a group.
     *
     * @param int 'gid' the id of the group to list membership for.
     * @param int 'objectid' generic object id mapped onto gid if present.
     *
     * @return string HTML output string.
     */
    public function groupmembershipAction(array $args = array())
    {
        // Get parameters from whatever input we need.
        $gid = (int)$this->request->query->get('gid', isset($args['gid']) ? $args['gid'] : null);
        $startnum = (int)$this->request->query->get('startnum', isset($args['startnum']) ? $args['startnum'] : null);
        $letter = $this->request->query->get('letter', isset($args['letter']) ? $args['letter'] : null);

        // The user API function is called.
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get',
                array('gid'      => $gid,
                      'startnum' => $startnum,
                      'numitems' => $this->getVar('itemsperpage')));

        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_EDIT));

        // assign the group to the template
        $this->view->assign('group', $group);

        $users = $group['members'];

        $currentUid = UserUtil::getVar('uid');
        $defaultGroup = $this->getVar('defaultgroup', 0);
        $primaryAdminGroup = $this->getVar('primaryadmingroup', 0);

        $groupmembers = array();

        if (is_array($users) && SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_EDIT)) {
            foreach ($users as $user) {
                $options = array();

                if (($user['uid'] == $currentUid)
                    && (($group['gid'] == $defaultGroup) || ($group['gid'] == $primaryAdminGroup))) {
                    $options[] = array();
                } else {
                    $options[] = array(
                        'url'     => ModUtil::url('ZikulaGroupsModule', 'admin', 'removeuser', array('gid' => $group['gid'], 'uid' => $user['uid'])),
                        'imgfile' => 'editdelete.png',
                        'uid'     => $user['uid'],
                        'title'   => $this->__('Remove user from group')
                    );
                }

                $groupmembers[] = array(
                    'uname'   => UserUtil::getVar('uname', $user['uid']),
                    'name'    => UserUtil::getVar('name', $user['uid']),
                    'email'    => UserUtil::getVar('email', $user['uid']),
                    'uid'     => $user['uid'],
                    'options' => $options
                );
            }
        }

        // sort alphabetically.
        $sortAarr = array();
        foreach ($groupmembers as $res) {
            $sortAarr[] = strtolower($res['uname']);
        }
        array_multisort($sortAarr, SORT_ASC, $groupmembers);

        $this->view->assign('groupmembers', $groupmembers);

        // check for a letter parameter
        if (empty($letter) && strlen($letter) != 1) {
            $letter = '*';
        }

        switch ($letter) {
            case '*':
                // read allusers
                $field = '';
                $expression = '';
                break;

            default:
                $field = 'uname';
                $expression = $letter . '%';
        }

        $users = UserUtil::getAll('uname', 'ASC', null, null, '', $field, $expression);

        $allusers = array();
        foreach ($users as $user) {
            if ($user['uid'] == 0 || strtolower($user['uname']) == 'anonymous' || strtolower($user['uname']) == 'guest'
                    || $user['uname'] == $this->getVar(Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME)
                    ) {
                continue;
            }
            $alias = '';
            if (!empty($user['name'])) {
                $alias = ' (' . $user['name'] . ')';
            }
            $allusers[$user['uid']] = $user['uname'] . $alias;
        }

        // Now lets remove the users that are currently part of the group
        // flip the array so we have the user id's as the key
        // this makes the array the same is the group members array
        // from the get function
        $flippedusers = array_flip($allusers);
        // now lets diff the array
        $diffedusers = array_diff($flippedusers, array_keys($group['members']));
        // now flip the array back
        $allusers = array_flip($diffedusers);
        // sort the users by user name
        natcasesort($allusers);

        // assign the users not in the group to the template
        $this->view->assign('uids', $allusers);

        // Assign the values for the smarty plugin to produce a pager
        $this->view->assign('pager', array('numitems'     => ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', array('gid' => $gid)),
                                           'itemsperpage' => $this->getVar( 'itemsperpage')));

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('Admin/groupmembership.tpl'));
    }

    /**
     * This is a standard function to add a user to a group.
     *
     * @param int 'gid' The id of the group.
     * @param mixed 'uid' The id of the user (int) or an array of userids.
     *
     * @return boolean True is user added succesfully, false otherwise.
     */
    public function adduserAction(array $args = array())
    {
        $this->checkCsrfToken();

        // Get parameters from whatever input we need.
        $gid = (int)$this->request->request->get('gid', isset($args['gid']) ? $args['gid'] : null);
        $uid = $this->request->request->get('uid', isset($args['uid']) ? $args['uid'] : null);

        // The API function is called.
        if (is_array($uid)) {
            $total_users_added = 0;
            $total_users_notadded = 0;

            foreach($uid as $id) {
                if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'adduser', array('gid' => $gid, 'uid' => $id))) {
                    $total_users_notadded++;
                } else {
                    $total_users_added++;
                }
            }

            if ($total_users_added > 0) {
                LogUtil::registerStatus($this->_fn('Done! %s user was added to the group.', 'Done! %s users were added to the group.', $total_users_added, $total_users_added));
            }
            if ($total_users_notadded > 0) {
                LogUtil::registerError($this->_fn('Error! %s user was not added to the group.', 'Error! %s users were not added to the group.', $total_users_notadded, $total_users_notadded));
            }
        } else {
            if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'adduser', array('gid' => $gid, 'uid' => $uid))) {
                LogUtil::registerError($this->__('Error! A problem occurred and the user was not added to the group.'));
            } else {

                LogUtil::registerStatus($this->__('Done! The user was added to the group.'));
            }
        }

        // This function generated no output
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'groupmembership', array('gid' => $gid)));
    }

    /**
     * This is a standard function to add a user to a group.
     *
     * @param int 'gid' the id of the group.
     * @param int 'uid' the id of the user.
     *
     * @return boolean true is user added succesfully, false otherwise.
     */
    public function removeuserAction(array $args = array())
    {
        // Get parameters from whatever input we need.
        $gid = (int)$this->request->query->get('gid', isset($args['gid']) ? $args['gid'] : null);
        $uid = (int)$this->request->query->get('uid', isset($args['uid']) ? $args['uid'] : null);
        $confirmation = (bool)$this->request->request->get('confirmation', isset($args['confirmation']) ? $args['confirmation'] : null);
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid.'::', ACCESS_EDIT));

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet - display a suitable form to obtain confirmation
            // of this action from the user

            $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

            // Add a hidden variable for the item id.
            $this->view->assign('gid', $gid)
                       ->assign('uid', $uid)
                       ->assign('group', $group)
                       ->assign('uname', UserUtil::getVar('uname', $uid));

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('Admin/removeuser.tpl'));
        }

        $this->checkCsrfToken();

        // The API function is called.
        if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'removeuser', array('gid' => $gid, 'uid' => $uid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! The user was removed from the group.'));
        } else {
            LogUtil::registerError($this->__('Error! A problem occurred while attempting to remove the user. The user has not been removed from the group.'));
        }

        // This function generated no output
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'groupmembership', array('gid' => $gid)));
    }

    /**
     * display a list of group applications
     *
     */
    public function userpendingAction()
    {
        $gid = (int)$this->request->query->get('gid', null);
        $userid = (int)$this->request->query->get('userid', null);
        $action = $this->request->query->get('action', null);

        if (empty($gid) || empty($userid)) {
            return LogUtil::registerArgsError(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        if ($action != 'deny' && $action != 'accept') {
            return LogUtil::registerArgsError(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        $appinfo = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getapplicationinfo', array('gid' => $gid, 'userid' => $userid));

        $sendoptions = array(
            0 => $this->__('None'),
            1 => $this->__('E-mail')
        );

        $this->view->assign('userid',      $userid)
                   ->assign('gid',         $gid)
                   ->assign('action',      $action)
                   ->assign('sendoptions', $sendoptions)
                   ->assign('application', $appinfo['application']);

        return $this->response($this->view->fetch('Admin/userpending.tpl'));
    }

    /**
     * update group applications
     *
     */
    public function userupdateAction()
    {
        $this->checkCsrfToken();

        $action = $this->request->request->get('action', null);

        if ($action != 'deny' && $action != 'accept') {
            return LogUtil::registerArgsError(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        $tag = $this->request->request->get('tag', null);
        $sendtag = $this->request->request->get('sendtag', null);
        $reason = $this->request->request->get('reason', null);
        $gid = (int)$this->request->request->get('gid', null);
        $userid = (int)$this->request->request->get('userid', null);

        if (empty($tag) || empty($gid) || empty($userid)) {
            return LogUtil::registerArgsError(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        if ($action == 'deny') {

            $reasontitle = $this->__f('Concerning your %s group membership application', $group['name']);

            if (empty($reason)) {
                // Get Default TEXT
                $reason = $this->__('Sorry! This is a message to inform you with regret that your application for membership of the aforementioned private group has been rejected.');
            }

        } elseif ($action == 'accept') {

            $reasontitle = $this->__f('Done! The user has been added to the %s group.', $group['name']);

            if (empty($reason)) {
                // Get Default TEXT
                $reason = $this->__('Done! Your application has been accepted. You have been granted all the privileges assigned to the group of which you are now member.');
            }

        }

        $result = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'pendingaction',
                array('userid'      => $userid,
                      'gid'         => $gid,
                      'sendtag'     => $sendtag,
                      'reason'      => $reason,
                      'reasontitle' => $reasontitle,
                      'action'      => $action));

        if (!$result) {
            if ($action == 'deny') {
                LogUtil::registerError($this->__("Error! Could not execute 'Reject' action."));
            } else {
                LogUtil::registerError($this->__("Error! Could not execute 'Accept' action."));
            }
            return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
        }

        if ($action == 'accept') {
            LogUtil::registerStatus($this->__('Done! The user was added to the group.'));
        } else {
            LogUtil::registerStatus($this->__("Done! The user's application for group membership has been rejected."));
        }

        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
    }

    /**
     * This is a standard function to modify the configuration parameters of the module.
     *
     * @return string HTML string
     */
    public function modifyconfigAction()
    {
        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADMIN));

        // get all groups from the API
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');

        // build an array suitable for html_options
        $groupslist = array();
        foreach ($groups as $group) {
            $groupslist[$group['gid']] = $group['name'];
        }

        // assign the list of existing groups
        $this->view->assign('groups', $groupslist);

        // Return the output that has been generated by this function
        return $this->response($this->view->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form.
     *
     * @return boolean True.
     */
    public function updateconfigAction()
    {
        $this->checkCsrfToken();

        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADMIN));

        // Update module variables.
        $itemsperpage = (int)$this->request->request->get('itemsperpage', 25);
        $this->setVar('itemsperpage', $itemsperpage);

        $defaultgroup = (int)$this->request->request->get('defaultgroup', 1);
        $this->setVar('defaultgroup', $defaultgroup);

        $mailwarning = (bool)$this->request->request->get('mailwarning', false);
        $this->setVar('mailwarning', $mailwarning);

        $hideclosed = (bool)$this->request->request->get('hideclosed', false);
        $this->setVar('hideclosed', $hideclosed);

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        // This function generated no output
        return $this->redirect(ModUtil::url('ZikulaGroupsModule', 'admin', 'view'));
    }
}
