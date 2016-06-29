<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Helper;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\ZAuthConstant;

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
    private $userRepository;

    /**
     * UserAdministrationActionsFunction constructor.
     * @param PermissionApi $permissionsApi
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        PermissionApi $permissionsApi,
        RouterInterface $router,
        TranslatorInterface $translator,
        UserVerificationRepositoryInterface $userVerificationRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->permissionsApi = $permissionsApi;
        $this->router = $router;
        $this->translator = $translator;
        $this->verificationRepository = $userVerificationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param AuthenticationMappingEntity $user
     * @return array
     */
    public function user(AuthenticationMappingEntity $user)
    {
        $actions = [];
        if (!$this->permissionsApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            return $actions;
        }
        /** @var UserVerificationEntity $userVerification */
        $userVerification = $this->verificationRepository->findOneBy([
            'uid' => $user->getUid(),
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL
        ]);
        // send verification email requires no further perm check
        if (!$user->isVerifiedEmail()) {
            if (!empty($userVerification)) {
                $title = (null == $userVerification->getVerifycode())
                    ? $this->translator->__f('Send an e-mail verification code for %sub%', ["%sub%" => $user->getUname()])
                    : $this->translator->__f('Send a new e-mail verification code for %sub%', ["%sub%" => $user->getUname()]);
                $actions['verify'] = [
                    'url' => $this->router->generate('zikulazauthmodule_useradministration_verify', ['user' => $user->getUid()]), // @todo route
                    'text' => $title,
                    'icon' => 'envelope',
                ];
            }
        }
        $hasModeratePermissionToUser = $this->permissionsApi->hasPermission('ZikulaZAuthModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE);
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaZAuthModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        if ($user->getUid() > 1 && $hasModeratePermissionToUser) {
            $actions['senduname'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_sendusername', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Send user name to %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'user',
            ];
        }
        if ($hasModeratePermissionToUser) {
            $actions['sendconfirm'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_sendconfirmation', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Send password recovery code to %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'key',
            ];
        }
        if ($hasEditPermissionToUser) {
            $userEntity = $this->userRepository->find($user->getUid());
            if ($userEntity->getAttributes()->containsKey('_Users_mustChangePassword') && (bool)$userEntity->getAttributeValue('_Users_mustChangePassword')) {
                $title = $this->translator->__f('Cancel required change of password for %sub%', ["%sub%" => $user->getUname()]);
                $fa = 'unlock-alt';
            } else {
                $title = $this->translator->__f('Require %sub% to change password at next login', ["%sub%" => $user->getUname()]);
                $fa = 'lock';
            }
            $actions['togglepass'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_togglepasswordchange', ['user' => $user->getUid()]),
                'text' => $title,
                'icon' => $fa,
            ];
        }
        if ($user->getUid() > 1 && $hasEditPermissionToUser) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_modify', ['user' => $user->getUid()]),
                'text' => $this->translator->__f('Edit %sub%', ["%sub%" => $user->getUname()]),
                'icon' => 'pencil',
            ];
        }

        return $actions;
    }
}
