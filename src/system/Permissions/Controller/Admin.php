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

/**
 * Permissions_Controller_Admin class.
 */
class Permissions_Controller_Admin extends Zikula_AbstractController
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
     * Main administration function.
     *
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function).
     *
     * @return mixed HTML string or true
     */
    public function main()
    {
        // Security check will be done in view()
        $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * view permissions
     * @return string HTML string
     */
    public function view()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        $permgrp = FormUtil::getPassedValue('permgrp', -1, 'REQUEST');
        $testuser = FormUtil::getPassedValue('test_user', null, 'POST');
        $testcomponent = FormUtil::getPassedValue('test_component', null, 'POST');
        $testinstance = FormUtil::getPassedValue('test_instance', null, 'POST');
        $testlevel = FormUtil::getPassedValue('test_level', null, 'POST');

        $testresult = '';
        if (!empty($testuser) &&
                !empty($testcomponent) &&
                !empty($testinstance)
        ) {
            // we have everything we need for an effective permission check
            $testuid = UserUtil::getIdFromName($testuser);
            if ($testuid <> false) {
                if (SecurityUtil::checkPermission($testcomponent, $testinstance, $testlevel, $testuid)) {
                    $testresult = '<span id="permissiontestinfogreen">' . $this->__('permission granted.') . '</span>';
                } else {
                    $testresult = '<span id="permissiontestinfored">' . $this->__('permission not granted.') . '</span>';
                }
            } else {
                $testresult = '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
            }
        }

        $this->view->assign('testuser', $testuser)
                ->assign('testcomponent', $testcomponent)
                ->assign('testinstance', $testinstance)
                ->assign('testlevel', $testlevel)
                ->assign('testresult', $testresult);

        // decide the default view
        $enableFilter = $this->getVar('filter', 1);
        $rowview = $this->getVar('rowview', 25);

        // Work out which tables to operate against, and
        // various other bits and pieces
        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];
        $ids = $this->getGroupsInfo();

        $where = '';
        if ($enableFilter == 1) {
            $permgrpparts = explode('+', $permgrp);
            if ($permgrpparts[0] == 'g') {
                if (is_array($permgrpparts) && $permgrpparts[1] != SecurityUtil::PERMS_ALL) {
                    $where = "WHERE (" . $permcolumn['gid'] . "='" . SecurityUtil::PERMS_ALL . "' OR " . $permcolumn['gid'] . "='" . DataUtil::formatForStore($permgrpparts[1]) . "')";
                    $permgrp = $permgrpparts[1];
                    $this->view->assign('filtertype', 'group');
                } else {
                    $permgrp = SecurityUtil::PERMS_ALL;
                    $where = '';
                }
            } elseif ($permgrpparts[0] == 'c') {
                if (is_array($permgrpparts) && $permgrpparts[1] != SecurityUtil::PERMS_ALL) {
                    $where = "WHERE (" . $permcolumn['component'] . "='.*' OR " . $permcolumn['component'] . " LIKE '" . DataUtil::formatForStore($permgrpparts[1]) . "%')";
                    $permgrp = $permgrpparts[1];
                    $this->view->assign('filtertype', 'component');
                } else {
                    $permgrp = SecurityUtil::PERMS_ALL;
                    $where = '';
                }
            } else {
                $this->view->assign('filtertype', '');
            }
            $this->view->assign('permgrps', $ids);
            $this->view->assign('permgrp', $permgrp);
            $this->view->assign('enablefilter', true);
        } else {
            $this->view->assign('enablefilter', false);
            $this->view->assign('filtertype', '');
            $this->view->assign('permgrp', SecurityUtil::PERMS_ALL);
        }

        $accesslevels = SecurityUtil::accesslevelnames();

        $orderBy = "ORDER BY $permcolumn[sequence]";
        $objArray = DBUtil::selectObjectArray('group_perms', $where, $orderBy, -1, -1, false);
        $numrows = DBUtil::_getFetchedObjectCount();

        $permissions = array();
        $components = array(-1 => $this->__('All components'));
        if ($numrows > 0) {
            $csrftoken = SecurityUtil::generateCsrfToken($this->serviceManager, true);
            $rownum = 1;
            $ak = array_keys($objArray);
            foreach ($ak as $v) {
                $obj = $objArray[$v];
                $id = $obj['gid'];
                $up = array('url' => ModUtil::url('Permissions', 'admin', 'inc',
                                array('pid' => $obj['pid'],
                                        'permgrp' => $permgrp,
                                        'csrftoken' => $csrftoken)),
                        'title' => $this->__('Up'));
                $down = array('url' => ModUtil::url('Permissions', 'admin', 'dec',
                                array('pid' => $obj['pid'],
                                        'permgrp' => $permgrp,
                                        'csrftoken' => $csrftoken)),
                        'title' => $this->__('Down'));
                switch ($rownum) {
                    case 1:
                        $arrows = array('up' => 0, 'down' => 1);
                        break;
                    case $numrows:
                        $arrows = array('up' => 1, 'down' => 0);
                        break;
                    default:
                        $arrows = array('up' => 1, 'down' => 1);
                        break;
                }
                $rownum++;

                $options = array();
                $inserturl = ModUtil::url('Permissions', 'admin', 'listedit',
                                array('permgrp' => $permgrp,
                                        'action' => 'insert',
                                        'insseq' => $obj['sequence']));
                $editurl = ModUtil::url('Permissions', 'admin', 'listedit',
                                array('chgpid' => $obj['pid'],
                                        'permgrp' => $permgrp,
                                        'action' => 'modify'));
                $deleteurl = ModUtil::url('Permissions', 'admin', 'delete',
                                array('pid' => $obj['pid'],
                                        'permgrp' => $permgrp));

                $permissions[] = array('sequence' => $obj['sequence'],
                        'arrows' => $arrows,
                        // Realms not currently functional so hide the output - jgm
                        //'realms'    => $realms[$realm],
                        'group' => $ids[$id],
                        'groupid' => $id,
                        'component' => $obj['component'],
                        'instance' => $obj['instance'],
                        'accesslevel' => $accesslevels[$obj['level']],
                        'accesslevelid' => $obj['level'],
                        'options' => $options,
                        'up' => $up,
                        'down' => $down,
                        'permid' => $obj['pid'],
                        'inserturl' => $inserturl,
                        'editurl' => $editurl,
                        'deleteurl' => $deleteurl);
            }
        }

        // read all perms to extract components
        $allPerms = DBUtil::selectObjectArray('group_perms', '', $orderBy, -1, -1, false);
        foreach ($allPerms as $singlePerm) {
            // extract components, we keep everything up to the first colon
            $compparts = explode(':', $singlePerm['component']);
            $components[$compparts[0]] = $compparts[0];
        }

        $this->view->assign('groups', $this->getGroupsInfo());
        $this->view->assign('permissions', $permissions);
        $this->view->assign('components', $components);

        $lockadmin = ($this->getVar('lockadmin')) ? 1 : 0;
        $this->view->assign('lockadmin', $lockadmin);
        $this->view->assign('adminid', $this->getVar('adminid'));

        // Assign the permission levels
        $this->view->assign('permissionlevels', SecurityUtil::accesslevelnames());

        return $this->view->fetch('permissions_admin_view.tpl');
    }

    /**
     * Increment a permission.
     *
     * @param int 'pid' permissions id
     *
     * @return boolean true
     */
    public function inc()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // MMaes,2003-06-23: Added sec.check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        // MMaes,2003-06-23: View permissions applying to single group; added permgrp
        $pid = FormUtil::getPassedValue('pid', null, 'GET');
        $permgrp = FormUtil::getPassedValue('permgrp', null, 'GET');

        if (empty($permgrp)) {
            // For group-permissions, make sure we return something sensible.
            // Doesn't matter if we're looking at user-permissions...
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'inc',
                        array('pid' => $pid,
                                'permgrp' => $permgrp))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Incremented permission rule.'));
        }

        // Redirect
        $this->redirect(ModUtil::url('Permissions', 'admin', 'view',
                        array('permgrp' => $permgrp)));
    }

    /**
     * Decrement a permission.
     *
     * @param int 'pid' permissions id.
     *
     * @return boolean true
     */
    public function dec()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // MMaes,2003-06-23: Added sec.check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        // MMaes,2003-06-23: View permissions applying to single group; added permgrp
        $pid = FormUtil::getPassedValue('pid', null, 'GET');
        $permgrp = FormUtil::getPassedValue('permgrp', null, 'GET');

        if (!isset($permgrp) || $permgrp == '') {
            // For group-permissions, make sure we return something sensible.
            // This doesn't matter if we're looking at user-permissions...
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'dec',
                        array('pid' => $pid,
                                'permgrp' => $permgrp))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Decremented permission rule.'));
        }

        // Redirect
        $this->redirect(ModUtil::url('Permissions', 'admin', 'view',
                        array('permgrp' => $permgrp)));
    }

    /**
     * Edit / Create permissions in the mainview.
     *
     * @return boolean
     */
    public function listedit()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        $chgpid = FormUtil::getPassedValue('chgpid', null, 'GET');
        $action = FormUtil::getPassedValue('action', null, 'GET');
        $insseq = FormUtil::getPassedValue('insseq', null, 'GET');
        $permgrp = FormUtil::getPassedValue('permgrp', null, 'REQUEST');

        // decide default view
        $rowview = is_null($this->getVar('rowview')) ? '25' : $this->getVar('rowview');

        // Assign the permission levels
        $this->view->assign('permissionlevels', SecurityUtil::accesslevelnames());

        // Work out which tables to operate against, and
        // various other bits and pieces
        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];
        $mlpermtype = $this->__('Group');
        $viewperms = ($action == 'modify') ? $this->__('Modify permission rule') : $this->__('Create new permission rule');
        $ids = $this->getGroupsInfo();

        $orderBy = "ORDER BY $permcolumn[sequence]";
        $objArray = DBUtil::selectObjectArray('group_perms', '', $orderBy);
        if (!$objArray && $action != 'add') {
            LogUtil::registerError($this->__('Error! No permission rules of this kind were found. Please add some first.'));
            $this->redirect(ModUtil::url('modules', 'admin', 'view'));
        }

        $this->view->assign('title', $viewperms)
                ->assign('mlpermtype', $mlpermtype);

        $accesslevels = SecurityUtil::accesslevelnames();
        $numrows = count($objArray);

        $this->view->assign('idvalues', $ids);

        if ($action == 'modify') {
            // Form-start
            $this->view->assign('formurl', ModUtil::url('Permissions', 'admin', 'update'))
                    ->assign('permgrp', $permgrp)
                    ->assign('chgpid', $chgpid);

            // Realms hard-code4d - jgm
            $this->view->assign('realm', 0)
                    ->assign('insseq', $chgpid)
                    ->assign('submit', $this->__('Edit permission rule'));
        } elseif ($action == 'insert') {
            $this->view->assign('formurl', ModUtil::url('Permissions', 'admin', 'create'))
                    ->assign('permgrp', $permgrp)
                    ->assign('insseq', $insseq);

            // Realms hard-coded - jgm
            $this->view->assign('realm', 0)
                    ->assign('submit', $this->__('Create new permission rule'));
        } elseif ($action == 'add') {
            // Form-start
            $this->view->assign('formurl', ModUtil::url('Permissions', 'admin', 'create'))
                    ->assign('permgrp', $permgrp)
                    ->assign('insseq', -1);

            // Realms hard-coded - jgm
            $this->view->assign('realm', 0)
                    ->assign('submit', $this->__('Create new permission rule'));
        }

        $this->view->assign('action', $action);

        $permissions = array();
        $ak = array_keys($objArray);
        foreach ($ak as $v) {
            $obj = & $objArray[$v];
            $id = $obj['gid']; //get's uid or gid accordingly
            $permissions[] = array(// Realms not currently functional so hide the output - jgm
                    //'realms' => $realms[$realm],
                    'pid' => $obj['pid'],
                    'group' => $ids[$id],
                    'component' => $obj['component'],
                    'instance' => $obj['instance'],
                    'accesslevel' => $accesslevels[$obj['level']],
                    'level' => $obj['level'],
                    'sequence' => $obj['sequence']);
            if ($action == 'modify' && $obj['pid'] == $chgpid) {
                $this->view->assign('selectedid', $id);
            }
        }
        $this->view->assign('permissions', $permissions);

        return $this->view->fetch('permissions_admin_listedit.tpl');
    }

    /**
     * Update.
     *
     * @param int 'pid' permissions id.
     * @param int 'id' group or user id.
     * @param int 'realm' realm to which the permission belongs.
     * @param string 'component' component string.
     * @param string 'instance' instance string.
     * @param int 'level' permission level.
     *
     * @return boolean true.
     */
    public function update()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $pid = FormUtil::getPassedValue('pid', null, 'POST');
        $seq = FormUtil::getPassedValue('seq', null, 'POST');
        $oldseq = FormUtil::getPassedValue('oldseq', null, 'POST');
        $realm = FormUtil::getPassedValue('realm', null, 'POST');
        $id = FormUtil::getPassedValue('id', null, 'POST');
        $component = FormUtil::getPassedValue('component', null, 'POST');
        $instance = FormUtil::getPassedValue('instance', null, 'POST');
        $level = FormUtil::getPassedValue('level', null, 'POST');

        // Since we're using TextAreas, make sure no carriage-returns etc get through unnoticed.
        $warnmsg = '';
        if (preg_match("/[\n\r\t\x0B]/", $component)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in component!]');
        }
        if (preg_match("/[\n\r\t\x0B]/", $instance)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in instance!]');
        }

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'update',
                        array('pid' => $pid,
                                'seq' => $seq,
                                'oldseq' => $oldseq,
                                'realm' => $realm,
                                'id' => $id,
                                'component' => $component,
                                'instance' => $instance,
                                'level' => $level))) {
            // Success
            if ($warnmsg == '') {
                LogUtil::registerStatus($this->__('Done! Saved permission rule.'));
            } else {
                LogUtil::registerError($warnmsg);
            }
        }

        $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * Create a new permission.
     *
     * @param int 'id' group.
     * @param int 'realm' realm to which the permission belongs.
     * @param string 'component' component string.
     * @param string 'instance' instance string.
     * @param int 'level' permission level.
     *
     * @return bool true
     */
    public function create()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $realm = FormUtil::getPassedValue('realm', null, 'POST');
        $id = FormUtil::getPassedValue('id', null, 'POST');
        $component = FormUtil::getPassedValue('component', null, 'POST');
        $instance = FormUtil::getPassedValue('instance', null, 'POST');
        $level = FormUtil::getPassedValue('level', null, 'POST');
        $insseq = FormUtil::getPassedValue('insseq', null, 'POST');

        // Since we're using TextAreas, make sure no carriage-returns etc get through unnoticed.
        $warnmsg = '';
        if (preg_match("/[\n\r\t\x0B]/", $component)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in component!]');
        }
        if (preg_match("/[\n\r\t\x0B]/", $instance)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
            $warnmsg .= $this->__('[Illegal input in instance!]');
        }

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'create',
                        array('realm' => $realm,
                                'id' => $id,
                                'component' => $component,
                                'instance' => $instance,
                                'level' => $level,
                                'insseq' => $insseq))) {
            // Success
            if ($warnmsg == '') {
                LogUtil::registerStatus($this->__('Done! Created permission rule.'));
            } else {
                LogUtil::registerError($warnmsg);
            }
        }

        $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * Delete a permission.
     *
     * @param int 'pid' permissions id.
     *
     * @return bool true
     */
    public function delete()
    {
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $permgrp = FormUtil::getPassedValue('permgrp', null, 'REQUEST');
        $pid = FormUtil::getPassedValue('pid', null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'REQUEST');

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet

            // Add a hidden field for the item ID to the output
            $this->view->assign('pid', $pid);

            // assign the permission type and group
            $this->view->assign('permgrp', $permgrp);

            // Return the output that has been generated by this function
            return $this->view->fetch('permissions_admin_delete.tpl');
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'delete',
                        array('pid' => $pid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted permission rule.'));
        }

        $this->redirect(ModUtil::url('Permissions', 'admin', 'view',
                        array('permgrp' => $permgrp)));
    }

    /**
     * getGroupsInfo - get groups information.
     *
     * @todo remove calls to this function in favour of calls to the groups module
     *
     * @return array groups array
     */
    public function getGroupsInfo()
    {
        $dbtable = DBUtil::getTables();

        $groupcolumn = $dbtable['groups_column'];

        $orderBy = "ORDER BY $groupcolumn[name]";
        $objArrray = DBUtil::selectObjectArray('groups', '', $orderBy);
        $groups[SecurityUtil::PERMS_ALL] = $this->__('All groups');
        $groups[SecurityUtil::PERMS_UNREGISTERED] = $this->__('Unregistered');

        $ak = array_keys($objArrray);
        foreach ($ak as $v) {
            $gid = $objArrray[$v]['gid'];
            $groups[$gid] = $objArrray[$v]['name'];
        }

        return($groups);
    }

    /**
     * showInstanceInformation.
     *
     * Show instance information gathered from blocks and modules.
     *
     * @return boolean
     */
    public function viewinstanceinfo()
    {
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get all permissions schemas, sort and assign to the template
        $this->view->assign('schemas', ModUtil::apiFunc('Permissions', 'admin', 'getallschemas'));

        // we don't return the output back to the core here since this template is a full page
        // template i.e. we don't want this output wrapped in the theme.
        $this->view->display('permissions_admin_viewinstanceinfo.tpl');

        return true;
    }

    /**
     * Set configuration parameters of the module
     *
     * @return boolean
     */
    public function modifyconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // assign the module vars
        $this->view->assign($this->getVars());

        // return the output
        return $this->view->fetch('permissions_admin_modifyconfig.tpl');
    }

    /**
     * Save new settings.
     *
     * @return boolean
     */
    public function updateconfig()
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $error = false;
        $filter = (bool)FormUtil::getPassedValue('filter', false, 'POST');
        $this->setVar('filter', $filter);

        $rowview = (int)FormUtil::getPassedValue('rowview', 25, 'POST');
        $this->setVar('rowview', $rowview);

        $rowedit = (int)FormUtil::getPassedValue('rowedit', 35, 'POST');
        $this->setVar('rowedit', $rowedit);

        $lockadmin = (bool)FormUtil::getPassedValue('lockadmin', false, 'POST');
        $this->setVar('lockadmin', $lockadmin);

        $adminid = (int)FormUtil::getPassedValue('adminid', 1, 'POST');
        if ($adminid <> 0) {
            $perm = DBUtil::selectObjectByID('group_perms', $adminid, 'pid');
            if ($perm == false) {
                $adminid = 0;
                $error = true;
            }
        }
        $this->setVar('adminid', $adminid);

        // the module configuration has been updated successfuly
        if ($error == true) {
            LogUtil::registerStatus($this->__('Error! Could not save configuration: unknown permission rule ID.'));
            $this->redirect(ModUtil::url('Permissions', 'admin', 'modifyconfig'));
        }
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
        $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * Check permissions.
     *
     * @return boolean
     */
    public function checkpermissions()
    {
        $username = FormUtil::getPassedValue('username', null, 'POST');
        $returnto = FormUtil::getPassedValue('returnto', System::getCurrentUri(), 'POST');
        $this->redirect($returnto);
    }

}
