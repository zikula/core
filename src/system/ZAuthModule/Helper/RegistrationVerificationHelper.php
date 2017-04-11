<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Api\PasswordApi;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
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
     * @var PasswordApi
     */
    private $passwordApi;

    /**
     * RegistrationVerificationHelper constructor.
     * @param PermissionApiInterface $permissionApi
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param MailHelper $mailHelper
     * @param CurrentUserApiInterface $currentUserApi
     * @param UserRepositoryInterface $userRepository
     * @param PasswordApi $passwordApi
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        MailHelper $mailHelper,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        PasswordApi $passwordApi
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
     * @param AuthenticationMappingEntity $mapping
     * @return bool True on success; otherwise false
     */
    public function sendVerificationCode(AuthenticationMappingEntity $mapping)
    {
        // we do not check permissions for guests here - registering users are not logged in and must complete this method.
        if ($this->currentUserApi->isLoggedIn() && !$this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $verificationCode = $this->passwordApi->generatePassword();
        $this->userVerificationRepository->setVerificationCode($mapping->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL, $this->passwordApi->getHashedPassword($verificationCode), $mapping->getEmail());
        $userEntity = $this->userRepository->find($mapping->getUid());
        $codeSent = $this->mailHelper->sendNotification($mapping->getEmail(), 'regverifyemail', [
            'user' => $mapping,
            'isApproved' => $userEntity->isApproved(),
            'verifycode' => $verificationCode,
        ]);

        $userVerificationEntity = $this->userVerificationRepository->findOneBy(['uid' => $mapping->getUid(), 'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL]);
        if ($codeSent) {
            return $userVerificationEntity->getCreated_Dt();
        } else {
            $this->userVerificationRepository->removeAndFlush($userVerificationEntity);

            return false;
        }
    }
}
