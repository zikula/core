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
class Permissions_Controller_AdminController extends Zikula_AbstractController
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
    public function mainAction()
    {
        // Security check will be done in view()
        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * view permissions
     * @return string HTML string
     */
    public function viewAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        $permgrp = $this->request->get('permgrp', -1);
        $testuser = $this->request->request->get('test_user', null);
        $testcomponent = $this->request->request->get('test_component', null);
        $testinstance = $this->request->request->get('test_instance', null);
        $testlevel = $this->request->request->get('test_level', null);

        $testresult = '';
        if (!empty($testuser) && !empty($testcomponent) && !empty($testinstance)) {
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

        $ids = $this->getGroupsInfo();
        
        $where = '';
        
        $enableFilter = $this->getVar('filter', 1);
        if ($enableFilter == 1) {
            $permgrpparts = explode('+', $permgrp);
            
            if ($permgrpparts[0] == 'g') {
                if (is_array($permgrpparts) && $permgrpparts[1] != SecurityUtil::PERMS_ALL) {
                    $where = "WHERE (p.gid = '" . SecurityUtil::PERMS_ALL . "' OR p.gid = '" . DataUtil::formatForStore($permgrpparts[1]) . "')";
                    $permgrp = $permgrpparts[1];
                    $this->view->assign('filtertype', 'group');
                } else {
                    $permgrp = SecurityUtil::PERMS_ALL;
                }
            } else if ($permgrpparts[0] == 'c') {
                if (is_array($permgrpparts) && $permgrpparts[1] != SecurityUtil::PERMS_ALL) {
                    $where = "WHERE (p.component = '.*' OR p.component LIKE '" . DataUtil::formatForStore($permgrpparts[1]) . "%')";
                    $permgrp = $permgrpparts[1];
                    $this->view->assign('filtertype', 'component');
                } else {
                    $permgrp = SecurityUtil::PERMS_ALL;
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
        
        $dql = "SELECT p FROM Permissions\Entity\Permission p $where ORDER BY p.sequence ASC";
        $query = $this->entityManager->createQuery($dql);
        $objArray = $query->getResult();
        
        $numrows = count($objArray);

        $permissions = array();
        
        if ($numrows > 0) {
            $accesslevels = SecurityUtil::accesslevelnames();
            $csrftoken = SecurityUtil::generateCsrfToken($this->container, true);
            $rownum = 1;
            
            foreach ($objArray as $obj) {
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

                $permissions[] = array(
                    'sequence' => $obj['sequence'],
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
        
        $components = array(-1 => $this->__('All components'));
        
        // read all perms to extract components
        $allperms = $this->entityManager->getRepository('Permissions\Entity\Permission')->findBy(array(), array('sequence' => 'ASC'));
        foreach ($allperms as $singlePerm) {
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

        return $this->response($this->view->fetch('permissions_admin_view.tpl'));
    }

    /**
     * Increment a permission.
     *
     * @param int 'pid' permissions id
     *
     * @return boolean true
     */
    public function incAction()
    {
        $csrftoken = $this->request->request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $pid = $this->request->query->get('pid', null);
        $permgrp = $this->request->query->get('permgrp', null);

        if (empty($permgrp)) {
            // For group-permissions, make sure we return something sensible.
            // Doesn't matter if we're looking at user-permissions...
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'inc', array('pid' => $pid, 'permgrp' => $permgrp))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Incremented permission rule.'));
        }

        // Redirect
        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view', array('permgrp' => $permgrp)));
    }

    /**
     * Decrement a permission.
     *
     * @param int 'pid' permissions id.
     *
     * @return boolean true
     */
    public function decAction()
    {
        $csrftoken = $this->request->request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $pid = $this->request->query->get('pid', null);
        $permgrp = $this->request->query->get('permgrp', null);

        if (!isset($permgrp) || $permgrp == '') {
            // For group-permissions, make sure we return something sensible.
            // This doesn't matter if we're looking at user-permissions...
            $permgrp = SecurityUtil::PERMS_ALL;
        }

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'dec', array('pid' => $pid, 'permgrp' => $permgrp))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Decremented permission rule.'));
        }

        // Redirect
        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view', array('permgrp' => $permgrp)));
    }

    /**
     * Edit / Create permissions in the mainview.
     *
     * @return boolean
     */
    public function listeditAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        $chgpid = $this->request->query->get('chgpid', null);
        $action = $this->request->query->get('action', null);
        $insseq = $this->request->query->get('insseq', null);
        $permgrp = $this->request->get('permgrp', null);

        // Assign the permission levels
        $this->view->assign('permissionlevels', SecurityUtil::accesslevelnames());
        
        // get all permissions
        $allperms = $this->entityManager->getRepository('Permissions\Entity\Permission')->findBy(array(), array('sequence' => 'ASC'));    
        if (!$allperms && $action != 'add') {
            LogUtil::registerError($this->__('Error! No permission rules of this kind were found. Please add some first.'));
            return $this->redirect(ModUtil::url('modules', 'admin', 'view'));
        }
        
        $viewperms = ($action == 'modify') ? $this->__('Modify permission rule') : $this->__('Create new permission rule');
        $this->view->assign('title', $viewperms);
         
        $mlpermtype = $this->__('Group');
        $this->view->assign('mlpermtype', $mlpermtype);

        $ids = $this->getGroupsInfo();
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
        } else if ($action == 'insert') {
            $this->view->assign('formurl', ModUtil::url('Permissions', 'admin', 'create'))
                       ->assign('permgrp', $permgrp)
                       ->assign('insseq', $insseq);

            // Realms hard-coded - jgm
            $this->view->assign('realm', 0)
                       ->assign('submit', $this->__('Create new permission rule'));
        } else if ($action == 'add') {
            // Form-start
            $this->view->assign('formurl', ModUtil::url('Permissions', 'admin', 'create'))
                       ->assign('permgrp', $permgrp)
                       ->assign('insseq', -1);

            // Realms hard-coded - jgm
            $this->view->assign('realm', 0)
                       ->assign('submit', $this->__('Create new permission rule'));
        }

        $this->view->assign('action', $action);
        
        $accesslevels = SecurityUtil::accesslevelnames();
        $permissions = array();
        
        foreach ($allperms as $obj) {
            $id = $obj['gid']; //get's uid or gid accordingly
            
            $permissions[] = array(
                // Realms not currently functional so hide the output - jgm
                //'realms' => $realms[$realm],
                'pid' => $obj['pid'],
                'group' => $ids[$id],
                'component' => $obj['component'],
                'instance' => $obj['instance'],
                'accesslevel' => $accesslevels[$obj['level']],
                'level' => $obj['level'],
                'sequence' => $obj['sequence']
            );
            
            if ($action == 'modify' && $obj['pid'] == $chgpid) {
                $this->view->assign('selectedid', $id);
            }
        }
        
        $this->view->assign('permissions', $permissions);

        return $this->response($this->view->fetch('permissions_admin_listedit.tpl'));
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
    public function updateAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $pid = $this->request->request->get('pid', null);
        $seq = $this->request->request->get('seq', null);
        $oldseq = $this->request->request->get('oldseq', null);
        $realm = $this->request->request->get('realm', null);
        $id = $this->request->request->get('id', null);
        $component = $this->request->request->get('component', null);
        $instance = $this->request->request->get('instance', null);
        $level = $this->request->request->get('level', null);

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

        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
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
    public function createAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $realm = $this->request->request->get('realm', null);
        $id = $this->request->request->get('id', null);
        $component = $this->request->request->get('component', null);
        $instance = $this->request->request->get('instance', null);
        $level = $this->request->request->get('level', null);
        $insseq = $this->request->request->get('insseq', null);

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

        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * Delete a permission.
     *
     * @param int 'pid' permissions id.
     *
     * @return bool true
     */
    public function deleteAction()
    {
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $permgrp = $this->request->get('permgrp', null);
        $pid = $this->request->get('pid', null);
        $confirmation = $this->request->get('confirmation', null);

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet

            // Add a hidden field for the item ID to the output
            $this->view->assign('pid', $pid);

            // assign the permission type and group
            $this->view->assign('permgrp', $permgrp);

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('permissions_admin_delete.tpl'));
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Pass to API
        if (ModUtil::apiFunc('Permissions', 'admin', 'delete', array('pid' => $pid))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted permission rule.'));
        }

        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view', array('permgrp' => $permgrp)));
    }

    /**
     * getGroupsInfo - get groups information.
     *
     * @todo remove calls to this function in favour of calls to the groups module
     *
     * @return array groups array
     */
    function getGroupsInfo()
    {
        $groups = array();
        $groups[SecurityUtil::PERMS_ALL] = $this->__('All groups');
        $groups[SecurityUtil::PERMS_UNREGISTERED] = $this->__('Unregistered');

        $objArray = ModUtil::apiFunc('Groups', 'user', 'getall');
        foreach ($objArray as $group) {
            $groups[$group['gid']] = $group['name'];
        }

        return $groups;
    }

    /**
     * showInstanceInformation.
     *
     * Show instance information gathered from blocks and modules.
     *
     * @return boolean
     */
    public function viewinstanceinfoAction()
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
    public function modifyconfigAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // assign the module vars
        $this->view->assign($this->getVars());

        // return the output
        return $this->response($this->view->fetch('permissions_admin_modifyconfig.tpl'));
    }

    /**
     * Save new settings.
     *
     * @return boolean
     */
    public function updateconfigAction()
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $error = false;
        $filter = (bool)$this->request->request->get('filter', false);
        $this->setVar('filter', $filter);

        $rowview = (int)$this->request->request->get('rowview', 25);
        $this->setVar('rowview', $rowview);

        $rowedit = (int)$this->request->request->get('rowedit', 35);
        $this->setVar('rowedit', $rowedit);

        $lockadmin = (bool)$this->request->request->get('lockadmin', false);
        $this->setVar('lockadmin', $lockadmin);

        $adminid = (int)$this->request->request->get('adminid', 1);
        if ($adminid <> 0) {
            $perm = $this->entityManager->find('Permissions\Entity\Permission', $adminid);
            if (!$perm) {
                $adminid = 0;
                $error = true;
            }
        }
        $this->setVar('adminid', $adminid);

        // the module configuration has been updated successfuly
        if ($error == true) {
            LogUtil::registerStatus($this->__('Error! Could not save configuration: unknown permission rule ID.'));
            return $this->redirect(ModUtil::url('Permissions', 'admin', 'modifyconfig'));
        }
        
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
        return $this->redirect(ModUtil::url('Permissions', 'admin', 'view'));
    }

    /**
     * Check permissions.
     *
     * @return boolean
     */
    public function checkpermissionsAction()
    {
        $returnto = $this->request->request->get('returnto', System::getCurrentUri());
        return $this->redirect($returnto);
    }

}