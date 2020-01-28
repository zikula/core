<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;
use Zikula\PermissionsModule\Form\Type\FilterListType;
use Zikula\PermissionsModule\Form\Type\PermissionCheckType;
use Zikula\PermissionsModule\Form\Type\PermissionType;
use Zikula\PermissionsModule\Helper\SchemaHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class PermissionController extends AbstractController
{
    /**
     * @Route("/list")
     * @Theme("admin")
     * @Template("@ZikulaPermissionsModule/Permission/list.html.twig")
     *
     * View permissions.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function listAction(
        GroupRepositoryInterface $groupsRepository,
        PermissionRepositoryInterface $permissionRepository,
        PermissionApiInterface $permissionApi,
        SchemaHelper $schemaHelper
    ): array {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $groups = $groupsRepository->getGroupNamesById();
        $permissions = $permissionRepository->getFilteredPermissions();
        $components = $permissionRepository->getAllComponents();
        $components = [$this->trans('All components') => '-1'] + $components;
        $permissionLevels = $permissionApi->accessLevelNames();

        $filterForm = $this->createForm(FilterListType::class, [], [
            'groupChoices' => $groups,
            'componentChoices' => $components
        ]);
        $permissionCheckForm = $this->createForm(PermissionCheckType::class, [], [
            'permissionLevels' => $permissionLevels
        ]);

        return [
            'filterForm' => $filterForm->createView(),
            'permissionCheckForm' => $permissionCheckForm->createView(),
            'permissionLevels' => $permissionLevels,
            'permissions' => $permissions,
            'groups' => $groups,
            'lockadmin' => $this->getVar('lockadmin', 1) ? 1 : 0,
            'adminId' => $this->getVar('adminid', 1),
            'schema' => $schemaHelper->getAllSchema(),
            'enableFilter' => (bool)$this->getVar('filter', 1)
        ];
    }

    /**
     * @Route("/edit/{pid}", options={"expose"=true})
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions to the module
     */
    public function editAction(
        Request $request,
        GroupRepositoryInterface $groupsRepository,
        PermissionRepositoryInterface $permissionRepository,
        PermissionApiInterface $permissionApi,
        PermissionEntity $permissionEntity = null
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (!isset($permissionEntity)) {
            $permissionEntity = new PermissionEntity();
            if ($request->request->has('sequence')) {
                $permissionEntity->setSequence($request->request->getInt('sequence'));
            }
        }

        $groupNames = $groupsRepository->getGroupNamesById();
        $accessLevelNames = $permissionApi->accessLevelNames();

        $form = $this->createForm(PermissionType::class, $permissionEntity, [
            'groups' => $groupNames,
            'permissionLevels' => $accessLevelNames
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permissionEntity = $form->getData();
            $pid = $permissionEntity->getPid();
            if (null === $pid) {
                if (-1 === $permissionEntity->getSequence()) {
                    $permissionEntity->setSequence($permissionRepository->getMaxSequence() + 1); // last
                } else {
                    $permissionRepository->updateSequencesFrom($permissionEntity->getSequence()); // insert
                }
            }
            $permissionRepository->persistAndFlush($permissionEntity);
            $row = null === $pid ? $this->renderView('@ZikulaPermissionsModule/Permission/permissionTableRow.html.twig', [
                'permission' => $permissionEntity,
                'groups' => $groupNames,
                'permissionLevels' => $accessLevelNames,
                'lockadmin' => $this->getVar('lockadmin', 1) ? 1 : 0,
                'adminId' => $this->getVar('adminid', 1)
            ]) : null;

            return $this->json([
                'permission' => $permissionEntity->toArray(),
                'row' => $row
            ]);
        }
        $templateParameters = [
            'form' => $form->createView()
        ];
        $view = $this->renderView('@ZikulaPermissionsModule/Permission/permission.html.twig', $templateParameters);

        return $this->json(['view' => $view]);
    }

    /**
     * @Route("/change-order", methods = {"POST"}, options={"expose"=true})
     *
     * Change the order of a permission rule.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function changeOrderAction(
        Request $request,
        PermissionRepositoryInterface $permissionRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $permOrder = $request->request->get('permorder');
        $amountOfPermOrderValues = count($permOrder);
        for ($cnt = 0; $cnt < $amountOfPermOrderValues; $cnt++) {
            $permission = $permissionRepository->find($permOrder[$cnt]);
            $permission['sequence'] = $cnt + 1;
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['result' => true]);
    }

    /**
     * @Route("/delete/{pid}", methods = {"POST"}, options={"expose"=true})
     *
     * Delete a permission.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws FatalError Thrown if the requested permission rule is the default admin rule
     *                           or if the permission rule couldn't be deleted
     */
    public function deleteAction(
        PermissionEntity $permissionEntity,
        PermissionRepositoryInterface $permissionRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // check if this is the overall admin permission and return if this shall be deleted
        if (1 === $permissionEntity->getPid()
            && ACCESS_ADMIN === $permissionEntity->getLevel()
            && '.*' === $permissionEntity->getComponent()
            && '.*' === $permissionEntity->getInstance()
        ) {
            throw new FatalError($this->trans('Notice: You cannot delete the main administration permission rule.'));
        }

        $this->getDoctrine()->getManager()->remove($permissionEntity);
        $this->getDoctrine()->getManager()->flush();
        $permissionRepository->reSequence();
        if ($permissionEntity->getPid() === $this->getVar('adminid')) {
            $this->setVar('adminid', 0);
            $this->setVar('lockadmin', false);
        }

        return $this->json(['pid' => $permissionEntity->getPid()]);
    }

    /**
     * @Route("/test", methods = {"POST"}, options={"expose"=true})
     *
     * Test a permission rule for a given username.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function testAction(
        Request $request,
        PermissionApiInterface $permissionApi,
        UserRepositoryInterface $userRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $permissionCheckForm = $this->createForm(PermissionCheckType::class, [], [
            'permissionLevels' => $permissionApi->accessLevelNames()
        ]);
        $permissionCheckForm->handleRequest($request);
        $data = $permissionCheckForm->getData();

        $result = $this->trans('Permission check result:') . ' ';
        if (!empty($data['user'])) {
            $user = $userRepository->findOneBy(['uname' => $data['user']]);
            $uid = isset($user) ? $user->getUid() : Constant::USER_ID_ANONYMOUS;
        } else {
            $uid = Constant::USER_ID_ANONYMOUS;
        }

        if (false === $uid) {
            $result .= '<span class="text-danger">' . $this->trans('unknown user.') . '</span>';
        } else {
            $granted = $this->hasPermission($data['component'], $data['instance'], $data['level'], $uid);

            $result .= '<span class="' . ($granted ? 'text-success' : 'text-danger') . '">';
            $result .= (0 === $uid) ? $this->trans('unregistered user') : $data['user'];
            $result .= ': ';
            if ($granted) {
                $result .= $this->trans('permission granted.');
            } else {
                $result .= $this->trans('permission not granted.');
            }
            $result .= '</span>';
        }

        return $this->json(['testresult' => $result]);
    }
}
