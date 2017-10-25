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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Zikula\PermissionsModule\Form\Type\FilterListType;
use Zikula\PermissionsModule\Form\Type\PermissionCheckType;
use Zikula\PermissionsModule\Form\Type\PermissionType;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant;

class PermissionController extends AbstractController
{
    /**
     * @Route("/list")
     * @Theme("admin")
     * @Template("ZikulaPermissionsModule:Permission:list.html.twig")
     *
     * view permissions
     *
     * @return array
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function listAction()
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $groups = $this->get('zikula_groups_module.group_repository')->getGroupNamesById();
        $permissions = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->getFilteredPermissions();
        $permissionLevels = $this->get('zikula_permissions_module.api.permission')->accessLevelNames();
        $components = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->getAllComponents();
        $components = [$this->__('All components') => '-1'] + $components;
        $filterForm = $this->createForm(FilterListType::class, [], [
            'groupChoices' => $groups,
            'componentChoices' => $components,
            'translator' => $this->getTranslator()
        ]);
        $templateParameters['filterForm'] = $filterForm->createView();
        $permissionCheckForm = $this->createForm(PermissionCheckType::class, [], [
            'translator' => $this->getTranslator(),
            'permissionLevels' => $permissionLevels
        ]);
        $templateParameters['permissionCheckForm'] = $permissionCheckForm->createView();
        $templateParameters['permissionLevels'] = $permissionLevels;
        $templateParameters['permissions'] = $permissions;
        $templateParameters['groups'] = $groups;
        $templateParameters['lockadmin'] = $this->getVar('lockadmin', 1) ? 1 : 0;
        $templateParameters['adminId'] = $this->getVar('adminid', 1);
        $templateParameters['schema'] = $this->get('zikula_permissions_module.helper.schema_helper')->getAllSchema();
        $templateParameters['enableFilter'] = (bool)$this->getVar('filter', 1);

        return $templateParameters;
    }

    /**
     * @Route("/edit/{pid}", options={"expose"=true})
     * @param Request $request
     * @param PermissionEntity $permissionEntity
     * @return AjaxResponse
     */
    public function editAction(Request $request, PermissionEntity $permissionEntity = null)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (!isset($permissionEntity)) {
            $permissionEntity = new PermissionEntity();
            if ($request->request->has('sequence')) {
                $permissionEntity->setSequence($request->request->get('sequence'));
            }
        }
        $form = $this->createForm(PermissionType::class, $permissionEntity, [
            'translator' => $this->getTranslator(),
            'groups' => $this->get('zikula_groups_module.group_repository')->getGroupNamesById(),
            'permissionLevels' => $this->get('zikula_permissions_module.api.permission')->accessLevelNames()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permissionEntity = $form->getData();
            $pid = $permissionEntity->getPid();
            if (null === $pid) {
                if ($permissionEntity->getSequence() == -1) {
                    $permissionEntity->setSequence($this->get('zikula_permissions_module.permission_repository')->getMaxSequence() + 1); // last
                } else {
                    $this->get('zikula_permissions_module.permission_repository')->updateSequencesFrom($permissionEntity->getSequence(), 1); // insert
                }
            }
            $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->persistAndFlush($permissionEntity);
            $row = (null === $pid) ? $this->renderView('@ZikulaPermissionsModule/Permission/permissionTableRow.html.twig', [
                'permission' => $permissionEntity,
                'groups' => $this->get('zikula_groups_module.group_repository')->getGroupNamesById(),
                'permissionLevels' => $this->get('zikula_permissions_module.api.permission')->accessLevelNames(),
                'lockadmin' => $this->getVar('lockadmin', 1) ? 1 : 0,
                'adminId' => $this->getVar('adminid', 1)
            ]) : null;

            return new AjaxResponse([
                'permission' => $permissionEntity->toArray(),
                'row' => $row
            ]);
        }
        $templateParameters = [
            'form' => $form->createView(),
        ];
        $view = $this->renderView('@ZikulaPermissionsModule/Permission/permission.html.twig', $templateParameters);

        return new AjaxResponse(['view' => $view]);
    }

    /**
     * @Route("/change-order", options={"expose"=true})
     * @Method("POST")
     *
     * Change the order of a permission rule
     *
     * @param Request $request
     *  permorder array of sorted permissions (value = permission id)
     * @return AjaxResponse ajax response containing true
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function changeOrderAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $permOrder = $request->request->get('permorder');
        $amountOfPermOrderValues = count($permOrder);
        for ($cnt = 0; $cnt < $amountOfPermOrderValues; $cnt++) {
            $permission = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->find($permOrder[$cnt]);
            $permission['sequence'] = $cnt + 1;
        }
        $this->get('doctrine')->getManager()->flush();

        return new AjaxResponse(['result' => true]);
    }

    /**
     * @Route("/delete/{pid}", options={"expose"=true})
     * @Method("POST")
     *
     * Delete a permission
     *
     * @param PermissionEntity $permissionEntity
     * @return AjaxResponse Ajax response containing the id of the permission that has been deleted
     * @throws FatalErrorException Thrown if the requested permission rule is the default admin rule or if
     *                                    if the permission rule couldn't be deleted
     */
    public function deleteAction(PermissionEntity $permissionEntity)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // check if this is the overall admin permission and return if this shall be deleted
        if ($permissionEntity->getPid() == 1
            && $permissionEntity->getLevel() == ACCESS_ADMIN
            && $permissionEntity->getComponent() == '.*'
            && $permissionEntity->getInstance() == '.*'
        ) {
            throw new FatalErrorException($this->__('Notice: You cannot delete the main administration permission rule.'));
        }

        $this->get('doctrine')->getManager()->remove($permissionEntity);
        $this->get('doctrine')->getManager()->flush();
        $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->reSequence();
        if ($permissionEntity->getPid() == $this->getVar('adminid')) {
            $this->setVar('adminid', 0);
            $this->setVar('lockadmin', false);
        }

        return new AjaxResponse(['pid' => $permissionEntity->getPid()]);
    }

    /**
     * @Route("/test", options={"expose"=true})
     * @Method("POST")
     *
     * Test a permission rule for a given username
     *
     * @param Request $request
     * @return AjaxResponse Ajax response containing string with test result for display
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function testAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $permissionCheckForm = $this->createForm(PermissionCheckType::class, [], [
            'translator' => $this->getTranslator(),
            'permissionLevels' => $this->get('zikula_permissions_module.api.permission')->accessLevelNames()
        ]);
        $permissionCheckForm->handleRequest($request);
        $data = $permissionCheckForm->getData();

        $result = $this->__('Permission check result:') . ' ';
        if (!empty($data['user'])) {
            $user = $this->get('zikula_users_module.user_repository')->findOneBy(['uname' => $data['user']]);
            $uid = isset($user) ? $user->getUid() : Constant::USER_ID_ANONYMOUS;
        } else {
            $uid = Constant::USER_ID_ANONYMOUS;
        }

        if ($uid === false) {
            $result .= '<span id="permissiontestinfored">' . $this->__('unknown user.') . '</span>';
        } else {
            $granted = $this->hasPermission($data['component'], $data['instance'], $data['level'], $uid);

            $result .= '<span id="' . ($granted ? 'permissiontestinfogreen' : 'permissiontestinfored') . '">';
            $result .= ($uid == 0) ? $this->__('unregistered user') : $data['user'];
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
