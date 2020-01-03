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

namespace Zikula\ZAuthModule\Helper;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\ZAuthConstant;

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
     * @var UserVerificationRepositoryInterface
     */
    private $verificationRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(
        PermissionApiInterface $permissionsApi,
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

    public function user(AuthenticationMappingEntity $mapping): array
    {
        $actions = [];
        if (!$this->permissionsApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            return $actions;
        }
        /** @var UserVerificationEntity $userVerification */
        $userVerification = $this->verificationRepository->findOneBy([
            'uid' => $mapping->getUid(),
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL
        ]);
        // send verification email requires no further perm check
        if (null !== $userVerification && !$mapping->isVerifiedEmail()) {
            $title = (null === $userVerification->getVerifycode())
                ? $this->translator->__f('Send an e-mail verification code for %sub%', ['%sub%' => $mapping->getUname()])
                : $this->translator->__f('Send a new e-mail verification code for %sub%', ['%sub%' => $mapping->getUname()]);
            $actions['verify'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_verify', ['mapping' => $mapping->getId()]),
                'text' => $title,
                'icon' => 'envelope',
            ];
        }
        $hasModeratePermissionToUser = $this->permissionsApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE);
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_EDIT);
        if ($hasModeratePermissionToUser && $mapping->getUid() > 1) {
            $actions['senduname'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_sendusername', ['mapping' => $mapping->getId()]),
                'text' => $this->translator->__f('Send user name to %sub%', ['%sub%' => $mapping->getUname()]),
                'icon' => 'user',
            ];
        }
        if ($hasModeratePermissionToUser) {
            $actions['sendconfirm'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_sendconfirmation', ['mapping' => $mapping->getId()]),
                'text' => $this->translator->__f('Send password recovery e-mail to %sub%', ['%sub%' => $mapping->getUname()]),
                'icon' => 'key',
            ];
        }
        if ($hasEditPermissionToUser) {
            $userEntity = $this->userRepository->find($mapping->getUid());
            if (null !== $userEntity) {
                if ((bool)$userEntity->getAttributeValue(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)
                    && $userEntity->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)
                ) {
                    $title = $this->translator->__f('Cancel required change of password for %sub%', ['%sub%' => $mapping->getUname()]);
                    $fa = 'unlock-alt';
                } else {
                    $title = $this->translator->__f('Require %sub% to change password at next login', ['%sub%' => $mapping->getUname()]);
                    $fa = 'lock';
                }
                $actions['togglepass'] = [
                    'url' => $this->router->generate('zikulazauthmodule_useradministration_togglepasswordchange', ['user' => $mapping->getUid()]), // note intentionally UID
                    'text' => $title,
                    'icon' => $fa,
                ];
            }
        }
        if ($hasEditPermissionToUser && $mapping->getUid() > 1) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_modify', ['mapping' => $mapping->getId()]),
                'text' => $this->translator->__f('Edit %sub%', ['%sub%' => $mapping->getUname()]),
                'icon' => 'pencil-alt',
            ];
        }

        return $actions;
    }
}
