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
     * @param array   $reginfo {
     *     @type integer   $uid          If the information is for a new user registration, then this should not be set. Otherwise,
     *                                   the uid of the registration record.
     *     @type string    $uname        The user name for the registering user.
     *     @type string    $pass         The password for the registering user.
     *     @type string    $passreminder The password reminder for the registering user.
     *     @type string    $email        The e-mail address for the registering user.
     *     @type bool|null $isverified   This will overwrite the verification status. Do not specify to calculate
     *                                   it automatically.
     *     @type bool|null $isapproved   This will overwrite the approval status. Do not specify to calculate
     *                                   it automatically.
     *                        }
     * @param boolean $userMustVerify
     * @param boolean $userNotification
     * @param boolean $adminNotification
     * @param boolean $sendPassword
     *
     * @return array|bool If the user registration information is successfully saved (either full user record was
     *                      created or a pending registration record was created in the users table), then the array containing
     *                      the information saved is returned; false on error.
     *
     * @throws AccessDeniedException Thrown if the user does not have read access.
     * @throws \LogicException Thrown if registration is disabled.
     * @throws \InvalidArgumentException Thrown if reginfo is invalid
     */
    public function registerNewUser(array $reginfo, $userMustVerify = false, $userNotification = true, $adminNotification = true, $sendPassword = false)
    {
        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!$isAdmin && !$this->variableApi->get('ZikulaUsersModule', 'reg_allowreg', false)) {
            $registrationUnavailableReason = $this->variableApi->get('ZikulaUsersModule', 'reg_noregreasons', $this->__('New user registration is currently disabled.'));
            throw new \LogicException($registrationUnavailableReason);
        }

        if (!isset($reginfo['isverified'])) {
            $adminWantsVerification = $isAdminOrSubAdmin && $userMustVerify || !isset($reginfo['pass']) || empty($reginfo['pass']);
            $reginfo['isverified'] = ($isAdminOrSubAdmin && !$adminWantsVerification) || (!$isAdminOrSubAdmin && ($this->variableApi->get('ZikulaUsersModule', 'reg_verifyemail') == UsersConstant::VERIFY_NO));
        }
        if (!isset($reginfo['isapproved'])) {
            $reginfo['isapproved'] = $isAdminOrSubAdmin || !$this->variableApi->get('ZikulaUsersModule', 'moderation', false);
        }

        $createRegistration = !$reginfo['isapproved'] || !$reginfo['isverified'];

        if ($sendPassword) {
            // Function called by admin adding user/reg, administrator created the password; no approval needed, so must need verification.
            $passwordCreatedForUser = $reginfo['pass'];
        } else {
            $passwordCreatedForUser = '';
        }

        if (isset($reginfo['pass']) && !empty($reginfo['pass']) && ($reginfo['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
            $reginfo['pass'] = \UserUtil::getHashedPassword($reginfo['pass']);
        }

        // Dispatch to the appropriate function, depending on whether a registration record or a full user record is needed.
        if ($createRegistration) {
            // We need a registration record
            $registeredObj = $this->createRegistration($reginfo, $userNotification, $adminNotification, $passwordCreatedForUser);
        } else {
            // Everything is in order for a full user record
            $registeredObj = $this->createUser($reginfo, $userNotification, $adminNotification, $passwordCreatedForUser);
        }

        return $registeredObj;
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
     * Creates a new registration record in the users table.
     *
     * This is an internal function that creates a new user registration. External calls to create either a new
     * registration record or a new users record are made to Users_Api_Registration#registerNewUser(), which
     * dispatches either this function or createUser(). Users_Api_Registration#registerNewUser() should be the
     * primary and exclusive function used to create either a user record or a registraion, as it knows how to
     * decide which gets created based on the system configuration and the data provided.
     *
     * ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
     * registration, not a user, so a user account record has really not yet been "created".
     * The item-create hook will be fired when the registration becomes a "real" user
     * account record. This is so that modules that do default actions on the creation
     * of a user account do not perform those actions on a pending registration, which
     * may be deleted at any point.
     *
     * @param array  $reginfo                Contains the data gathered about the user for the registration record.
     * @param bool   $userNotification       Whether the user should be notified of the new registration or not; however
     *                                       if the user's password was created for him, then he will receive at
     *                                       least that notification without regard to this setting.
     * @param bool   $adminNotification      Whether the configured administrator notification e-mail address should be
     *                                       sent notification of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by an
     *                                       administrator (but not by the user himself).
     *
     * @return array|bool The registration info, as saved in the users table; false on error.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     * @throws \RuntimeException Thrown if the registration couldn't be saved
     */
    protected function createRegistration(array $reginfo, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        if (!isset($reginfo) || empty($reginfo)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $createdByAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        // @todo This forces both uname and email to all lowercase
        if (isset($reginfo['uname']) && !empty($reginfo['uname'])) {
            $reginfo['uname'] = mb_strtolower($reginfo['uname']);
        }
        if (isset($reginfo['email']) && !empty($reginfo['email'])) {
            $reginfo['email'] = mb_strtolower($reginfo['email']);
        }

        // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
        // Just check some basic things we need directly in this function.
        if (!isset($reginfo['isapproved']) || !isset($reginfo['isverified'])) {
            // Both must be set in order to determine the appropriate flags, but one or the other can be false.
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } elseif ($reginfo['isapproved'] && $reginfo['isverified']) {
            // One or the other must be false, otherwise why are we in this function?
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } elseif ((!isset($reginfo['pass']) || empty($reginfo['pass'])) && ($reginfo['isverified'] || !$createdByAdminOrSubAdmin)) {
            // If the password is not set (or is empty) then both isverified must be set to false AND this
            // function call must be the result of an admin or sub-admin creating the record.
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $approvalOrder = $this->variableApi->get('ZikulaUsersModule', 'moderation_order', UsersConstant::APPROVAL_BEFORE);

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UsersConstant::DATETIME_FORMAT);

        // Finally, save it.
        // Note that we have two objects operating here, $userObj for storage, and $reginfo with original information
        $userObj = $reginfo;

        $userObj['activated'] = UsersConstant::ACTIVATED_PENDING_REG;
        $userObj['user_regdate'] = $nowUTCStr;
        if (!isset($reginfo['isapproved']) || !$reginfo['isapproved']) {
            // Not yet approved
            $userObj['approved_by'] = 0;
        } elseif ($createdByAdminOrSubAdmin && $reginfo['isapproved']) {
            // Approved by admin
            // If self approved (moderation is off), then see below.
            $userObj['approved_date'] = $nowUTCStr;
            $userObj['approved_by'] = \UserUtil::getVar('uid');
        }

        // remove pseudo-properties.
        if (isset($userObj['isapproved'])) {
            unset($userObj['isapproved']);
        }
        if (isset($userObj['verificationsent'])) {
            unset($userObj['verificationsent']);
        }
        $userObj = $this->cleanFieldsToAttributes($userObj);

        // store user's attributes to a variable.
        // we will persist them to the database after the user record is created
        $attributes = $userObj['__ATTRIBUTES__'];
        unset($userObj['__ATTRIBUTES__']);

        // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
        // registration, not a user, so a user account record has really not yet been "created".
        // The item-create hook will be fired when the registration becomes a "real" user
        // account record. This is so that modules that do default actions on the creation
        // of a user account do not perform those actions on a pending registration, which
        // may be deleted at any point.
        $user = new UserEntity();
        $user->merge($userObj);

        // store attributes also
        foreach ($attributes as $attr_key => $attr_value) {
            $user->setAttribute($attr_key, $attr_value);
        }

        $this->userRepository->persistAndFlush($user);

        // TODO - Even though we are not firing an item-create hook, should we fire a special
        // registration created event?

        $userObj = $user->toArray();
        if ($userObj) {
            $reginfo['uid'] = $userObj['uid'];

            $regErrors = [];

            if (!$createdByAdminOrSubAdmin && $reginfo['isapproved']) {
                // moderation is off, so the user "self-approved".
                // We could not set it earlier because we didn't know the uid.
                $this->userRepository->setApproved($user, $nowUTCStr);
            }

            // Force the reload of the user in the cache.
            $userObj = \UserUtil::getVars($userObj['uid'], true, 'uid', true);

            $createEvent = new GenericEvent($userObj);
            $this->eventDispatcher->dispatch('user.registration.create', $createEvent);

            if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
                $siteurl = \System::getBaseUrl();

                $rendererArgs = [];
                $rendererArgs['sitename'] = \System::getVar('sitename');
                $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl) - 1);
                $rendererArgs['reginfo'] = $reginfo;
                $rendererArgs['createdpassword'] = $passwordCreatedForUser;
                $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
                $rendererArgs['approvalorder'] = $approvalOrder;

                if (!$reginfo['isverified'] && (($approvalOrder != UsersConstant::APPROVAL_BEFORE) || $reginfo['isapproved'])) {
                    $verificationSent = $this->verificationHelper->sendVerificationCode($reginfo, null, null, $rendererArgs);

                    if (!$verificationSent) {
                        $regErrors[] = $this->__('Warning! The verification code for the new registration could not be sent.');
                        $loggedErrorMessages = $this->session->getFlashBag()->get(\Zikula_Session::MESSAGE_ERROR);
                        $this->session->getFlashBag()->clear(\Zikula_Session::MESSAGE_ERROR);
                        foreach ($loggedErrorMessages as $lem) {
                            if (!in_array($lem, $regErrors)) {
                                $regErrors[] = $lem;
                            }
                        }
                    }
                    $userObj['verificationsent'] = $verificationSent;
                } elseif (($userNotification && $reginfo['isapproved']) || !empty($passwordCreatedForUser)) {
                    $notificationSent = $this->notificationHelper->sendNotification($reginfo['email'], 'welcome', $rendererArgs);

                    if (!$notificationSent) {
                        $regErrors[] = $this->__('Warning! The welcoming email for the new registration could not be sent.');
                        $loggedErrorMessages = $this->session->getFlashBag()->get(\Zikula_Session::MESSAGE_ERROR);
                        $this->session->getFlashBag()->clear(\Zikula_Session::MESSAGE_ERROR);
                        foreach ($loggedErrorMessages as $lem) {
                            if (!in_array($lem, $regErrors)) {
                                $regErrors[] = $lem;
                            }
                        }
                    }
                }

                if ($adminNotification) {
                    // mail notify email to inform admin about registration
                    $notificationEmail = $this->variableApi->get('ZikulaUsersModule', 'reg_notifyemail', '');
                    if (!empty($notificationEmail)) {
                        $notificationSent = $this->notificationHelper->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs);

                        if (!$notificationSent) {
                            $regErrors[] = $this->__('Warning! The notification email for the new registration could not be sent.');
                            $loggedErrorMessages = $this->session->getFlashBag()->get(\Zikula_Session::MESSAGE_ERROR);
                            $this->session->getFlashBag()->clear(\Zikula_Session::MESSAGE_ERROR);
                            foreach ($loggedErrorMessages as $lem) {
                                if (!in_array($lem, $regErrors)) {
                                    $regErrors[] = $lem;
                                }
                            }
                        }
                    }
                }
            }

            $userObj['regErrors'] = $regErrors;

            return $userObj;
        } else {
            throw new \RuntimeException($this->__('Unable to store the new user registration record.'));
        }
    }

    /**
     * NOT A PUBLIC API !
     *
     * Creates a new users table record.
     *
     * This is an internal function that creates a new user. External calls to create either a new
     * registration record or a new users record are made to Users_Api_Registration#registerNewUser(), which
     * dispatches either this function or createRegistration(). Users_Api_Registration#registerNewUser() should be the
     * primary and exclusive function used to create either a user record or a registraion, as it knows how to
     * decide which gets created based on the system configuration and the data provided.
     *
     * ATTENTION: This is the proper place to fire an item-created hook for the user account
     * record, even though the physical database record may have been saved previously as a pending
     * registration. See the note in createRegistration().
     *
     * @param array $reginfo                 Contains the data gathered about the user for the registration record.
     * @param bool  $userNotification        Whether the user should be notified of the new registration or not;
     *                                       however if the user's password was created for him, then he will
     *                                       receive at least that notification without regard to this setting.
     * @param bool $adminNotification        Whether the configured administrator notification e-mail address should
     *                                       be sent notification of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by
     *                                       an administrator (but not by the user himself).
     *
     * @return array|bool The user info, as saved in the users table; false on error.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received.
     * @throws AccessDeniedException Thrown if the current user does not have overview access.
     * @throws \RuntimeException Thrown if the user couldn't be added to the relevant user groups or
     *                                  if the registration couldn't be saved
     */
    public function createUser(array $reginfo, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        $currentUserIsAdminOrSubadmin = $this->currentUserIsAdminOrSubAdmin();

        if (!isset($reginfo) || empty($reginfo)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // It is only considered 'created by admin' if the reginfo has no id. If it has an id, then the
        // registration record was created by an admin, but this is being created after a verification
        $createdByAdminOrSubAdmin = $currentUserIsAdminOrSubadmin && !isset($reginfo['uid']);

        // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
        // Just check some basic things we need directly in this function.
        if (!isset($reginfo['email']) || empty($reginfo['email'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Check to see if we are getting a record directly from the registration request process, or one
        // from a later step in the registration process (e.g., approval or verification)
        if (!isset($reginfo['uid']) || empty($reginfo['uid'])) {
            // This is a record directly from the registration request process (never been saved before)

            // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
            // Just check some basic things we need directly in this function.
            if (!isset($reginfo['isapproved']) || empty($reginfo['isapproved'])) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }

            // Ensure that no user gets created without a password, and that the password is reasonable (no spaces, salted)
            // If the user is being registered with an authentication method other than one from the Users module, then the
            // password will be the unsalted, unhashed string stored in UsersConstant::PWD_NO_USERS_AUTHENTICATION.
            $hasPassword = isset($reginfo['pass']) && is_string($reginfo['pass']) && !empty($reginfo['pass']);
            if ($reginfo['pass'] === UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
                $hasSaltedPassword = false;
                $hasNoUsersAuthenticationPassword = true;
            } else {
                $hasSaltedPassword = $hasPassword && (strpos($reginfo['pass'], UsersConstant::SALT_DELIM) != strrpos($reginfo['pass'], UsersConstant::SALT_DELIM));
                $hasNoUsersAuthenticationPassword = false;
            }
            if (!$hasPassword || (!$hasSaltedPassword && !$hasNoUsersAuthenticationPassword)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }

            $reginfo['uname'] = mb_strtolower($reginfo['uname']);
            $reginfo['email'] = mb_strtolower($reginfo['email']);

            $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
            $nowUTCStr = $nowUTC->format(UsersConstant::DATETIME_FORMAT);

            // Finally, save it, but first get rid of some pseudo-properties
            $userObj = $reginfo;

            // Remove some pseudo-properties
            if (isset($userObj['isapproved'])) {
                unset($userObj['isapproved']);
            }
            if (isset($userObj['isverified'])) {
                unset($userObj['isverified']);
            }
            if (isset($userObj['verificationsent'])) {
                unset($userObj['verificationsent']);
            }
            $userObj = $this->cleanFieldsToAttributes($userObj);

            if (isset($userObj['__ATTRIBUTES__']['_Users_isVerified'])) {
                unset($userObj['__ATTRIBUTES__']['_Users_isVerified']);
            }

            $userObj['user_regdate'] = $nowUTCStr;

            if ($createdByAdminOrSubAdmin) {
                // Current user is admin, so admin is creating this registration.
                // See below if moderation is off and user is self-approved
                $userObj['approved_by'] = \UserUtil::getVar('uid');
            }
            // Approved date is set no matter what approved_by will become.
            $userObj['approved_date'] = $nowUTCStr;

            // Set activated state as pending registration for now to prevent firing of update hooks after the insert until the
            // activated state is set properly further below.
            $userObj['activated'] = UsersConstant::ACTIVATED_PENDING_REG;

            // store user's attributes to a variable.
            // we will persist them to the database after the user record is created
            $attributes = $userObj['__ATTRIBUTES__'];
            unset($userObj['__ATTRIBUTES__']);

            $user = new UserEntity();
            $user->merge($userObj);
            $this->userRepository->persistAndFlush($user);

            // store attributes also
            foreach ($attributes as $attr_key => $attr_value) {
                $user->setAttribute($attr_key, $attr_value);
            }

            // NOTE: See below for the firing of the item-create hook.
            $userObj = $user->toArray();

            if ($userObj) {
                if (!$createdByAdminOrSubAdmin) {
                    // Current user is not admin, so moderation is off and user "self-approved" through the registration process
                    // We couldn't do this above because we didn't know the uid.
                    $this->userRepository->setApproved($user, $nowUTCStr);
                }

                $reginfo['uid'] = $userObj['uid'];
            }
        } else {
            // This is a record from intermediate step in the registration process (e.g. verification or approval)

            // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
            // Just check some basic things we need directly in this function.
            if (!isset($reginfo['approved_by']) || empty($reginfo['approved_by'])) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }

            $userObj = $reginfo;

            $reginfo['isapproved'] = true;

            // delete attribute from user without using \UserUtil::delVar
            // so that we don't get an update event. (Create hasn't happened yet.);
            $user = $this->userRepository->find($reginfo['uid']);
            $user->delAttribute('_Users_isVerified');

            // NOTE: See below for the firing of the item-create hook.
        }

        if ($userObj) {
            // Set appropriate activated status. Again, use Doctrine so we don't get an update event. (Create hasn't happened yet.)
            // Need to do this here so that it happens for both the case where $reginfo is coming in new, and the case where
            // $reginfo was already in the database.
            $user = $this->userRepository->find($userObj['uid']);
            $user->setActivated(UsersConstant::ACTIVATED_ACTIVE);

            $userObj['activated'] = UsersConstant::ACTIVATED_ACTIVE;

            // Add user to default group
            $defaultGroup = $this->variableApi->get('ZikulaGroupsModule', 'defaultgroup', false);
            if (!$defaultGroup) {
                throw new \RuntimeException($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }
            $groupAdded = \ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'adduser', ['gid' => $defaultGroup, 'uid' => $userObj['uid']]);
            if (!$groupAdded) {
                throw new \RuntimeException($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }

            // Force the reload of the user in the cache.
            $userObj = \UserUtil::getVars($userObj['uid'], true);

            // ATTENTION: This is the proper place for the item-create hook, not when a pending
            // registration is created. It is not a "real" record until now, so it wasn't really
            // "created" until now. It is way down here so that the activated state can be properly
            // saved before the hook is fired.
            $createEvent = new GenericEvent($userObj);
            $this->eventDispatcher->dispatch('user.account.create', $createEvent);

            $regErrors = [];

            if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
                $sitename = \System::getVar('sitename');
                $siteurl = \System::getBaseUrl();
                $approvalOrder = $this->variableApi->get('ZikulaUsersModule', 'moderation_order', UsersConstant::APPROVAL_BEFORE);

                $rendererArgs = [];
                $rendererArgs['sitename'] = $sitename;
                $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl) - 1);
                $rendererArgs['reginfo'] = $reginfo;
                $rendererArgs['createdpassword'] = $passwordCreatedForUser;
                $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
                $rendererArgs['approvalorder'] = $approvalOrder;
                $rendererArgs['PWD_NO_USERS_AUTHENTICATION'] = UsersConstant::PWD_NO_USERS_AUTHENTICATION;

                if ($userNotification || !empty($passwordCreatedForUser)) {
                    $notificationSent = $this->notificationHelper->sendNotification($userObj['email'], 'welcome', $rendererArgs);

                    if (!$notificationSent) {
                        $loggedErrorMessages = $this->session->getFlashBag()->get(\Zikula_Session::MESSAGE_ERROR);
                        $this->session->getFlashBag()->clear(\Zikula_Session::MESSAGE_ERROR);
                        foreach ($loggedErrorMessages as $lem) {
                            if (!in_array($lem, $regErrors)) {
                                $regErrors[] = $lem;
                            }
                            $regErrors[] = $this->__('Warning! The welcoming email for the newly created user could not be sent.');
                        }
                    }
                }

                if ($adminNotification) {
                    // mail notify email to inform admin about registration
                    $notificationEmail = $this->variableApi->get('ZikulaUsersModule', 'reg_notifyemail', '');
                    if (!empty($notificationEmail)) {
                        $subject = $this->__f('New registration: %s', $userObj['uname']);

                        $notificationSent = $this->notificationHelper->sendNotification($notificationEmail, 'regadminnotify', $rendererArgs, $subject);

                        if (!$notificationSent) {
                            $loggedErrorMessages = $this->session->getFlashBag()->get(\Zikula_Session::MESSAGE_ERROR);
                            $this->session->getFlashBag()->clear(\Zikula_Session::MESSAGE_ERROR);
                            foreach ($loggedErrorMessages as $lem) {
                                if (!in_array($lem, $regErrors)) {
                                    $regErrors[] = $lem;
                                }
                                $regErrors[] = $this->__('Warning! The notification email for the newly created user could not be sent.');
                            }
                        }
                    }
                }
            }

            $userObj['regErrors'] = $regErrors;

            return $userObj;
        } else {
            throw new \RuntimeException($this->__('Unable to store the new user registration record.'));
        }
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

        $uniqueEmails = $this->variableApi->get('ZikulaUsersModule', 'reg_uniemail', true);
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
                    if ($userRec->getAttributes()['_Users_isVerified'] == (int)$isVerifiedValue) {
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
            $this->eventDispatcher->dispatch('user.registration.delete', $deleteEvent);

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
                $this->eventDispatcher->dispatch('user.registration.delete', $deleteEvent);
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
