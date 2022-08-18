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

namespace Zikula\UsersBundle\Helper;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Constant as UsersConstant;
use Zikula\UsersBundle\Entity\UserEntity;

class AdministrationActionsHelper
{
    public function __construct(
        private readonly PermissionApiInterface $permissionsApi,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly CurrentUserApiInterface $currentUserApi
    ) {
    }

    public function user(UserEntity $user): array
    {
        $actions = [];
        if (!$this->permissionsApi->hasPermission('ZikulaUsersModule::', 'ANY', ACCESS_MODERATE)) {
            return $actions;
        }
        if (UsersConstant::ACTIVATED_ACTIVE !== $user->getActivated() && $this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $actions['approveForce'] = [
                'url' => $this->router->generate('zikulausersbundle_useradministration_approve', ['user' => $user->getUid(), 'force' => true]),
                'text' => $this->translator->trans('Approve %sub%', ['%sub%' => $user->getUname()]),
                'icon' => 'check text-success',
            ];
        }
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        $hasDeletePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_DELETE);
        if ($hasEditPermissionToUser && $user->getUid() > UsersConstant::USER_ID_ANONYMOUS) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulausersbundle_useradministration_modify', ['user' => $user->getUid()]),
                'text' => $this->translator->trans('Edit %sub%', ['%sub%' => $user->getUname()]),
                'icon' => 'pencil-alt',
            ];
        }
        $isCurrentUser = $this->currentUserApi->get('uid') === $user->getUid();
        if (!$isCurrentUser && $hasDeletePermissionToUser && $user->getUid() > UsersConstant::USER_ID_ADMIN) {
            $actions['delete'] = [
                'url' => $this->router->generate('zikulausersbundle_useradministration_delete', ['user' => $user->getUid()]),
                'text' => $this->translator->trans('Delete %sub%', ['%sub%' => $user->getUname()]),
                'icon' => 'trash-alt',
            ];
        }

        return $actions;
    }
}
