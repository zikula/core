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

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class RegistrationVerificationHelper
{
    use TranslatorTrait;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var MailHelper
     */
    private $mailHelper;

    /**
     * RegistrationHelper constructor.
     * @param VariableApi $variableApi
     * @param SessionInterface $session
     * @param PermissionApi $permissionApi
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param TranslatorInterface $translator
     * @param UserRepositoryInterface $userRepository
     * @param MailHelper $mailHelper
     */
    public function __construct(
        VariableApi $variableApi,
        SessionInterface $session,
        PermissionApi $permissionApi,
        UserVerificationRepositoryInterface $userVerificationRepository,
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository,
        MailHelper $mailHelper
    ) {
        $this->variableApi = $variableApi;
        $this->session = $session;
        $this->permissionApi = $permissionApi;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->setTranslator($translator);
        $this->userRepository = $userRepository;
        $this->mailHelper = $mailHelper;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @param UserEntity $userEntity  optional; if not set, then $uid must be set and point to a valid registration record.
     * @param int $uid The uid of a valid registration record; optional; if not set, then $reginfo must be set and valid.
     * @param bool $force Indicates that a verification code should be sent, even if the Users module configuration is
     *                                set not to verify e-mail addresses; optional; only has an effect if the current user is
     *                                an administrator.
     * @return bool True on success; otherwise false.
     */
    public function sendVerificationCode(UserEntity $userEntity = null, $uid = null, $force = null)
    {
        // In the future, it is possible we will add a feature to allow a newly registered user to resend
        // a new verification code to himself after doing a login-like process with information from  his
        // registration record, so allow not-logged-in plus READ, as well as moderator.

        // we do not check permissions for guests here (see #1874)
        if ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        if (!isset($userEntity) && (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid))) {
            throw new \InvalidArgumentException($this->translator->__('Invalid arguments array received'));
        } else {
            if (!isset($userEntity)) {
                // Got just a uid.
                $userEntity = $this->userRepository->find($uid);
            }
            if (!isset($userEntity)) {
                throw new \RuntimeException($this->translator->__f('Error! Unable to retrieve registration record with uid \'%uid\'', ['%uid' => $uid]));
            }
            if (null == $userEntity->getEmail() || '' == $userEntity->getEmail()) {
                throw new \InvalidArgumentException($this->translator->__f('Error! The registration record with uid \'%uid%\' does not contain an e-mail address.', ['%uid' => $userEntity->getUid()]));
            }
        }

        if (isset($force) && $force && $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $forceVerification = true;
        } else {
            $forceVerification = false;
        }

        $approvalOrder = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::APPROVAL_BEFORE);

        if (isset($reginfo['isverified']) && $reginfo['isverified']) {
            throw new \InvalidArgumentException($this->translator->__f('Error! A verification code cannot be sent for the registration record for \'%name%\'. It is already verified.', ['%name%' => $userEntity->getUname()]));
        } elseif (!$forceVerification && ($approvalOrder == UsersConstant::APPROVAL_BEFORE) && isset($reginfo['approvedby']) && !empty($reginfo['approved_by'])) {
            throw new \InvalidArgumentException($this->translator->__f('Error! A verification code cannot be sent for the registration record for \'%name%\'. It must first be approved.', ['%name%' => $userEntity->getUname()]));
        }

        $verificationCode = $this->userVerificationRepository->setVerificationCode($userEntity->getUid(), UsersConstant::VERIFYCHGTYPE_REGEMAIL, $userEntity->getEmail());

        $codeSent = $this->mailHelper->sendNotification($userEntity->getEmail(), 'regverifyemail', [
            'user' => $userEntity,
            'verifycode' => $verificationCode,
            'approvalorder' => $approvalOrder
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
