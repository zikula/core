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
use Zikula\UsersModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserVerificationEntity;

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
    public function registration(UserEntity $user)
    {
        $actions = [];
        if (!$this->permissionsApi->hasPermission('ZikulaUsersModule::', 'ANY', ACCESS_MODERATE)) {
            return $actions;
        }
        $approvalOrder = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::APPROVAL_BEFORE);
        $userIsVerified = ($user->getActivated() == UsersConstant::ACTIVATED_PENDING_REG)
            && $user->getAttributes()->containsKey('_Users_isVerified')
            ? $user->getAttributeValue('_Users_isVerified')
            : false;
        /** @var UserVerificationEntity $userVerification */
        $userVerification = $this->verificationRepository->findOneBy([
            'uid' => $user->getUid(),
            'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL
        ]);
        // display registration details requires no further perm check
        $actions['display'] = [
            'url' => $this->router->generate('zikulausersmodule_registrationadministration_display', ['user' => $user->getUid()]),
            'text' => $this->translator->__f('Display registration details for %sub%', ["%sub%" => $user->getUname()]),
            'icon' => 'info-circle',
        ];

        // send verification email requires no further perm check
        if (!$userIsVerified && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $user->isApproved())) {
            if (!empty($userVerification)) {
                $title = (null == $userVerification->getVerifycode())
                    ? $this->translator->__f('Send an e-mail verification code for %sub%', ["%sub%" => $user->getUname()])
                    : $this->translator->__f('Send a new e-mail verification code for %sub%', ["%sub%" => $user->getUname()]);
            } else {
                // @todo is this state possible? or is this just a development error?
                $title = $this->translator->__f('Unknown state for %sub%', ["%sub%" => $user->getUname()]);
            }
            $actions['verify'] = [
                'url' => $this->router->generate('zikulausersmodule_registrationadministration_verify', ['user' => $user->getUid()]),
                'text' => $title,
                'icon' => 'envelope',
            ];
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT)) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulausersmodule_registrationadministration_modify', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Modify registration details for %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'pencil-square-o',
            ];
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADD)
            && !$user->isApproved() && (($approvalOrder != UsersConstant::APPROVAL_AFTER) || $userIsVerified)) {
            if (!$userIsVerified) {
                $title = ($approvalOrder == UsersConstant::APPROVAL_AFTER)
                    ? $this->translator->__f('Pre-approve %sub% (verification still required)', ["%sub%" => $user->getUname()])
                    : $this->translator->__f('Approve %sub%', ["%sub%" => $user->getUname()]);
            } else {
                $title = $this->translator->__f('Approve %sub% (creates a new user account)', ["%sub%" => $user->getUname()]);
            }
            $actions['approve'] = [
                'url' => $this->router->generate('zikulausersmodule_registrationadministration_approve', ['user' => $user->getUid()]),
                'text' => $title,
                'icon' => 'check-square-o',
            ];
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_DELETE)) {
            $actions['deny'] = [
                'url' => $this->router->generate('zikulausersmodule_registrationadministration_deny', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Deny for %sub% (deletes registration)', ["%sub%" => $user->getUname()]),
                'icon' => 'trash-o',
            ];
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)
            && !$userIsVerified && (null != $user->getPass()) && ('' != $user->getPass())) {
            $actions['approveForce'] = [
                'url' => $this->router->generate('zikulausersmodule_registrationadministration_approve', ['user' => $user->getUid(), 'force' => true]),
                'text' => $this->translator->__f('Skip verification for %sub% (approves, and creates a new user account) ', ["%sub%" => $user->getUname()]),
                'icon' => 'share-square-o',
            ];
        }

        return $actions;
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
        $hasModeratePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE);
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        $hasDeletePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_DELETE);
        $userHasActualPassword = (null != $user->getPass()) && ('' != $user->getPass()) && ($user->getPass() != UsersConstant::PWD_NO_USERS_AUTHENTICATION);
        if ($user->getUid() > 1 && $hasModeratePermissionToUser) {
            $actions['senduname'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_sendusername', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Send user name to %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'envelope',
            ];
        }
        if ($userHasActualPassword && $hasModeratePermissionToUser) {
            $actions['sendconfirm'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_sendconfirmation', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Send password recovery code to %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'key',
            ];
        }
        if ($userHasActualPassword && $hasEditPermissionToUser) {
            if ($user->getAttributes()->containsKey('_Users_mustChangePassword') && (bool)$user->getAttributeValue('_Users_mustChangePassword')) {
                $title = $this->translator->__f('Cancel required change of password for %sub%', ["%sub%" => $user->getUname()]);
                $fa = 'unlock-alt';
            } else {
                $title = $this->translator->__f('Require %sub% to change password at next login', ["%sub%" => $user->getUname()]);
                $fa = 'lock';
            }
            $actions['togglepass'] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_togglepasswordchange', ['user' => $user->getUid()]),
                'text' => $title,
                'icon' => $fa,
            ];
        }
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
