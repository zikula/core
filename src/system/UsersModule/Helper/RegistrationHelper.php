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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;

class RegistrationHelper
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
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var RegistrationVerificationHelper
     */
    private $verificationHelper;

    /**
     * @var MailHelper
     */
    private $mailHelper;

    /**
     * RegistrationHelper constructor.
     * @param VariableApi $variableApi
     * @param SessionInterface $session
     * @param PermissionApi $permissionApi
     * @param UserRepositoryInterface $userRepository
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface $translator
     * @param RegistrationVerificationHelper $verificationHelper
     * @param MailHelper $mailHelper
     */
    public function __construct(
        VariableApi $variableApi,
        SessionInterface $session,
        PermissionApi $permissionApi,
        UserRepositoryInterface $userRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        RegistrationVerificationHelper $verificationHelper,
        MailHelper $mailHelper
    ) {
        $this->variableApi = $variableApi;
        $this->session = $session;
        $this->permissionApi = $permissionApi;
        $this->userRepository = $userRepository;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->setTranslator($translator);
        $this->verificationHelper = $verificationHelper;
        $this->mailHelper = $mailHelper;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Create a new user or registration.
     *
     * @param UserEntity $userEntity
     * @param boolean $userMustVerify
     * @param boolean $userNotification
     * @param boolean $adminNotification
     * @param boolean $sendPassword
     *
     * @return array If the creation was unsuccessful, an array of errors is returned.
     */
    public function registerNewUser(UserEntity $userEntity, $userMustVerify = false, $userNotification = true, $adminNotification = true, $sendPassword = false)
    {
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!$userEntity->getAttributes()->contains('_Users_isVerified')) {
            $adminWantsVerification = $isAdminOrSubAdmin && $userMustVerify || '' == $userEntity->getPass();
            $isVerified = ($isAdminOrSubAdmin && !$adminWantsVerification)
                || (!$isAdminOrSubAdmin
                    && ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE) == UsersConstant::VERIFY_NO));
            $userEntity->setAttribute('_Users_isVerified', (int)$isVerified);
        }
        if (!$userEntity->isApproved()) {
            $isApproved = $isAdminOrSubAdmin
                || !$this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
            $userEntity->setApproved_By((int)$isApproved); // temp set to `1` or `0`
        }

        // Function called by admin adding user/reg, administrator created the password; no approval needed, so must need verification.
        $passwordCreatedForUser = $sendPassword ? $userEntity->getPass() : '';

        if (('' != $userEntity->getPass()) && (UsersConstant::PWD_NO_USERS_AUTHENTICATION != $userEntity->getPass())) {
            $hashedPassword = \UserUtil::getHashedPassword($userEntity->getPass());
            // DO NOT yet persist and flush the user in this method. It will occur in one of the two below methods.
            $userEntity->setPass($hashedPassword);
        }
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));

        if (!$userEntity->isApproved() || !$userEntity->isVerified()) {
            // We need a registration record
            $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);
            $userEntity->setUser_Regdate($nowUTC);
            if ($isAdminOrSubAdmin && $userEntity->isApproved()) {
                // Approved by admin
                // If self approved (moderation is off), then see below.
                $userEntity->setApproved_Date($nowUTC);
                $userEntity->setApproved_By(\UserUtil::getVar('uid'));
            }

            // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
            // registration, not a user, so a user account record has really not yet been "created".
            // The item-create hook will be fired when the registration becomes a "real" user
            // account record. This is so that modules that do default actions on the creation
            // of a user account do not perform those actions on a pending registration, which
            // may be deleted at any point.
            $this->userRepository->persistAndFlush($userEntity);
            if (!$isAdminOrSubAdmin && $userEntity->isApproved()) {
                // moderation is off, so the user "self-approved".
                // We could not set it earlier because we didn't know the uid.
                $this->userRepository->setApproved($userEntity, $nowUTC);
            }

            $this->eventDispatcher->dispatch(RegistrationEvents::CREATE_REGISTRATION, new GenericEvent($userEntity));

            return $this->createAndSendRegistrationMail($userEntity, $userNotification, $adminNotification, $passwordCreatedForUser);
        } else {
            // Everything is in order for a full user record

            // Check to see if we are getting a record directly from the registration request process, or one
            // from a later step in the registration process (e.g., approval or verification)
            if (null == $userEntity->getUid()) {
                // This is a record directly from the registration request process (never been saved before)

                // Ensure that no user gets created without a password, and that the password is reasonable (no spaces, salted)
                // or == UsersConstant::PWD_NO_USERS_AUTHENTICATION.
                $userPassword = $userEntity->getPass();
                $hasPassword = null != $userPassword;
                if ($userPassword === UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
                    $hasSaltedPassword = false;
                    $hasNoUsersAuthenticationPassword = true;
                } else {
                    $hasSaltedPassword = $hasPassword && (strpos($userPassword, UsersConstant::SALT_DELIM) != strrpos($userPassword, UsersConstant::SALT_DELIM));
                    $hasNoUsersAuthenticationPassword = false;
                }
                if (!$hasPassword || (!$hasSaltedPassword && !$hasNoUsersAuthenticationPassword)) {
                    throw new \InvalidArgumentException(__('Invalid arguments array received'));
                }

                $userEntity->setUser_Regdate($nowUTC);

                if ($isAdminOrSubAdmin) {
                    // Current user is admin, so admin is creating this registration.
                    // See below if moderation is off and user is self-approved
                    $userEntity->setApproved_By(\UserUtil::getVar('uid'));
                }
                // Approved date is set no matter what approved_by will become.
                $userEntity->setApproved_Date($nowUTC);

                // Set activated state as pending registration for now to prevent firing of update hooks after the insert until the
                // activated state is set properly further below.
                $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);

                $this->userRepository->persistAndFlush($userEntity);

                if (!$isAdminOrSubAdmin) {
                    // Current user is not admin, so moderation is off and user "self-approved" through the registration process
                    // updated approvedBy to `self` then flush
                    $this->userRepository->setApproved($userEntity, $nowUTC);
                }
            } else {
                // This is a record from intermediate step in the registration process (e.g. verification or approval)
                // delete attribute from user so that we don't get an update event. (Create hasn't happened yet.);
                $userEntity->delAttribute('_Users_isVerified');

                $this->userRepository->persistAndFlush($userEntity);
                // NOTE: See below for the firing of the item-create hook.
            }

            // Set appropriate activated status. Again, use Doctrine so we don't get an update event. (Create hasn't happened yet.)
            // Need to do this here so that it happens for both the case where $reginfo is coming in new, and the case where
            // $reginfo was already in the database.
            $userEntity->setActivated(UsersConstant::ACTIVATED_ACTIVE);
            $this->userRepository->persistAndFlush($userEntity);

            // Add user to default group
            $defaultGroup = $this->variableApi->get('ZikulaGroupsModule', 'defaultgroup', false);
            if (!$defaultGroup) {
                throw new \RuntimeException($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }
            $groupAdded = \ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'adduser', ['gid' => $defaultGroup, 'uid' => $userEntity->getUid()]);
            if (!$groupAdded) {
                throw new \RuntimeException($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }

            // ATTENTION: This is the proper place for the item-create hook, not when a pending
            // registration is created. It is not a "real" record until now, so it wasn't really
            // "created" until now. It is way down here so that the activated state can be properly
            // saved before the hook is fired.
            $this->eventDispatcher->dispatch(UserEvents::CREATE_ACCOUNT, new GenericEvent($userEntity));

            return $this->createAndSendUserMail($userEntity, $userNotification, $adminNotification, $passwordCreatedForUser);
        }
    }

    /**
     * Creates a new registration mail.
     *
     * @param UserEntity $userEntity
     * @param bool   $userNotification       Whether the user should be notified of the new registration or not; however
     *                                       if the user's password was created for him, then he will receive at
     *                                       least that mail without regard to this setting.
     * @param bool   $adminNotification      Whether the configured administrator mail e-mail address should be
     *                                       sent mail of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by an
     *                                       administrator (but not by the user himself).
     *
     * @return array of errors created from the mail process.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     * @throws \RuntimeException Thrown if the registration couldn't be saved
     */
    private function createAndSendRegistrationMail(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $mailErrors = [];
        $approvalOrder = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::APPROVAL_BEFORE);
        $rendererArgs = [];
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['admincreated'] = $this->currentUserIsAdminOrSubAdmin();

        if (!$userEntity->isVerified() && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $userEntity->isApproved())) {
            $verificationSent = $this->verificationHelper->sendVerificationCode($userEntity);
            if (!$verificationSent) {
                $mailErrors[] = $this->__('Warning! The verification code for the new registration could not be sent.');
            }
            $userObj['verificationsent'] = $verificationSent;
        } elseif (($userNotification && $userEntity->isApproved()) || !empty($passwordCreatedForUser)) {
            $mailSent = $this->mailHelper->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->__('Warning! The welcoming email for the new registration could not be sent.');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            $mailEmail = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');
            if (!empty($mailEmail)) {
                $mailSent = $this->mailHelper->sendNotification($mailEmail, 'regadminnotify', $rendererArgs);
                if (!$mailSent) {
                    $mailErrors[] = $this->__('Warning! The mail email for the new registration could not be sent.');
                }
            }
        }

        return $mailErrors;
    }

    /**
     * Creates a new users mail.
     *
     * @param UserEntity $userEntity
     * @param bool  $userNotification        Whether the user should be notified of the new registration or not;
     *                                       however if the user's password was created for him, then he will
     *                                       receive at least that mail without regard to this setting.
     * @param bool $adminNotification        Whether the configured administrator mail e-mail address should
     *                                       be sent mail of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by
     *                                       an administrator (but not by the user himself).
     *
     * @return array of mail errors
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     * @throws AccessDeniedException Thrown if the current user does not have overview access.
     * @throws \RuntimeException Thrown if the user couldn't be added to the relevant user groups or
     *                                  if the registration couldn't be saved
     */
    private function createAndSendUserMail(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $mailErrors = [];
        $rendererArgs = [];
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['admincreated'] = $this->currentUserIsAdminOrSubAdmin();

        if ($userNotification || !empty($passwordCreatedForUser)) {
            $mailSent = $this->mailHelper->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
            if (!$mailSent) {
                $mailErrors[] = $this->__('Warning! The welcoming email for the newly created user could not be sent.');
            }
        }
        if ($adminNotification) {
            // mail notify email to inform admin about registration
            $mailEmail = $this->variableApi->get('ZikulaUsersModule', 'reg_notifyemail', '');
            if (!empty($mailEmail)) {
                $subject = $this->__f('New registration: %s', $userEntity->getUname());
                $mailSent = $this->mailHelper->sendNotification($mailEmail, 'regadminnotify', $rendererArgs, $subject);
                if (!$mailSent) {
                    $mailErrors[] = $this->__('Warning! The mail email for the newly created user could not be sent.');
                }
            }
        }

        return $mailErrors;
    }

    /**
     * Returns the number of pending applications for new user accounts (registration requests).
     *
     * NOTE: Expired registrations are purged before the count is performed.
     *
     * @param array $filter An array of field/value combinations used to filter the results. Optional, default is to count all records.
     * @return integer|boolean Numer of pending applications, false on error.
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     */
    public function countAll(array $filter = [])
    {
        // activated must always be set to UsersConstant::ACTIVATED_PENDING_REG
        $filter['activated'] = UsersConstant::ACTIVATED_PENDING_REG;
        if (isset($filter['isverified'])) {
            $isVerifiedFilter = $filter['isverified'];
            unset($filter['isverified']);
        }

        $this->purgeExpired();

        if (isset($isVerifiedFilter)) {
            // TODO - Maybe can do this with a constructed SQL count select and join, but we'll do it this way for now.
            /** @var UserEntity[] $pendingRegistrationRequests */
            $pendingRegistrationRequests = $this->userRepository->query($filter);

            $count = 0;
            if (count($pendingRegistrationRequests) > 0) {
                if (!is_array($isVerifiedFilter)) {
                    $isVerifiedFilter = [
                        'operator' => '=',
                        'operand' => $isVerifiedFilter,
                    ];
                }
                // TODO - might want to error if the operator is not =, != or <>, or if the operand is not a boolean
                $isVerifiedValue = ($isVerifiedFilter['operator'] == '=') && (bool)$isVerifiedFilter['operand'];
                foreach ($pendingRegistrationRequests as $userRec) {
                    if ($userRec->getAttributeValue('_Users_isVerified') == (int)$isVerifiedValue) {
                        $count++;
                    }
                }
            }

            return $count;
        } else {
            return $this->userRepository->count($filter);
        }
    }

    /**
     * Delete a registration record.
     *
     * @param int $uid The uid of the registration record to remove
     * @return bool True on success; otherwise false.
     */
    public function remove($uid)
    {
        $user = $this->userRepository->find($uid);
        if (isset($user)) {
            $this->userRepository->removeAndFlush($user);
            $this->userVerificationRepository->resetVerifyChgFor($uid, [UsersConstant::VERIFYCHGTYPE_REGEMAIL]);
            $this->eventDispatcher->dispatch(RegistrationEvents::DELETE_REGISTRATION, new GenericEvent($user));

            return true;
        }

        return false;
    }

    /**
     * Removes expired registrations from the users table.
     */
    public function purgeExpired()
    {
        $regExpireDays = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_EXPIRE_DAYS_REGISTRATION, UsersConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION);

        if ($regExpireDays > 0) {
            $deletedUsers = $this->userVerificationRepository->purgeExpiredRecords($regExpireDays);
            foreach ($deletedUsers as $deletedUser) {
                $this->eventDispatcher->dispatch(RegistrationEvents::DELETE_REGISTRATION, new GenericEvent($deletedUser));
            }
        }
    }

    /**
     * Approves a registration.
     * If the registration is also verified (or does not need it) then a new users table recordis created.
     *
     * @param UserEntity $user
     * @param bool $force Force the approval of the registration record.
     * @return bool True on success; otherwise false.
     */
    public function approve(UserEntity $user, $force = false)
    {
        $user->setApproved_By(\UserUtil::getVar('uid'));
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $user->setApproved_Date($nowUTC);

        if ($force) {
            if (null == $user->getEmail() || '' == $user->getEmail()) {
                throw new \RuntimeException($this->translator->__f('Error: Unable to force registration for %sub% to be verified during approval. No e-mail address.', ['%sub%' => $user->getUname()]));
            }
            $user->setActivated(UsersConstant::ACTIVATED_PENDING_REG);
            $user->setAttribute('_Users_isVerified', true);
            $this->userVerificationRepository->resetVerifyChgFor($user->getUid(), [UsersConstant::VERIFYCHGTYPE_REGEMAIL]);
        }
        $this->userRepository->persistAndFlush($user);

        if ($user->isVerified()) {
            $mailErrors = $this->registerNewUser($user, false, true, false);
            if (count($mailErrors) > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     * @return bool
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return (bool)$this->session->get('uid') && $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);
    }
}
