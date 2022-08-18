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

namespace Zikula\ZAuthBundle\Helper;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthBundle\Entity\UserVerificationEntity;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

class AdministrationActionsHelper
{
    public function __construct(
        private readonly PermissionApiInterface $permissionsApi,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly UserVerificationRepositoryInterface $userVerificationRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function user(AuthenticationMappingEntity $mapping): array
    {
        $actions = [];
        if (!$this->permissionsApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            return $actions;
        }
        /** @var UserVerificationEntity $userVerification */
        $userVerification = $this->userVerificationRepository->findOneBy([
            'uid' => $mapping->getUid(),
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL
        ]);
        // send verification email requires no further perm check
        if (null !== $userVerification && !$mapping->isVerifiedEmail()) {
            $title = (null === $userVerification->getVerifycode())
                ? $this->translator->trans('Send an e-mail verification code for %sub%', ['%sub%' => $mapping->getUname()])
                : $this->translator->trans('Send a new e-mail verification code for %sub%', ['%sub%' => $mapping->getUname()]);
            $actions['verify'] = [
                'url' => $this->router->generate('zikulazauthbundle_useradministration_verify', ['mapping' => $mapping->getId()]),
                'text' => $title,
                'icon' => 'envelope',
            ];
        }
        $hasModeratePermissionToUser = $this->permissionsApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE);
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_EDIT);
        if ($hasModeratePermissionToUser && $mapping->getUid() > 1) {
            $actions['senduname'] = [
                'url' => $this->router->generate('zikulazauthbundle_useradministration_sendusername', ['mapping' => $mapping->getId()]),
                'text' => $this->translator->trans('Send user name to %sub%', ['%sub%' => $mapping->getUname()]),
                'icon' => 'user',
            ];
        }
        if ($hasModeratePermissionToUser) {
            $actions['sendconfirm'] = [
                'url' => $this->router->generate('zikulazauthbundle_useradministration_sendconfirmation', ['mapping' => $mapping->getId()]),
                'text' => $this->translator->trans('Send password recovery e-mail to %sub%', ['%sub%' => $mapping->getUname()]),
                'icon' => 'key',
            ];
        }
        if ($hasEditPermissionToUser) {
            $userEntity = $this->userRepository->find($mapping->getUid());
            if (null !== $userEntity) {
                if ((bool) $userEntity->getAttributeValue(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)
                    && $userEntity->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)
                ) {
                    $title = $this->translator->trans('Cancel required change of password for %sub%', ['%sub%' => $mapping->getUname()]);
                    $fa = 'unlock-alt';
                } else {
                    $title = $this->translator->trans('Require %sub% to change password at next login', ['%sub%' => $mapping->getUname()]);
                    $fa = 'lock';
                }
                $actions['togglepass'] = [
                    'url' => $this->router->generate('zikulazauthbundle_useradministration_togglepasswordchange', ['user' => $mapping->getUid()]), // note intentionally UID
                    'text' => $title,
                    'icon' => $fa,
                ];
            }
        }
        if ($hasEditPermissionToUser && $mapping->getUid() > 1) {
            $actions['modify'] = [
                'url' => $this->router->generate('zikulazauthbundle_useradministration_modify', ['mapping' => $mapping->getId()]),
                'text' => $this->translator->trans('Edit %sub%', ['%sub%' => $mapping->getUname()]),
                'icon' => 'pencil-alt',
            ];
        }

        return $actions;
    }
}
