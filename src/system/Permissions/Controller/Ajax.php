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

class Permissions_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Updates a permission rule in the database
     *
     * @param pid the permission id
     * @param gid the group id
     * @param seq the sequence
     * @param component the permission component
     * @param instance the permission instance
     * @param level the permission level
     * @return mixed updated permission as array or Ajax error
     */
    public function updatepermission()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN));

        $pid       = (int)$this->request->request->get('pid');
        $gid       = (int)$this->request->request->get('gid');
        $seq       = (int)$this->request->request->get('seq', 9999);
        $component = $this->request->request->get('comp', '.*');
        $instance  = $this->request->request->get('inst', '.*');
        $level     = (int)$this->request->request->get('level', 0);

        if (preg_match("/[\n\r\t\x0B]/", $component)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
        }
        if (preg_match("/[\n\r\t\x0B]/", $instance)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
        }

        // Pass to API

        ModUtil::apiFunc('Permissions', 'admin', 'update',
                array('pid'       => $pid,
                'seq'       => $seq,
                'oldseq'    => $seq,
                'realm'     => 0,
                'id'        => $gid,
                'component' => $component,
                'instance'  => $instance,
                'level'     => $level));

        // read current settings and return them
        $perm = DBUtil::selectObjectByID('group_perms', $pid, 'pid');
        $accesslevels = SecurityUtil::accesslevelnames();
        $perm['levelname'] = $accesslevels[$perm['level']];
        switch ($perm['gid']) {
            case -1:
                $perm['groupname'] = $this->__('All groups');
                break;
            case 0:
                $perm['groupname'] = $this->__('Unregistered');
                break;
            default:
                $group = DBUtil::selectObjectByID('groups', $perm['gid'], 'gid');
                $perm['groupname'] = $group['name'];
        }

        return new Zikula_Response_Ajax($perm);
    }

    /**
     *
     * @param permorder array of sorted permissions (value = permission id)
     * @return mixed true or Ajax error
     */
    public function changeorder()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN));

        $permorder = $this->request->request->get('permorder');

        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];

        for ($cnt=0; $cnt<count($permorder); $cnt++) {
            $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($permorder[$cnt]) . "'";
            $obj = array('sequence' => $cnt);
            DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
        }

        return new Zikula_Response_Ajax(array('result' => true));
    }

    /**
     * Create a blank permission and return it
     *
     * @return mixed array with new permission or Ajax error
     */
    public function createpermission()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN));

        // add a blank permission
        $dummyperm = array('realm'     => 0,
                'id'        => 0,
                'component' => '.*',
                'instance'  => '.*',
                'level'     => ACCESS_NONE,
                'insseq'    => -1);

        $newperm = ModUtil::apiFunc('Permissions', 'admin', 'create', $dummyperm);
        if ($newperm == false) {
            AjaxUtil::error($this->__('Error! Could not create new permission rule.'));
        }

        $accesslevels = SecurityUtil::accesslevelnames();

        $newperm['instance']  = DataUtil::formatForDisplay($newperm['instance']);
        $newperm['component'] = DataUtil::formatForDisplay($newperm['component']);
        $newperm['levelname'] = $accesslevels[$newperm['level']];
        $newperm['groupname'] = $this->__('Unregistered');

        return new Zikula_Response_Ajax($newperm);
    }

    /**
     * Delete a permission
     *
     * @param pid the permission id
     * @return mixed the id of the permission that has been deleted or Ajax error
     */
    public function deletepermission()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN));

        $pid = (int)$this->request->request->get('pid');

        // check if this is the overall admin permssion and return if this shall be deleted
        $perm = DBUtil::selectObjectByID('group_perms', $pid, 'pid');
        if ($perm['pid'] == 1 && $perm['level'] == ACCESS_ADMIN && $perm['component'] == '.*' && $perm['instance'] == '.*') {
            throw new Zikula_Exception_Fatal($this->__('Notice: You cannot delete the main administration permission rule.'));
        }

        if (ModUtil::apiFunc('Permissions', 'admin', 'delete', array('pid' => $pid)) == true) {
            if ($pid == $this->getVar('adminid')) {
                $this->setVar('adminid', 0);
                $this->setVar('lockadmin', false);
            }

            return new Zikula_Response_Ajax(array('pid' => $pid));
        }

        throw new Zikula_Exception_Fatal($this->__f('Error! Could not delete permission rule with ID %s.', $pid));
    }

    /**
     * Test a permission rule for a given username
     *
     * @param test_user the username
     * @param test_component the component
     * @param test_instance the instance
     * @param test_level the accesslevel
     * @return string with test result for display
     */
    public function testpermission()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN));

        $uname = $this->request->request->get('test_user', '');
        $comp  = $this->request->request->get('test_component', '.*');
        $inst  = $this->request->request->get('test_instance', '.*');
        $level = $this->request->request->get('test_level', ACCESS_READ);

        $result = $this->__('Permission check result:') . ' ';
        $uid = UserUtil::getIdFromName($uname);

        if ($uid==false) {
            $result .= '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
        } else {
            if (SecurityUtil::checkPermission($comp, $inst, $level, $uid)) {
                $result .= '<span id="permissiontestinfogreen">' . $this->__('permission granted.') . '</span>';
            } else {
                $result .= '<span id="permissiontestinfored">' . $this->__('permission not granted.') . '</span>';
            }
        }

        return new Zikula_Response_Ajax(array('testresult' => $result));
    }
}
