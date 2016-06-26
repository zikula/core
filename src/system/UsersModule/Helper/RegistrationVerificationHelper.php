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

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class RegistrationVerificationHelper
{
    use TranslatorTrait;

    /**
     * @var VariableApi
     */
    private $variableApi;

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
     * RegistrationHelper constructor.
     * @param VariableApi $variableApi
     * @param PermissionApi $permissionApi
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param TranslatorInterface $translator
     * @param MailHelper $mailHelper
     * @param CurrentUserApi $currentUserApi
     */
    public function __construct(
        VariableApi $variableApi,
        PermissionApi $permissionApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        TranslatorInterface $translator,
        MailHelper $mailHelper,
        CurrentUserApi $currentUserApi
    ) {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->setTranslator($translator);
        $this->mailHelper = $mailHelper;
        $this->currentUserApi = $currentUserApi;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @param UserEntity $userEntity
     * @return bool True on success; otherwise false.
     */
    public function sendVerificationCode(UserEntity $userEntity)
    {
        // we do not check permissions for guests here - registering users are not logged in and must complete this method.
        if ($this->currentUserApi->isLoggedIn() && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $verificationCode = $this->userVerificationRepository->setVerificationCode($userEntity->getUid(), UsersConstant::VERIFYCHGTYPE_REGEMAIL, $userEntity->getEmail());

        $codeSent = $this->mailHelper->sendNotification($userEntity->getEmail(), 'regverifyemail', [
            'user' => $userEntity,
            'verifycode' => $verificationCode,
        ]);

        $userVerificationEntity = $this->userVerificationRepository->findOneBy(['uid' => $userEntity->getUid(), 'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL]);
        if ($codeSent) {
            return $userVerificationEntity->getCreated_Dt();
        } else {
            $this->userVerificationRepository->removeAndFlush($userVerificationEntity);

            return false;
        }
    }
}
