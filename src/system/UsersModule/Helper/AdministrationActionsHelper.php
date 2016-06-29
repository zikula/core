<?php
/**
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
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Constant as UsersConstant;

class AdministrationActionsHelper
{
    /**
     * @var PermissionApi
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
     * @var UserVerificationRepositoryInterface
     */
    private $verificationRepository;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var CurrentUserApi
     */
    private $currentUser;

    /**
     * UserAdministrationActionsFunction constructor.
     * @param PermissionApi $permissionsApi
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param VariableApi $variableApi
     * @param CurrentUserApi $currentUserApi
     */
    public function __construct(
        PermissionApi $permissionsApi,
        RouterInterface $router,
        TranslatorInterface $translator,
        UserVerificationRepositoryInterface $userVerificationRepository,
        VariableApi $variableApi,
        CurrentUserApi $currentUserApi
    ) {
        $this->permissionsApi = $permissionsApi;
        $this->router = $router;
        $this->translator = $translator;
        $this->verificationRepository = $userVerificationRepository;
        $this->variableApi = $variableApi;
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
                'text' => $this->translator->__f('Approve %sub% (approves, and creates a new user account) ', ["%sub%" => $user->getUname()]),
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
                // @todo or deny and delete if pending :: zikulausersmodule_registrationadministration_deny
                'text' => $this->translator->__f('Delete %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'trash-o',
            ];
        }

        return $actions;
    }
}
