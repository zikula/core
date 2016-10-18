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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class PermissionController extends AbstractController
{
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
        $form = $this->createForm('Zikula\PermissionsModule\Form\Type\PermissionType', $permissionEntity, [
            'translator' => $this->getTranslator(),
            'groups' => $this->get('zikula_groups_module.group_repository')->getGroupNamesById(),
            'permissionLevels' => $this->get('zikula_permissions_module.api.permission')->accessLevelNames()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permissionEntity = $form->getData();
            $pid = $permissionEntity->getPid();
            if (null == $pid) {
                if ($permissionEntity->getSequence() == -1) {
                    $permissionEntity->setSequence($this->get('zikula_permissions_module.permission_repository')->getMaxSequence() + 1); // last
                } else {
                    $this->get('zikula_permissions_module.permission_repository')->updateSequencesFrom($permissionEntity->getSequence(), 1); // insert
                }
            }
            $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->persistAndFlush($permissionEntity);
            $row = (null == $pid) ? $this->renderView("@ZikulaPermissionsModule/Admin/permissionTableRow.html.twig", [
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
        $view = $this->renderView("@ZikulaPermissionsModule/Permission/Permission.html.twig", $templateParameters);

        return new AjaxResponse(['view' => $view]);
    }
}
