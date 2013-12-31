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

namespace Zikula\Module\PermissionsModule\Controller;

use SecurityUtil;
use ModUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula_Response_Ajax;
use DataUtil;
use UserUtil;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * Ajax controllers for the search module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
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
     *
     * @return \Zikula_Response_Ajax Ajax repsonse with updated permissions
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function updatepermissionAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

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
        ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'update',
                array('pid'       => $pid,
                      'seq'       => $seq,
                      'oldseq'    => $seq,
                      'realm'     => 0,
                      'id'        => $gid,
                      'component' => $component,
                      'instance'  => $instance,
                      'level'     => $level));

        // read current settings and return them
        $permission = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $pid)->toArray();

        $accesslevels = SecurityUtil::accesslevelnames();
        $permission['levelname'] = $accesslevels[$permission['level']];

        switch($permission['gid']) {
            case -1:
                $permission['groupname'] = $this->__('All groups');
                break;

            case 0:
                $permission['groupname'] = $this->__('Unregistered');
                break;

            default:
                $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));
                $permission['groupname'] = $group['name'];
        }

        return new Zikula_Response_Ajax($permission);
    }

    /**
     * Change the order of a permission rule
     *
     * @param permorder array of sorted permissions (value = permission id)
     *
     * @return \Zikula_Response_Ajax ajax response containing true
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function changeorderAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $permorder = $this->request->request->get('permorder');

        for($cnt = 0 ; $cnt < count($permorder) ; $cnt++) {
            $permission = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $permorder[$cnt]);
            $permission['sequence'] = $cnt;
        }

        $this->entityManager->flush();

        return new Zikula_Response_Ajax(array('result' => true));
    }

    /**
     * Create a blank permission and return it
     *
     * @return \Zikula_Response_Ajax array with new permission
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function createpermissionAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // add a blank permission
        $dummyperm = array(
            'realm'     => 0,
            'id'        => 0,
            'component' => '.*',
            'instance'  => '.*',
            'level'     => ACCESS_NONE,
            'insseq'    => -1
        );

        $newperm = ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'create', $dummyperm);
        if ($newperm == false) {
            throw new FatalErrorException($this->__('Error! Could not create new permission rule.'));
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
     * @return \Zikula_Response_Ajax Ajax response containing the id of the permission that has been deleted
     *
     * @throws FatalErrorException Thrown if the requested permission rule is the default admin rule or if
     *                                    if the permission rule couldn't be deleted
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function deletepermissionAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $pid = (int)$this->request->request->get('pid');

        // check if this is the overall admin permssion and return if this shall be deleted
        $perm = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $pid);
        if ($perm['pid'] == 1 && $perm['level'] == ACCESS_ADMIN && $perm['component'] == '.*' && $perm['instance'] == '.*') {
            throw new FatalErrorException($this->__('Notice: You cannot delete the main administration permission rule.'));
        }

        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'delete', array('pid' => $pid)) == true) {
            if ($pid == $this->getVar('adminid')) {
                $this->setVar('adminid', 0);
                $this->setVar('lockadmin', false);
            }

            return new Zikula_Response_Ajax(array('pid' => $pid));
        }

        throw new FatalErrorException($this->__f('Error! Could not delete permission rule with ID %s.', $pid));
    }

    /**
     * Test a permission rule for a given username
     *
     * @return \Zikula_Response_Ajax Ajax response containing string with test result for display
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function testpermissionAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $uname = $this->request->request->get('test_user', '');
        $comp  = $this->request->request->get('test_component', '.*');
        $inst  = $this->request->request->get('test_instance', '.*');
        $level = $this->request->request->get('test_level', ACCESS_READ);

        $result = $this->__('Permission check result:') . ' ';
        if (empty($uname)) {
            $uid = 0;
        } else {
            $uid = UserUtil::getIdFromName($uname);
        }

        if ($uid === false) {
            $result .= '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
        } else {
            $result .= '<span id="permissiontestinfogreen">';
            if ($uid == 0) {
                $result .= $this->__('unregistered user');
            } else {
                $result .= $uname;
            }
            $result .= ': ';
            if (SecurityUtil::checkPermission($comp, $inst, $level, $uid)) {
                $result .= $this->__('permission granted.');
            } else {
                $result .= $this->__('permission not granted.');
            }
            $result .= '</span>';
        }

        return new Zikula_Response_Ajax(array('testresult' => $result));
    }
}