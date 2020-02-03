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

use DateTime;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class RegistrationVerificationHelper
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    /**
     * @var MailHelper
     */
    private $mailHelper;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(
        PermissionApiInterface $permissionApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        MailHelper $mailHelper,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        PasswordApiInterface $passwordApi
    ) {
        $this->permissionApi = $permissionApi;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->mailHelper = $mailHelper;
        $this->currentUserApi = $currentUserApi;
        $this->userRepository = $userRepository;
        $this->passwordApi = $passwordApi;
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @return DateTime|bool
     * @throws AccessDeniedException
     */
    public function sendVerificationCode(AuthenticationMappingEntity $mapping)
    {
        // we do not check permissions for guests here - registering users are not logged in and must complete this method.
        if ($this->currentUserApi->isLoggedIn() && !$this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $verificationCode = $this->passwordApi->generatePassword();
        $this->userVerificationRepository->setVerificationCode($mapping->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL, $this->passwordApi->getHashedPassword($verificationCode), $mapping->getEmail());
        /** @var UserEntity $userEntity */
        $userEntity = $this->userRepository->find($mapping->getUid());
        $codeSent = $this->mailHelper->sendNotification($mapping->getEmail(), 'regverifyemail', [
            'user' => $mapping,
            'isApproved' => $userEntity->isApproved(),
            'verifycode' => $verificationCode,
        ]);

        /** @var UserVerificationEntity $userVerificationEntity */
        $userVerificationEntity = $this->userVerificationRepository->findOneBy(['uid' => $mapping->getUid(), 'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL]);
        if ($codeSent) {
            return $userVerificationEntity->getCreatedDate();
        }
        $this->userVerificationRepository->removeAndFlush($userVerificationEntity);

        return false;
    }
}
