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
     * @var NotificationHelper
     */
    private $notificationHelper;

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
     * @param NotificationHelper $notificationHelper
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
        NotificationHelper $notificationHelper
    ) {
        $this->variableApi = $variableApi;
        $this->session = $session;
        $this->permissionApi = $permissionApi;
        $this->userRepository = $userRepository;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->setTranslator($translator);
        $this->verificationHelper = $verificationHelper;
        $this->notificationHelper = $notificationHelper;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Create a new user or registration.
     * @todo used in \Zikula\UsersModule\Controller\AdminController::newUserAction() NEEDS REFACTORING
     * @todo FYI used in \Zikula\UsersModule\Controller\RegistrationController::registerAction() correctly
     *
     * This is the primary and almost exclusive method for creating new user accounts, and the primary and
     * exclusive method for creating registration applications that are either pending approval, pending e-mail
     * verification, or both. 99.9% of all cases where a new user record needs to be created should use this
     * function to create the user or registration. This will ensure that all users and registrations are created
     * consistently, and that the system configuration for approval and verification is carried out correctly.
     * Only a few system-related internal edge cases should attempt to create user accounts without going through
     * this function.
     *
     * All information provided to this function is in the form of registration data, even if it is expected that
     * the end result will be a fully active user account.
     *
     * @param UserEntity $userEntity
     * @param boolean $userMustVerify
     * @param boolean $userNotification
     * @param boolean $adminNotification
     * @param boolean $sendPassword
     *
     * @return array|UserEntity If the user registration information is successfully saved (either full user record was
     *                      created or a pending registration record was created in the users table), then the UserEntity
     *                      is returned. If the creation was unsuccessful, an array of errors is returned.
     *
     * @throws \LogicException Thrown if registration is disabled.
     */
    public function registerNewUser(UserEntity $userEntity, $userMustVerify = false, $userNotification = true, $adminNotification = true, $sendPassword = false)
    {
        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!$isAdmin && !$this->variableApi->get('ZikulaUsersModule', 'reg_allowreg', false)) {
            $registrationUnavailableReason = $this->variableApi->get('ZikulaUsersModule', 'reg_noregreasons', $this->__('New user registration is currently disabled.'));
            throw new \LogicException($registrationUnavailableReason);
        }

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
            $userEntity->setPass($hashedPassword);
        }

        // @todo This forces both uname and email to all lowercase - I'm not convinced this is required nor desired.
        // maybe the uname part could be configurable. The email part is probably not needed.
        // https://tools.ietf.org/html/rfc5321#section-4.1.2 states that 'local part' of email addresses ARE case-sensitive, so foring to lowercase COULD break someone's email!
        $userEntity->setUname(mb_strtolower($userEntity->getUname()));
        $userEntity->setEmail(mb_strtolower(($userEntity->getEmail())));

        // DO NOT yet persist and flush the user in this method. It will occur in one of the two below methods.

        // Dispatch to the appropriate function, depending on whether a registration record or a full user record is needed.
        if (!$userEntity->isApproved() || !$userEntity->getAttributeValue('_Users_isVerified')) {
            // We need a registration record

            return $this->createRegistration($userEntity, $userNotification, $adminNotification, $passwordCreatedForUser);
        } else {
            // Everything is in order for a full user record

            return $this->createUser($userEntity, $userNotification, $adminNotification, $passwordCreatedForUser);
        }
    }

    /**
     * Utility method to clean up an object in preparation for storage.
     *
     * Moves any fields in the array that are not core database fields into the __ATTRIBUTES__ array.
     *
     * @param array $obj The array appropriate for the $table; passed by reference (this function will cause
     *                      the $obj to be modified in the calling function).
     *
     * @return array The $obj, modified for storage as described.
     */
    protected function cleanFieldsToAttributes(&$obj)
    {
        if (!isset($obj) || !is_array($obj)) {
            return $obj;
        }

        $user = new UserEntity();

        if (!isset($obj['__ATTRIBUTES__'])) {
            $obj['__ATTRIBUTES__'] = [];
        }

        if (isset($obj['isverified'])) {
            $obj['__ATTRIBUTES__']['_Users_isVerified'] = (int)$obj['isverified'];
            unset($obj['isverified']);
        } else {
            $obj['__ATTRIBUTES__']['_Users_isVerified'] = 0;
        }

        foreach ($obj as $field => $value) {
            if (substr($field, 0, 2) == '__') {
                continue;
            } elseif (!isset($user[$field])) {
                $obj['__ATTRIBUTES__'][$field] = is_array($value) ? serialize($value) : $value;
                unset($obj[$field]);
            }
        }

        return $obj;
    }

    /**
     * NOT A PUBLIC API !
     *
     * Creates a new registration record in the users table.
     *
     * This is an internal function that creates a new user registration. External calls to create either a new
     * registration record or a new users record are made to registerNewUser(), which
     * dispatches either this function or createUser(). registerNewUser() should be the
     * primary and exclusive function used to create either a user record or a registration, as it knows how to
     * decide which gets created based on the system configuration and the data provided.
     *
     * ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
     * registration, not a user, so a user account record has really not yet been "created".
     * The item-create hook will be fired when the registration becomes a "real" user
     * account record. This is so that modules that do default actions on the creation
     * of a user account do not perform those actions on a pending registration, which
     * may be deleted at any point.
     *
     * @param UserEntity $userEntity
     * @param bool   $userNotification       Whether the user should be notified of the new registration or not; however
     *                                       if the user's password was created for him, then he will receive at
     *                                       least that notification without regard to this setting.
     * @param bool   $adminNotification      Whether the configured administrator notification e-mail address should be
     *                                       sent notification of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by an
     *                                       administrator (but not by the user himself).
     *
     * @return array of errors created from the notification process.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     * @throws \RuntimeException Thrown if the registration couldn't be saved
     */
    protected function createRegistration(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $createdByAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
        // Just check some basic things we need directly in this function.
        if ($userEntity->isApproved() && $userEntity->getAttributeValue('_Users_isVerified')) {
            // One or the other must be false, otherwise why are we in this function?
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } elseif ((null == $userEntity->getPass()) || ('' == $userEntity->getPass()) && ($userEntity->getAttributeValue('_Users_isVerified') || !$createdByAdminOrSubAdmin)) {
            // If the password is not set (or is empty) then both isverified must be set to false AND this
            // function call must be the result of an admin or sub-admin creating the record.
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);
        $userEntity->setUser_Regdate($nowUTC);
        if ($createdByAdminOrSubAdmin && $userEntity->isApproved()) {
            // Approved by admin
            // If self approved (moderation is off), then see below.
            $userEntity->setApproved_Date($nowUTC);
            $userEntity->setApproved_By(\UserUtil::getVar('uid'));
        }

//        $userObj = $this->cleanFieldsToAttributes($userObj);
        // @todo it is possible someone is trying to set additional properties/attributes here and this would need to be accommodated.

        // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
        // registration, not a user, so a user account record has really not yet been "created".
        // The item-create hook will be fired when the registration becomes a "real" user
        // account record. This is so that modules that do default actions on the creation
        // of a user account do not perform those actions on a pending registration, which
        // may be deleted at any point.

        $this->userRepository->persistAndFlush($userEntity);

        // TODO - Even though we are not firing an item-create hook, should we fire a special
        // registration created event?

        $this->eventDispatcher->dispatch(UserEvents::CREATE_REGISTRATION, new GenericEvent($userEntity));

        $notificationErrors = [];
        if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
            $approvalOrder = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::APPROVAL_BEFORE);
            $rendererArgs = [];
            $rendererArgs['sitename'] = $this->variableApi->get(VariableApi::CONFIG, 'sitename');
            $rendererArgs['reginfo'] = $userEntity;
            $rendererArgs['createdpassword'] = $passwordCreatedForUser;
            $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
            $rendererArgs['approvalorder'] = $approvalOrder;

            if (!$userEntity->getAttributeValue('_Users_isVerified') && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $userEntity->isApproved())) {
                $verificationSent = $this->verificationHelper->sendVerificationCode($userEntity, null, null, $rendererArgs);
                if (!$verificationSent) {
                    $notificationErrors[] = $this->__('Warning! The verification code for the new registration could not be sent.');
                }
                $userObj['verificationsent'] = $verificationSent;
            } elseif (($userNotification && $userEntity->isApproved()) || !empty($passwordCreatedForUser)) {
                $notificationSent = $this->notificationHelper->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
                if (!$notificationSent) {
                    $notificationErrors[] = $this->__('Warning! The welcoming email for the new registration could not be sent.');
                }
            }
            if ($adminNotification) {
                // mail notify email to inform admin about registration
                $notificationEmail = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, '');
                if (!empty($notificationEmail)) {
                    $notificationSent = $this->notificationHelper->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs);
                    if (!$notificationSent) {
                        $notificationErrors[] = $this->__('Warning! The notification email for the new registration could not be sent.');
                    }
                }
            }
        }

        return $notificationErrors;
    }

    /**
     * NOT A PUBLIC API !
     *
     * Creates a new users table record.
     *
     * This is an internal function that creates a new user. External calls to create either a new
     * registration record or a new users record are made to registerNewUser(), which
     * dispatches either this function or createRegistration(). registerNewUser() should be the
     * primary and exclusive function used to create either a user record or a registration, as it knows how to
     * decide which gets created based on the system configuration and the data provided.
     *
     * ATTENTION: This is the proper place to fire an item-created hook for the user account
     * record, even though the physical database record may have been saved previously as a pending
     * registration. See the note in createRegistration().
     *
     * @param UserEntity $userEntity
     * @param bool  $userNotification        Whether the user should be notified of the new registration or not;
     *                                       however if the user's password was created for him, then he will
     *                                       receive at least that notification without regard to this setting.
     * @param bool $adminNotification        Whether the configured administrator notification e-mail address should
     *                                       be sent notification of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by
     *                                       an administrator (but not by the user himself).
     *
     * @return array of notification errors
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     * @throws AccessDeniedException Thrown if the current user does not have overview access.
     * @throws \RuntimeException Thrown if the user couldn't be added to the relevant user groups or
     *                                  if the registration couldn't be saved
     */
    public function createUser(UserEntity $userEntity, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $currentUserIsAdminOrSubadmin = $this->currentUserIsAdminOrSubAdmin();
        // It is only considered 'created by admin' if the UserEntity has no id yet. If it has an id, then the
        // registration record was created by an admin, but this is being created after a verification
        $createdByAdminOrSubAdmin = $currentUserIsAdminOrSubadmin && (null == $userEntity->getUid());

        // Check to see if we are getting a record directly from the registration request process, or one
        // from a later step in the registration process (e.g., approval or verification)
        if (null == $userEntity->getUid()) {
            // This is a record directly from the registration request process (never been saved before)

            // Ensure that no user gets created without a password, and that the password is reasonable (no spaces, salted)
            // If the user is being registered with an authentication method other than one from the Users module, then the
            // password will be the unsalted, unhashed string stored in UsersConstant::PWD_NO_USERS_AUTHENTICATION.
            $userPassword = $userEntity->getPass();
            $hasPassword = null != $userPassword && is_string($userEntity->getPass());
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

            $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
            $userEntity->setUser_Regdate($nowUTC);

            if ($createdByAdminOrSubAdmin) {
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

            if (!$createdByAdminOrSubAdmin) {
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

        $notificationErrors = [];

        if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
            $rendererArgs = [];
            $rendererArgs['sitename'] = $this->variableApi->get(VariableApi::CONFIG, 'sitename');
            $rendererArgs['reginfo'] = $userEntity;
            $rendererArgs['createdpassword'] = $passwordCreatedForUser;
            $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
            $rendererArgs['approvalorder'] = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::APPROVAL_BEFORE);
            $rendererArgs['PWD_NO_USERS_AUTHENTICATION'] = UsersConstant::PWD_NO_USERS_AUTHENTICATION;

            if ($userNotification || !empty($passwordCreatedForUser)) {
                $notificationSent = $this->notificationHelper->sendNotification($userEntity->getEmail(), 'welcome', $rendererArgs);
                if (!$notificationSent) {
                    $notificationErrors[] = $this->__('Warning! The welcoming email for the newly created user could not be sent.');
                }
            }
            if ($adminNotification) {
                // mail notify email to inform admin about registration
                $notificationEmail = $this->variableApi->get('ZikulaUsersModule', 'reg_notifyemail', '');
                if (!empty($notificationEmail)) {
                    $subject = $this->__f('New registration: %s', $userEntity->getUname());
                    $notificationSent = $this->notificationHelper->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs, $subject);
                    if (!$notificationSent) {
                        $notificationErrors[] = $this->__('Warning! The notification email for the newly created user could not be sent.');
                    }
                }
            }
        }

        return $notificationErrors;
    }

    /**
     * Retrieve one registration application for a new user account (one registration request).
     *
     * NOTE: Expired registrations are purged prior to performing the get.
     *
     * @param int|null $uid The uid of the registration record (registration request) to return;
     *                          required if uname and email are not specified, otherwise not allowed.
     * @param null $uname The uname of the registration record (registration request) to return;
     *                          required if id and email are not specified, otherwise not allowed.
     * @param null|string $email The e-mail address of the registration record (registration request) to return;
     *                          not allowed if the system allows an e-mail address to be registered
     *                          more than once; required if id and uname are not specified, otherwise not allowed.
     * @return array|bool An array containing the record, or false on error.
     *
     * Either id, uname, or email must be specified, but no more than one of those three, and email is not allowed
     * if the system allows an email address to be registered more than once.
     */
    public function get($uid = null, $uname = null, $email = null)
    {
        // we do not check permissions for guests here (see #1874)
        if ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $uniqueEmails = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL, true);
        // Checks the following:
        // - none of the three possible IDs is set
        // - uid is set along with either uname or email
        // - uname is set with email
        // - email is set but the system allows multiple registrations per email
        if ((!isset($uid) && !isset($uname) && !isset($email))
            || (isset($uid) && (isset($uname) || isset($email)))
            || (isset($uname) && isset($email))
            || (isset($email) && !$uniqueEmails)
        ) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $idField = 'uid';
        if (isset($uid)) {
            if (empty($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
            $idField = 'uid';
        } elseif (isset($uname)) {
            if (empty($uname) || !is_string($uname)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
            $idField = 'uname';
        } elseif (isset($email)) {
            if (empty($email) || !is_string($email)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
            $idField = 'email';
        }
        $idValue = $$idField; // intentional $$ variable variable.

        $this->purgeExpired();

        if ($idField == 'email') {
            // If reg_uniemail was ever false, or the admin created one or more users with an existing e-mail address,
            // then more than one user with the same e-mail address might exists.  The get function should not return the first
            // one it finds, as that is a security breach. It should return false, because we are not sure which one we want.
            $emailUsageCount = \UserUtil::getEmailUsageCount($idValue);
            if ($emailUsageCount > 1) {
                return false;
            }
        }

        $userObj = \UserUtil::getVars($idValue, false, $idField, true);

        if ($userObj === false) {
            throw new \RuntimeException($this->__('Error! Could not load data.'));
        }

        return $userObj;
    }

    /**
     * Retrieve all pending registration applications for a new user account (all registration requests).
     *
     * NOTE: The registration table is purged of expired records prior to retrieving results for this function.
     *
     * @param array $filter An array of field/value combinations used to filter the results. Optional, default
     *                            is to return all records.
     * @param array $orderBy An array of field name(s) by which to order the results, and the order direction. Example:
     *                            ['uname' => 'ASC'] orders by uname in ascending order.
     *                            The order direction is optional, and if not specified, the
     *                            database default is used (typically ASC). Optional, default is by id.
     * @param int $limitNumRows The number (count) of items to return.
     * @param int $limitOffset The ordinal number of the first item to return.
     * @param bool $reformatToLegacyArray @deprecated - reformat the array of objects to array of legacy user arrays with __ATTRIBUTES__
     * @return array|bool Array of registration requests, or false on failure.
     */
    public function getAll(array $filter = [], array $orderBy = ['user_regdate' => 'DESC'], $limitNumRows = 0, $limitOffset = 0, $reformatToLegacyArray = true)
    {
        if ((!(bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ))
            || ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE))) {
            throw new AccessDeniedException();
        }

        // activated must always be set to UsersConstant::ACTIVATED_PENDING_REG
        $filter['activated'] = UsersConstant::ACTIVATED_PENDING_REG;

        $this->purgeExpired();

        $pendingRegistrationRequests = $this->userRepository->query($filter, $orderBy, $limitNumRows, $limitOffset);

        // @todo - remove this reformatting back to legacy array structure and only use objects and 'attributes' (not __ATTRIBUTES__)
        if (!$reformatToLegacyArray) {
            return $pendingRegistrationRequests;
        }

        // reformat to legacy
        foreach ($pendingRegistrationRequests as $key => $userObj) {
            $userObj = $userObj->toArray();
            $attributes = [];
            foreach ($userObj['attributes'] as $attribute) {
                $attributes[$attribute['name']] = $attribute['value'];
            }
            $userObj['__ATTRIBUTES__'] = $attributes;
            unset($userObj['attributes']);
            $pendingRegistrationRequests[$key] = $userObj;
            $pendingRegistrationRequests[$key] = \UserUtil::postProcessGetRegistration($userObj);
        }

        return $pendingRegistrationRequests;
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
        if ((!(bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ))
            || ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE))) {
            return false;
        }

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
     * Processes a delete() operation for registration records.
     *
     * @param int $uid The uid of the registration record to remove; optional; if not set then $reginfo
     *                           must be set with a valid uid.
     * @param array $reginfo An array containing a registration record with a valid uid in $reginfo['uid'];
     *                           optional; if not set, then $uid must be set.
     *                      }
     * @return bool True on success; otherwise false.
     */
    public function remove($uid = null, array $reginfo = null)
    {
        if ((!(bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ))
            || ((bool)$this->session->get('uid') && !$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_DELETE))) {
            throw new AccessDeniedException();
        }

        if (isset($uid)) {
            if (empty($uid) || !is_numeric($uid)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
        } elseif (!isset($reginfo) || empty($reginfo) || !is_array($reginfo)
            || !isset($reginfo['uid']) || empty($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $uid = $reginfo['uid'];
        }

        $registration = \UserUtil::getVars($uid, true, 'uid', true);

        if (isset($registration) && $registration) {
            $user = $this->userRepository->find($uid);
            $this->userRepository->removeAndFlush($user);

            \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'resetVerifyChgFor', [
                'uid' => $uid,
                'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL,
            ]);

            $deleteEvent = new GenericEvent($registration);
            $this->eventDispatcher->dispatch(UserEvents::DELETE_REGISTRATION, $deleteEvent);

            return true;
        }

        return false;
    }

    /**
     * Removes expired registrations from the users table.
     *
     * @return void
     */
    protected function purgeExpired()
    {
        $regExpireDays = $this->variableApi->get('ZikulaUsersModule', 'reg_expiredays', 0);

        if ($regExpireDays > 0) {
            $deletedUsers = $this->userVerificationRepository->purgeExpiredRecords($regExpireDays);
            foreach ($deletedUsers as $deletedUser) {
                $deleteEvent = new GenericEvent($deletedUser);
                $this->eventDispatcher->dispatch(UserEvents::DELETE_REGISTRATION, $deleteEvent);
            }
        }
    }

    /**
     * Approves a registration.
     *
     * If the registration is also verified (or does not need it) then a new users table record
     * is created.
     *
     * @param array $reginfo An array of registration information containing a valid uid pointing to the registration
     *                           record to be approved; optional; if not set, then $uid should be set.
     * @param int $uid The uid of the registration record to be set; optional, used only if $reginfo not set; if not
     *                           set then $reginfo must be set and have a valid uid.
     * @param bool $force Force the approval of the registration record; optional; only effective if the current user
     *                           is an administrator.
     *                      }
     * @return bool True on success; otherwise false.
     */
    public function approve(array $reginfo = null, $uid = null, $force = null)
    {
        if (!$this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        if (isset($reginfo)) {
            // Got a full reginfo record
            if (!is_array($reginfo)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
                throw new \InvalidArgumentException($this->__('Error! Invalid registration record.'));
            }
        } elseif (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            // Got just an id.
            $reginfo = $this->get($uid);
            if (!$reginfo) {
                throw new \RuntimeException($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $uid));
            }
        }

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));

        $reginfo['approved_by'] = \UserUtil::getVar('uid');
        \UserUtil::setVar('approved_by', $reginfo['approved_by'], $reginfo['uid']);

        $reginfo['approved_date'] = $nowUTC->format(UsersConstant::DATETIME_FORMAT);
        \UserUtil::setVar('approved_date', $reginfo['approved_date'], $reginfo['uid']);

        $reginfo = \UserUtil::getVars($reginfo['uid'], true, 'uid', true);

        if (isset($force) && $force) {
            if (!isset($reginfo['email']) || empty($reginfo['email'])) {
                throw new \RuntimeException($this->__f('Error: Unable to force registration for \'%1$s\' to be verified during approval. No e-mail address.', [$reginfo['uname']]));
            }

            $reginfo['isverified'] = true;

            \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'resetVerifyChgFor', [
                'uid' => $reginfo['uid'],
                'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL,
            ]);
        }

        if ($reginfo['isverified']) {
            $reginfo = $this->createUser($reginfo, true, false);
        }

        return $reginfo;
    }

    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return (bool)$this->session->get('uid') && $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN);
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrative permission for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return (bool)$this->session->get('uid') && $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);
    }
}
