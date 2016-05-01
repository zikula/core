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
use Zikula\UsersModule\Entity\Repository\UserVerificationRepository;
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
     * @var UserVerificationRepository
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
     * @param UserVerificationRepository $userVerificationRepository
     * @param VariableApi $variableApi
     * @param CurrentUserApi $currentUserApi
     */
    public function __construct(
        PermissionApi $permissionsApi,
        RouterInterface $router,
        TranslatorInterface $translator,
        UserVerificationRepository $userVerificationRepository,
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
     * @return string
     */
    public function registration(UserEntity $user)
    {
        $content = '';
        if (!$this->permissionsApi->hasPermission('ZikulaUsersModule::', 'ANY', ACCESS_MODERATE)) {
            return $content;
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
        $url = $this->router->generate('zikulausersmodule_admin_displayregistration', ['uid' => $user->getUid()]);
        $title = $this->translator->__f('Display registration details for %sub%', ["%sub%" => $user->getUname()]);
        $content .= '<a class="fa fa-fw fa-info-circle tooltips" href="' . $url . '" title="' . $title . '"></a>';

        // send verification email requires no further perm check
        if (!$userIsVerified && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $user->isApproved())) {
            $url = $this->router->generate('zikulausersmodule_admin_verifyregistration', ['uid' => $user->getUid()]);
            if (!empty($userVerification)) {
                $title = (null == $userVerification->getVerifycode())
                    ? $this->translator->__f('Send an e-mail verification code for %sub%', ["%sub%" => $user->getUname()])
                    : $this->translator->__f('Send a new e-mail verification code for %sub%', ["%sub%" => $user->getUname()]);
            } else {
                // @todo is this state possible? or is this just a development error?
                $title = $this->translator->__f('Unknown state for %sub%', ["%sub%" => $user->getUname()]);
            }
            $content .= '<a class="fa fa-fw fa-envelope tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT)) {
            $url = $this->router->generate('zikulausersmodule_admin_modifyregistration', ['uid' => $user->getUid()]);
            $title = $this->translator->__f('Modify registration details for %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-pencil-square-o tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADD)
            && !$user->isApproved() && (($approvalOrder != UsersConstant::APPROVAL_AFTER) || $userIsVerified)) {
            $url = $this->router->generate('zikulausersmodule_admin_approveregistration', ['uid' => $user->getUid()]);
            if (!$userIsVerified) {
                $title = ($approvalOrder == UsersConstant::APPROVAL_AFTER)
                    ? $this->translator->__f('Pre-approve %sub% (verification still required)', ["%sub%" => $user->getUname()])
                    : $this->translator->__f('Approve %sub%', ["%sub%" => $user->getUname()]);
            } else {
                $title = $this->translator->__f('Approve %sub% (creates a new user account)', ["%sub%" => $user->getUname()]);
            }
            $content .= '<a class="fa fa-fw fa-check-square-o tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_DELETE)) {
            $url = $this->router->generate('zikulausersmodule_admin_denyregistration', ['uid' => $user->getUid()]);
            $title = $this->translator->__f('Deny for %sub% (deletes registration)', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-trash-o tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }

        if ($this->permissionsApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)
            && !$userIsVerified && (null != $user->getPass()) && ('' != $user->getPass())) {
            $url = $this->router->generate('zikulausersmodule_admin_approveregistration', ['uid' => $user->getUid(), 'force' => true]);
            $title = $this->translator->__f('Skip verification for %sub% (approves, and creates a new user account) ', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-share-square-o tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }

        return $content;
    }

    /**
     * @param UserEntity $user
     * @return string
     */
    public function user(UserEntity $user)
    {
        $content = '';
        if (!$this->permissionsApi->hasPermission('ZikulaUsersModule::', 'ANY', ACCESS_MODERATE)) {
            return $content;
        }
        $hasModeratePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE);
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        $hasDeletePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_DELETE);
        $userHasActualPassword = (null != $user->getPass()) && ('' != $user->getPass()) && ($user->getPass() != UsersConstant::PWD_NO_USERS_AUTHENTICATION);
        if ($user->getUid() > 1 && $hasModeratePermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_useradministration_sendusername', ['user' => $user->getUid()]);
            $title = $this->translator->__f('Send user name to %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-user tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }
        if ($userHasActualPassword && $hasModeratePermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_useradministration_sendconfirmation', ['user' => $user->getUid()]);
            $title = $this->translator->__f('Send password recovery code to %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-key tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }
        if ($userHasActualPassword && $hasEditPermissionToUser) {
            if ($user->getAttributes()->containsKey('_Users_mustChangePassword') && (bool)$user->getAttributeValue('_Users_mustChangePassword')) {
                $title = $this->translator->__f('Cancel required change of password for %sub%', ["%sub%" => $user->getUname()]);
                $fa = 'unlock-alt';
            } else {
                $title = $this->translator->__f('Require %sub% to change password at next login', ["%sub%" => $user->getUname()]);
                $fa = 'lock';
            }
            $url = $this->router->generate('zikulausersmodule_useradministration_togglepasswordchange', ['user' => $user->getUid()]);
            $content .= '<a class="fa fa-fw fa-' . $fa . ' tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }
        if ($user->getUid() > 1 && $hasEditPermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_useradministration_modify', ['user' => $user->getUid()]);
            $title = $this->translator->__f('Edit %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-pencil tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }
        $isCurrentUser = $this->currentUser->get('uid') == $user->getUid();
        if ($user->getUid() > 2 && !$isCurrentUser && $hasDeletePermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_useradministration_delete', ['user' => $user->getUid()]);
            $title = $this->translator->__f('Delete %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-trash-o tooltips" href="' . $url . '" title="' . $title . '"></a>';
        }

        return $content;
    }
}
