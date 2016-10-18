<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Controller;

use ModUtil;
use UserUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Core\Response\Ajax\AjaxResponse;

/**
 * @Route("/ajax")
 */
class AjaxController extends AbstractController
{
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
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $permorder = $request->request->get('permorder');

        for ($cnt = 0; $cnt < count($permorder); $cnt++) {
            $permission = $this->get('doctrine.orm.entity_manager')->find('ZikulaPermissionsModule:PermissionEntity', $permorder[$cnt]);
            $permission['sequence'] = $cnt;
        }

        $this->get('doctrine.orm.entity_manager')->flush();

        return new AjaxResponse(['result' => true]);
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
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $pid = $request->request->getDigits('pid');

        // check if this is the overall admin permission and return if this shall be deleted
        $perm = $this->get('doctrine.orm.entity_manager')->find('ZikulaPermissionsModule:PermissionEntity', $pid);
        if ($perm['pid'] == 1 && $perm['level'] == ACCESS_ADMIN && $perm['component'] == '.*' && $perm['instance'] == '.*') {
            throw new FatalErrorException($this->__('Notice: You cannot delete the main administration permission rule.'));
        }

        if (ModUtil::apiFunc('ZikulaPermissionsModule', 'admin', 'delete', ['pid' => $pid]) == true) {
            $variableApi = $this->get('zikula_extensions_module.api.variable');
            if ($pid == $variableApi->get('ZikulaPermissionsModule', 'adminid')) {
                $variableApi->set('ZikulaPermissionsModule', 'adminid', 0);
                $variableApi->set('ZikulaPermissionsModule', 'lockadmin', false);
            }

            return new AjaxResponse(['pid' => $pid]);
        }

        throw new FatalErrorException($this->__f('Error! Could not delete permission rule with ID %s.', ['%s' => $pid]));
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
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $uname = $request->request->get('test_user', '');
        $comp  = $request->request->get('test_component', '.*');
        $inst  = $request->request->get('test_instance', '.*');
        $level = $request->request->get('test_level', ACCESS_READ);

        $result = $this->__('Permission check result:') . ' ';
        $uid = !empty($uname) ? UserUtil::getIdFromName($uname) : 0;

        if ($uid === false) {
            $result .= '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
        } else {
            $granted = $this->hasPermission($comp, $inst, $level, $uid);

            $result .= '<span id="' . ($granted ? 'permissiontestinfogreen' : 'permissiontestinfored') . '">';
            $result .= ($uid == 0) ? $this->__('unregistered user') : $uname;
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
}
