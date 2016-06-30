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

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Helper\MailHelper;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class RegistrationVerificationHelper
{
    use TranslatorTrait;

    /**
     * @var PermissionApi
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
     * @var CurrentUserApi
     */
    private $currentUserApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * RegistrationHelper constructor.
     * @param PermissionApi $permissionApi
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param TranslatorInterface $translator
     * @param MailHelper $mailHelper
     * @param CurrentUserApi $currentUserApi
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        PermissionApi $permissionApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        TranslatorInterface $translator,
        MailHelper $mailHelper,
        CurrentUserApi $currentUserApi,
        UserRepositoryInterface $userRepository
    ) {
        $this->permissionApi = $permissionApi;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->setTranslator($translator);
        $this->mailHelper = $mailHelper;
        $this->currentUserApi = $currentUserApi;
        $this->userRepository = $userRepository;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @param AuthenticationMappingEntity $mapping
     * @return bool True on success; otherwise false.
     */
    public function sendVerificationCode(AuthenticationMappingEntity $mapping)
    {
        // we do not check permissions for guests here - registering users are not logged in and must complete this method.
        if ($this->currentUserApi->isLoggedIn() && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $verificationCode = $this->userVerificationRepository->setVerificationCode($mapping->getUid(), ZAuthConstant::VERIFYCHGTYPE_REGEMAIL, $mapping->getEmail());
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
