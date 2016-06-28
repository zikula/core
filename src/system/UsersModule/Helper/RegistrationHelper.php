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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
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
     * @var CurrentUserApi
     */
    private $currentUserApi;

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
     * @var MailHelper
     */
    private $mailHelper;

    /**
     * RegistrationHelper constructor.
     * @param VariableApi $variableApi
     * @param CurrentUserApi $currentUserApi
     * @param PermissionApi $permissionApi
     * @param UserRepositoryInterface $userRepository
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface $translator
     * @param MailHelper $mailHelper
     */
    public function __construct(
        VariableApi $variableApi,
        CurrentUserApi $currentUserApi,
        PermissionApi $permissionApi,
        UserRepositoryInterface $userRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        MailHelper $mailHelper
    ) {
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->permissionApi = $permissionApi;
        $this->userRepository = $userRepository;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->setTranslator($translator);
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
     * @param boolean $userNotification
     * @param boolean $adminNotification
     * @param string $passwordCreatedForUser unhashed password or empty if do not wish to send
     * @return array If the creation was unsuccessful, an array of errors is returned.
     */
    public function registerNewUser(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $adminApprovalRequired = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
        if (null == $userEntity->getUid()) {
            $userEntity->setUser_Regdate($nowUTC);
        }
        $userCreateEvent = new GenericEvent($userEntity);
        $this->eventDispatcher->dispatch(RegistrationEvents::FULL_USER_CREATE_VETO, $userCreateEvent);
        if (($adminApprovalRequired && !$userEntity->isApproved()) || $userCreateEvent->isPropagationStopped()) {
            // We need a registration record
            $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);
            $this->userRepository->persistAndFlush($userEntity);

            // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
            // registration, not a user, so a user account record has really not yet been "created".
            // The item-create hook will be fired when the registration becomes a "real" user
            // account record. This is so that modules that do default actions on the creation
            // of a user account do not perform those actions on a pending registration, which
            // may be deleted at any point.
            $eventName = RegistrationEvents::CREATE_REGISTRATION;
            $mailMethodName = 'createAndSendRegistrationMail';
        } else {
            // Everything is in order for a full user record
            $userEntity->setActivated(UsersConstant::ACTIVATED_ACTIVE);
            $this->userRepository->persistAndFlush($userEntity);

            // Add user to default group @todo refactor with Groups module
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
            $eventName = UserEvents::CREATE_ACCOUNT;
            $mailMethodName = 'createAndSendUserMail';
        }
        if (!$adminApprovalRequired) {
            $approvedBy = $this->currentUserApi->isLoggedIn() ? $this->currentUserApi->get('uid') : $userEntity->getUid();
            $this->userRepository->setApproved($userEntity, $nowUTC, $approvedBy); // flushes EM
        }
        $this->eventDispatcher->dispatch($eventName, new GenericEvent($userEntity));

        return $this->$mailMethodName($userEntity, $userNotification, $adminNotification, $passwordCreatedForUser);
    }

    /**
     * Creates a new registration mail.
     * NOTE: called by `$this->$mailMethodName()` above. (IDE makes it appear this method is unused)
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
        $rendererArgs = [];
        $rendererArgs['reginfo'] = $userEntity;
        $rendererArgs['createdpassword'] = $passwordCreatedForUser;
        $rendererArgs['admincreated'] = $this->currentUserIsAdminOrSubAdmin();

        if (($userNotification && $userEntity->isApproved()) || !empty($passwordCreatedForUser)) {
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
     * NOTE: called by `$this->$mailMethodName()` above. (IDE makes it appear this method is unused)
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

        $this->purgeExpired();

        return $this->userRepository->count($filter);
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
     * If the registration is also verified (or does not need it) then a new users table record is created.
     *
     * @param UserEntity $user
     * @return bool True on success; otherwise false.
     */
    public function approve(UserEntity $user)
    {
        $user->setApproved_By($this->currentUserApi->get('uid'));
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $user->setApproved_Date($nowUTC);

        $user->setActivated(UsersConstant::ACTIVATED_ACTIVE);
        $this->userRepository->persistAndFlush($user);
        $this->eventDispatcher->dispatch(RegistrationEvents::FORCE_REGISTRATION_APPROVAL, new GenericEvent($user));

        $mailErrors = $this->registerNewUser($user, true, false);
        if (count($mailErrors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     * @return bool
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return $this->currentUserApi->isLoggedIn() && $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);
    }
}
