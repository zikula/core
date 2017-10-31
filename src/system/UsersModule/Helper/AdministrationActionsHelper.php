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
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
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
     * @var CurrentUserApiInterface
     */
    private $currentUser;

    /**
     * UserAdministrationActionsFunction constructor.
     * @param PermissionApiInterface $permissionsApi
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param CurrentUserApiInterface $currentUserApi
     */
    public function __construct(
        PermissionApiInterface $permissionsApi,
        RouterInterface $router,
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi
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
        if (UsersConstant::ACTIVATED_ACTIVE != $user->getActivated() && $this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $actions['approveForce'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_approve', ['user' => $user->getUid(), 'force' => true]),
                'text' => $this->translator->__f('Approve %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'check text-success',
            ];
        }
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        $hasDeletePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_DELETE);
        if ($user->getUid() > UsersConstant::USER_ID_ANONYMOUS && $hasEditPermissionToUser) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_modify', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Edit %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'pencil',
            ];
        }
        $isCurrentUser = $this->currentUser->get('uid') == $user->getUid();
        if ($user->getUid() > UsersConstant::USER_ID_ADMIN && !$isCurrentUser && $hasDeletePermissionToUser) {
            $actions['delete'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_delete', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Delete %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'trash-o',
            ];
        }

        return $actions;
    }
}
