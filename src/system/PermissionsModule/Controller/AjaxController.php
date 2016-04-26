<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Controller;

use SecurityUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Response\Ajax\AjaxResponse;
use DataUtil;
use UserUtil;
use Zikula\Core\Exception\FatalErrorException;

/**
 * @Route("/ajax")
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * @Route("/update", options={"expose"=true})
     * @Method("POST")
     *
     * Updates a permission rule in the database
     *
     * @param Request $request
     *  pid the permission id
     *  gid the group id
     *  seq the sequence
     *  component the permission component
     *  instance the permission instance
     *  level the permission level
     *
     * @return AjaxResponse Ajax repsonse with updated permissions
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function updateAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $pid       = (int)$request->request->get('pid');
        $gid       = (int)$request->request->get('gid');
        $seq       = (int)$request->request->get('seq', 9999);
        $component = $request->request->get('comp', '.*');
        $instance  = $request->request->get('inst', '.*');
        $level     = (int)$request->request->get('level', 0);

        if (preg_match("/[\n\r\t\x0B]/", $component)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
        }
        if (preg_match("/[\n\r\t\x0B]/", $instance)) {
            $component = trim(preg_replace("/[\n\r\t\x0B]/", "", $component));
            $instance = trim(preg_replace("/[\n\r\t\x0B]/", "", $instance));
        }

        // Pass to API
        ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'update', [
            'pid'       => $pid,
            'seq'       => $seq,
            'oldseq'    => $seq,
            'realm'     => 0,
            'id'        => $gid,
            'component' => $component,
            'instance'  => $instance,
            'level'     => $level
        ]);

        // read current settings and return them
        $permission = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $pid)->toArray();

        $accesslevels = SecurityUtil::accesslevelnames();
        $permission['levelname'] = $accesslevels[$permission['level']];
        $permission['groupname'] = $this->determineGroupName($permission['gid']);

        return new AjaxResponse($permission);
    }

    /**
     * @Route("/change-order", options={"expose"=true})
     * @Method("POST")
     *
     * Change the order of a permission rule
     *
     * @param Request $request
     *  permorder array of sorted permissions (value = permission id)
     *
     * @return AjaxResponse ajax response containing true
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function changeorderAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $permorder = $request->request->get('permorder');

        for ($cnt = 0; $cnt < count($permorder); $cnt++) {
            $permission = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $permorder[$cnt]);
            $permission['sequence'] = $cnt;
        }

        $this->entityManager->flush();

        return new AjaxResponse(['result' => true]);
    }

    /**
     * @Route("/create", options={"expose"=true})
     * @Method("POST")
     *
     * Create a new permission and return it
     *
     * @param Request $request
     *
     * @return AjaxResponse array with new permission
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws FatalErrorException if cannot create
     */
    public function createAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // create a new permission array
        $dummyperm = [
            'realm'     => 0,
            'id'        => $request->request->get('group', 0),
            'component' => $request->request->get('component', '.*'),
            'instance'  => $request->request->get('instance', '.*'),
            'level'     => $request->request->get('level', ACCESS_NONE),
            'insseq'    => $request->request->get('insseq')
        ];

        $newperm = ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'create', $dummyperm);
        if ($newperm == false) {
            throw new FatalErrorException($this->__('Error! Could not create new permission rule.'));
        }

        $accesslevels = SecurityUtil::accesslevelnames();

        $newperm['instance']  = DataUtil::formatForDisplay($newperm['instance']);
        $newperm['component'] = DataUtil::formatForDisplay($newperm['component']);
        $newperm['levelname'] = $accesslevels[$newperm['level']];
        $newperm['groupname'] = $this->determineGroupName($newperm['gid']);

        return new AjaxResponse($newperm);
    }

    /**
     * @Route("/delete", options={"expose"=true})
     * @Method("POST")
     *
     * Delete a permission
     *
     * @param Request $request
     *
     * @return AjaxResponse Ajax response containing the id of the permission that has been deleted
     *
     * @throws FatalErrorException Thrown if the requested permission rule is the default admin rule or if
     *                                    if the permission rule couldn't be deleted
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function deleteAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $pid = $request->request->get('pid');

        // check if this is the overall admin permission and return if this shall be deleted
        $perm = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $pid);
        if ($perm['pid'] == 1 && $perm['level'] == ACCESS_ADMIN && $perm['component'] == '.*' && $perm['instance'] == '.*') {
            throw new FatalErrorException($this->__('Notice: You cannot delete the main administration permission rule.'));
        }

        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'delete', ['pid' => $pid]) == true) {
            if ($pid == $this->getVar('adminid')) {
                $this->setVar('adminid', 0);
                $this->setVar('lockadmin', false);
            }

            return new AjaxResponse(['pid' => $pid]);
        }

        throw new FatalErrorException($this->__f('Error! Could not delete permission rule with ID %s.', $pid));
    }

    /**
     * @Route("/test", options={"expose"=true})
     * @Method("POST")
     *
     * Test a permission rule for a given username
     *
     * @param Request $request
     *
     * @return AjaxResponse Ajax response containing string with test result for display
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function testAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $uname = $request->request->get('test_user', '');
        $comp  = $request->request->get('test_component', '.*');
        $inst  = $request->request->get('test_instance', '.*');
        $level = $request->request->get('test_level', ACCESS_READ);

        $result = $this->__('Permission check result:') . ' ';
        if (empty($uname)) {
            $uid = 0;
        } else {
            $uid = UserUtil::getIdFromName($uname);
        }

        if ($uid === false) {
            $result .= '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
        } else {
            $granted = SecurityUtil::checkPermission($comp, $inst, $level, $uid);

            $result .= '<span id="' . ($granted ? 'permissiontestinfogreen' : 'permissiontestinfored') . '">';
            if ($uid == 0) {
                $result .= $this->__('unregistered user');
            } else {
                $result .= $uname;
            }
            $result .= ': ';
            if ($granted) {
                $result .= $this->__('permission granted.');
            } else {
                $result .= $this->__('permission not granted.');
            }
            $result .= '</span>';
        }

        return new AjaxResponse(['testresult' => $result]);
    }

    /**
     * determine the group name from ID
     *
     * @param $gid
     * @return string
     */
    private function determineGroupName($gid)
    {
        switch ($gid) {
            case -1:
                $name = $this->__('All groups');
                break;

            case 0:
                $name = $this->__('Unregistered');
                break;

            default:
                $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid, 'group_membership' => false]);
                $name = $group['name'];
        }

        return $name;
    }
}
