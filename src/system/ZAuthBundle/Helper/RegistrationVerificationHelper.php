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

use DateTime;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMapping;
use Zikula\ZAuthBundle\Entity\UserVerification;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

class RegistrationVerificationHelper
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly UserVerificationRepositoryInterface $userVerificationRepository,
        private readonly MailHelper $mailHelper,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EncoderFactoryInterface $encoderFactory
    ) {
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @return DateTime|bool
     * @throws AccessDeniedException
     */
    public function sendVerificationCode(AuthenticationMapping $mapping)
    {
        // we do not check permissions for guests here - registering users are not logged in and must complete this method.
        if ($this->currentUserApi->isLoggedIn() && !$this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $verificationCode = bin2hex(random_bytes(8));
        $hashedCode = $this->encoderFactory->getEncoder($mapping)->encodePassword($verificationCode, null);

        $this->userVerificationRepository->setVerificationCode($mapping->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL, $hashedCode, $mapping->getEmail());
        /** @var User $userEntity */
        $userEntity = $this->userRepository->find($mapping->getUid());
        $codeSent = $this->mailHelper->sendNotification($mapping->getEmail(), 'regverifyemail', [
            'user' => $mapping,
            'isApproved' => $userEntity->isApproved(),
            'verifycode' => $verificationCode,
        ]);

        /** @var UserVerification $userVerificationEntity */
        $userVerificationEntity = $this->userVerificationRepository->findOneBy(['uid' => $mapping->getUid(), 'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL]);
        if ($codeSent) {
            return $userVerificationEntity->getCreatedDate();
        }

        $this->userVerificationRepository->removeAndFlush($userVerificationEntity);

        return false;
    }
}
