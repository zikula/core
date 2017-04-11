<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

class AdministrationActionsHelper
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionsApi;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CurrentUserApi
     */
    private $currentUser;

    /**
     * UserAdministrationActionsFunction constructor.
     * @param PermissionApiInterface $permissionsApi
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param CurrentUserApi $currentUserApi
     */
    public function __construct(
        PermissionApiInterface $permissionsApi,
        RouterInterface $router,
        TranslatorInterface $translator,
        CurrentUserApi $currentUserApi
    ) {
        $this->permissionsApi = $permissionsApi;
        $this->router = $router;
        $this->translator = $translator;
        $this->currentUser = $currentUserApi;
    }

    /**
     * @param UserEntity $user
     * @return array
     */
    public function user(UserEntity $user)
    {
        $actions = [];
        if (!$this->permissionsApi->hasPermission('ZikulaUsersModule::', 'ANY', ACCESS_MODERATE)) {
            return $actions;
        }
        if ($user->getActivated() != UsersConstant::ACTIVATED_ACTIVE && $this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $actions['approveForce'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_approve', ['user' => $user->getUid(), 'force' => true]),
                'text' => $this->translator->__f('Approve %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'check text-success',
            ];
        }
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        $hasDeletePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_DELETE);
        if ($user->getUid() > 1 && $hasEditPermissionToUser) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_modify', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Edit %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'pencil',
            ];
        }
        $isCurrentUser = $this->currentUser->get('uid') == $user->getUid();
        if ($user->getUid() > 2 && !$isCurrentUser && $hasDeletePermissionToUser) {
            $actions['delete'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_delete', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Delete %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'trash-o',
            ];
        }

        return $actions;
    }
}
