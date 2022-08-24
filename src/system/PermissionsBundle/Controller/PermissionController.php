<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\PermissionsBundle\Entity\PermissionEntity;
use Zikula\PermissionsBundle\Form\Type\FilterListType;
use Zikula\PermissionsBundle\Form\Type\PermissionCheckType;
use Zikula\PermissionsBundle\Form\Type\PermissionType;
use Zikula\PermissionsBundle\Helper\SchemaHelper;
use Zikula\PermissionsBundle\Repository\PermissionRepositoryInterface;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

/**
 * @PermissionCheck("admin")
 */
#[Route('/permissions')]
class PermissionController extends AbstractController
{
    use TranslatorTrait;

    public function __construct(
        TranslatorInterface $translator,
        private readonly PermissionApiInterface $permissionApi,
        private readonly bool $lockAdminRule,
        private readonly int $adminRuleId,
        private readonly bool $enableFiltering
    ) {
        $this->setTranslator($translator);
    }

    /**
     * @Theme("admin")
     */
    #[Route('/list', name: 'zikulapermissionsbundle_permission_listpermissions')]
    public function listPermissions(
        GroupRepositoryInterface $groupsRepository,
        PermissionRepositoryInterface $permissionRepository,
        PermissionApiInterface $permissionApi,
        SchemaHelper $schemaHelper
    ): Response {
        $groups = $groupsRepository->getGroupNamesById();
        $permissions = $permissionRepository->getFilteredPermissions();
        $components = [$this->trans('All components') => '-1'] + $permissionRepository->getAllComponents();
        $colours = [$this->trans('All colours') => '-1'] + $permissionRepository->getAllColours();
        $permissionLevels = $permissionApi->accessLevelNames();

        $filterForm = $this->createForm(FilterListType::class, [], [
            'groupChoices' => $groups,
            'componentChoices' => $components,
            'colourChoices' => $colours,
        ]);
        $permissionCheckForm = $this->createForm(PermissionCheckType::class, [], [
            'permissionLevels' => $permissionLevels,
        ]);

        return $this->render('@ZikulaPermissions/Permission/list.html.twig', [
            'filterForm' => $filterForm->createView(),
            'permissionCheckForm' => $permissionCheckForm->createView(),
            'permissionLevels' => $permissionLevels,
            'permissions' => $permissions,
            'groups' => $groups,
            'lockAdminRule' => $this->lockAdminRule,
            'adminRuleId' => $this->adminRuleId,
            'schema' => $schemaHelper->getAllSchema(),
            'enableFilter' => $this->enableFiltering,
        ]);
    }

    #[Route('/edit/{pid}', name: 'zikulapermissionsbundle_permission_edit', options: ['expose' => true])]
    public function edit(
        Request $request,
        GroupRepositoryInterface $groupsRepository,
        PermissionRepositoryInterface $permissionRepository,
        PermissionApiInterface $permissionApi,
        PermissionEntity $permissionEntity = null
    ): JsonResponse {
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
            $row = null === $pid ? $this->renderView('@ZikulaPermissions/Permission/permissionTableRow.html.twig', [
                'permission' => $permissionEntity,
                'groups' => $groupNames,
                'permissionLevels' => $accessLevelNames,
                'lockAdminRule' => $this->lockAdminRule,
                'adminRuleId' => $this->adminRuleId,
            ]) : null;

            return $this->json([
                'permission' => $permissionEntity->toArray(),
                'row' => $row
            ]);
        }
        $view = $this->renderView('@ZikulaPermissions/Permission/permission.html.twig', [
            'form' => $form->createView(),
        ]);

        return $this->json(['view' => $view]);
    }

    /**
     * Change the order of a permission rule.
     */
    #[Route('/change-order', name: 'zikulapermissionsbundle_permission_changeorder', methods: ['POST'], options: ['expose' => true])]
    public function changeOrder(
        Request $request,
        ManagerRegistry $doctrine,
        PermissionRepositoryInterface $permissionRepository
    ): JsonResponse {
        $permOrder = $request->request->get('permorder');
        $amountOfPermOrderValues = count($permOrder);
        for ($cnt = 0; $cnt < $amountOfPermOrderValues; $cnt++) {
            $permission = $permissionRepository->find($permOrder[$cnt]);
            $permission->setSequence($cnt + 1);
        }
        $doctrine->getManager()->flush();

        return $this->json(['result' => true]);
    }

    /**
     * @throws RuntimeException Thrown if the requested permission rule is the default admin rule
     *                          or if the permission rule couldn't be deleted
     */
    #[Route('/delete/{pid}', name: 'zikulapermissionsbundle_permission_delete', methods: ['POST'], options: ['expose' => true])]
    public function delete(
        PermissionEntity $permissionEntity,
        ManagerRegistry $doctrine,
        PermissionRepositoryInterface $permissionRepository
    ): JsonResponse {
        // check if this is the overall admin permission and return if this shall be deleted
        if (1 === $permissionEntity->getPid()
            && ACCESS_ADMIN === $permissionEntity->getLevel()
            && '.*' === $permissionEntity->getComponent()
            && '.*' === $permissionEntity->getInstance()
        ) {
            throw new RuntimeException($this->trans('Notice: You cannot delete the main administration permission rule.'));
        }

        if ($this->lockAdminRule && $permissionEntity->getPid() === $this->adminRuleId) {
            throw new RuntimeException($this->trans('Notice: You cannot delete the locked administration permission rule.'));
        }

        $doctrine->getManager()->remove($permissionEntity);
        $doctrine->getManager()->flush();
        $permissionRepository->reSequence();

        return $this->json(['pid' => $permissionEntity->getPid()]);
    }

    /**
     * Test a permission rule for a given username.
     */
    #[Route('/test', name: 'zikulapermissionsbundle_permission_test', methods: ['POST'], options: ['expose' => true])]
    public function test(
        Request $request,
        PermissionApiInterface $permissionApi,
        UserRepositoryInterface $userRepository
    ): JsonResponse {
        $permissionCheckForm = $this->createForm(PermissionCheckType::class, [], [
            'permissionLevels' => $permissionApi->accessLevelNames()
        ]);
        $permissionCheckForm->handleRequest($request);
        $data = $permissionCheckForm->getData();

        $result = $this->trans('Permission check result:') . ' ';
        if (!empty($data['user'])) {
            $user = $userRepository->findOneBy(['uname' => $data['user']]);
            $uid = isset($user) ? $user->getUid() : false;
        } else {
            $uid = UsersConstant::USER_ID_ANONYMOUS;
        }

        if (false === $uid) {
            $result .= '<span class="text-danger">' . $this->trans('unknown user.') . '</span>';
        } else {
            $granted = $this->permissionApi->hasPermission($data['component'], $data['instance'], $data['level'], $uid);

            $result .= '<span class="' . ($granted ? 'text-success' : 'text-danger') . '">';
            $result .= UsersConstant::USER_ID_ANONYMOUS < $uid && isset($user) ? $user->getUname() : $this->trans('unregistered user');
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
