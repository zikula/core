<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * The system-level and database-level functions for user-initiated actions related to new account registrations.
 */
class Users_Api_Registration extends Zikula_AbstractApi
{
    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN);
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrative permission for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT);
    }

    /**
     * Related to getRegistrationErrors, returns error information related to a new or modified password.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array  $args['reginfo']      An array containing either registration information gathered from the user, or user account
     *                                      information gathered from the user. The contents of the array that are checked by this method
     *                                      include 'uname', 'pass', 'passagain', and 'passreminder'. Optional. If not provided, then
     *                                      the individual elements should be provided on the $args array.
     * string $args['uname']        If not specified in $args['reginfo']['uname'], then the user name to be checked.
     * string $args['pass']         If not specified in $args['reginfo']['pass'], then the password to be checked.
     * string $args['passreminder'] If not specified in $args['reginfo']['passreminder'], then the password reminder to be checked.
     * string $args['passagain']    The repeated verification password entered by the user (NOTE: this is never sent in $args['reginfo']).
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return array An array of error information organized by registration form field.
     */
    public function getPasswordErrors($args)
    {
        $reginfo = array();
        if (isset($args['reginfo']) && is_array($args['reginfo'])) {
            $reginfo = $args['reginfo'];
        } else {
            if (isset($args['uname'])) {
                $reginfo['uname'] = $args['uname'];
            }
            if (isset($args['pass'])) {
                $reginfo['pass'] = $args['pass'];
            }
            if (isset($args['passreminder'])) {
                $reginfo['passreminder'] = $args['passreminder'];
            }
        }

        $passwordAgain = isset($args['passagain']) ? $args['passagain'] : '';
        $minPasswordLength = $this->getVar('minpass', 5);
        $passwordErrors = array();

        if ($reginfo['pass'] != Users_Constant::PWD_NO_USERS_AUTHENTICATION) {
            if (!isset($reginfo['pass']) || empty($reginfo['pass'])) {
                $passwordErrors['pass'] = $this->__('Please enter a password.');
            } elseif (isset($reginfo['pass']) && (strlen($reginfo['pass']) < $minPasswordLength)) {
                $passwordErrors['pass'] = $this->_fn(
                    'Your password must be at least %s character long',
                    'Your password must be at least %s characters long',
                    $minPasswordLength,
                    $minPasswordLength
                );
            } elseif (isset($reginfo['uname']) && !empty($reginfo['uname']) && ($reginfo['pass'] == $reginfo['uname'])) {
                $passwordErrors['pass'] = $this->__('The password cannot be the same as the user name. Please choose a different password.');
            } elseif (!isset($passwordAgain) || empty($passwordAgain) || ($reginfo['pass'] !== $passwordAgain)) {
                $passwordErrors['passagain'] = $this->__('You did not enter the same password in each password field. Please enter the same password once in each password field (this is required for verification).');
            }

            if (!$this->currentUserIsAdminOrSubAdmin()) {
                if (!isset($reginfo['passreminder']) || empty($reginfo['passreminder'])) {
                    $passwordErrors['passreminder'] = $this->__('Please enter a password reminder.');
                } else {
                    $testPass = mb_strtolower(trim($reginfo['pass']));
                    $testPassreminder = mb_strtolower(trim($reginfo['passreminder']));

                    if (!empty($testPass) && (strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false)) {
                        $passwordErrors['passreminder'] = $this->__('You cannot include your password in your password reminder.');
                    } else {
                        // See if they included their password with extra character in the middle--only tests if they included non alpha-numerics in the middle.
                        // Removes non-alphanumerics (mb-safe), and then checks to see that the strings are still of sufficient length to have a reasonable test.
                        $testPass = preg_replace('/[^\p{L}\p{N}]+/', '', preg_quote($testPass));
                        $testPassreminder = preg_replace('/[^\p{L}\p{N}]+/', '', preg_quote($testPassreminder));
                        if (!empty($testPass) && !empty($testPassreminder) && (strlen($testPass) >= $minPasswordLength)
                                && (strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false)) {
                            $passwordErrors['passreminder'] = $this->__('Your password reminder is too similar to your password.');
                        }
                    }
                }
            }
        }

        return $passwordErrors;
    }

    /**
     * Related to getRegistrationErrors, returns error information related to a new or modified e-mail address.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric $args['uid']        The uid of the user to be checked; optional.
     * string  $args['email']      The e-mail address to be checked.
     * string  $args['emailagain'] The repeated e-mail address entered by the user for verification.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return array An array of error information organized by registration form field.
     */
    public function getEmailErrors($args)
    {
        $reginfo = array();
        if (isset($args['uid'])) {
            $reginfo['uid'] = $args['uid'];
        }

        if (isset($args['email'])) {
            $reginfo['email'] = $args['email'];
        }

        if (isset($args['emailagain'])) {
            $emailAgain = $args['emailagain'];
        }

        if (isset($args['checkmode'])) {
            $checkMode = $args['checkmode'];
        } else {
            $checkMode = 'new';
        }

        $emailErrors = array();

        if (!isset($reginfo['email']) || empty($reginfo['email'])) {
            $emailErrors['email'] = $this->__('You must provide an e-mail address.');
        } elseif (!System::varValidate($reginfo['email'], 'email')) {
            $emailErrors['email'] = $this->__('The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons.');
        } else {
            $tempValid = true;

            $emailDomain = strstr($reginfo['email'], '@');
            if ($emailDomain) {
                if (!$this->currentUserIsAdmin()) {
                    $illegalDomains = $this->getVar('reg_Illegaldomains', '');
                    $pattern = array('/^((\s*,)*\s*)+/D', '/\b(\s*,\s*)+\b/D', '/((\s*,)*\s*)+$/D');
                    $replace = array('', '|', '');
                    $illegalDomains = preg_replace($pattern, $replace, preg_quote($illegalDomains, '/'));
                    if (!empty($illegalDomains)) {
                        if (preg_match("/@({$illegalDomains})/iD", $emailDomain)) {
                            $tempValid = false;
                            $emailErrors['email'] = $this->__('Sorry! The domain of the e-mail address you specified is banned.');
                        }
                    }
                }
            } else {
                $tempValid = false;
                $emailErrors['email'] = $this->__('The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons.');
            }


            if ($tempValid && $this->getVar(Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL, false)) {
                if ($checkMode == 'modify') {
                    $emailUsageCount = UserUtil::getEmailUsageCount($reginfo['email'], $reginfo['uid']);
                } else {
                    $emailUsageCount = UserUtil::getEmailUsageCount($reginfo['email']);
                }

                if ($emailUsageCount) {
                    $emailErrors['email'] = $this->__('The e-mail address you entered has already been registered.');
                    $tempValid = false;
                }
            }
        }

        if (!isset($emailAgain) || empty($emailAgain)) {
            $emailErrors['emailagain'] = $this->__('You did not repeat the e-mail address for verification. Please enter the same e-mail address once in each field.');
        } elseif (isset($reginfo['email']) && !empty($reginfo['email']) && ($reginfo['email'] !== $emailAgain)) {
            $emailErrors['emailagain'] = $this->__('You did not enter the same e-mail address in each e-mail address field. Please enter the same e-mail address once in each field (this is required for verification).');
        }

        return $emailErrors;
    }

    /**
     * Validate new user information entered by the user.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array  $args['reginfo']        The core registration or user information collected from the user.
     * string $args['emailagain']     The e-mail address repeated for verification.
     * string $args['passagain']      The passsword repeated for verification.
     * string $args['antispamanswer'] The answer to the antispam question provided by the user.
     * string $args['checkmode']      The "mode" that should be used when checking errors. Either 'new' or 'modify' 
     *                                      The checks that are performed depend on whether the record being checked is
     *                                      for a new record or a record being modified.
     * bool   $args['setpass']        A flag indicating whether the password is to be set on the new
     *                                      or modified record, affecting error checking.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return array An array containing errors organized by field.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user does not have read access.
     * 
     * @throws Zikula_Exception_Fatal If a required parameter is missing from $args.
     */
    public function getRegistrationErrors($args)
    {
        $registrationErrors = array();

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!isset($args['reginfo']) || !is_array($args['reginfo'])) {
            throw new Zikula_Exception_Fatal($this->__('Internal Error! Missing required parameter.'));
        }
        $reginfo = $args['reginfo'];

        // Easier to to these here....
        if (isset($reginfo['uname'])) {
            $reginfo['uname'] = mb_strtolower($reginfo['uname']);
        }
        if (isset($reginfo['email'])) {
            $reginfo['email'] = mb_strtolower($reginfo['email']);
        }

        $setPassword = ($isAdminOrSubAdmin && isset($args['setpass'])) ? $args['setpass'] : true;

        $checkMode                  = isset($args['checkmode'])     ? $args['checkmode']        : 'new';
        $emailAgain                 = isset($args['emailagain'])    ? $args['emailagain']       : '';
        $passwordAgain              = isset($args['passagain'])     ? $args['passagain']        : '';
        $spamProtectionUserAnswer   = isset($args['antispamanswer'])? $args['antispamanswer']   : '';

        if (!isset($reginfo['uname']) || empty($reginfo['uname'])) {
            $registrationErrors['uname'] = $this->__('You must provide a user name.');
        } elseif (!System::varValidate($reginfo['uname'], 'uname')) {
            $registrationErrors['uname'] = $this->__('The user name you entered contains unacceptable characters. A valid user name consists of lowercase letters, numbers, underscores, periods, and/or dashes.');
        } elseif (mb_strlen($reginfo['uname']) > Users_Constant::UNAME_VALIDATION_MAX_LENGTH) {
            $registrationErrors['uname'] = $this->__f('The user name you entered is too long. The maximum length is %1$d characters.', array(Users_Constant::UNAME_VALIDATION_MAX_LENGTH));
        } else {
            $tempValid = true;
            if (!$isAdmin) {
                $illegalUserNames = $this->getVar(Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, '');
                if (!empty($illegalUserNames)) {
                    $pattern = array('/^(\s*,\s*|\s+)+/D', '/\b(\s*,\s*|\s+)+\b/D', '/(\s*,\s*|\s+)+$/D');
                    $replace = array('', '|', '');
                    $illegalUserNames = preg_replace($pattern, $replace, preg_quote($illegalUserNames, '/'));
                    if (preg_match("/^({$illegalUserNames})/iD", $reginfo['uname'])) {
                        $registrationErrors['uname'] = $this->__('The user name you entered is reserved. It cannot be used.');
                        $tempValid = false;
                    }
                }
            }

            if ($tempValid) {
                if ($checkMode == 'modify') {
                    $unameUsageCount = UserUtil::getUnameUsageCount($reginfo['uname'], $reginfo['uid']);
                } else {
                    $unameUsageCount = UserUtil::getUnameUsageCount($reginfo['uname']);
                }

                if ($unameUsageCount) {
                    $registrationErrors['uname'] = $this->__('The user name you entered has already been registered.');
                    $tempValid = false;
                }
            }
            unset($tempValid);
        }

        $emailErrors = ModUtil::apiFunc($this->name, 'registration', 'getEmailErrors', array(
            'uid'        => isset($reginfo['uid'])        ? $reginfo['uid']        : null,
            'email'      => isset($reginfo['email'])      ? $reginfo['email']      : null,
            'emailagain' => isset($emailAgain)            ? $emailAgain            : null,
            'checkmode'  => isset($checkMode)             ? $checkMode             : null,
        ));
        if (!empty($emailErrors)) {
            $registrationErrors = array_merge($registrationErrors, $emailErrors);
        }

        $verificationAndPassword = $this->getVar(Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE, Users_Constant::VERIFY_NO);
        if ($verificationAndPassword == Users_Constant::VERIFY_SYSTEMPWD) {
            throw new Zikula_Exception_Fatal($this->__('Internal Error! System-generated passwords are no longer supported!'));
        }
        if (!$isAdminOrSubAdmin || $setPassword) {
            $passwordErrors = ModUtil::apiFunc($this->name, 'registration', 'getPasswordErrors', array(
                'reginfo'       => isset($reginfo)          ? $reginfo          : null,
                'passagain'     => isset($passwordAgain)    ? $passwordAgain    : null,
            ));

            if (!empty($passwordErrors)) {
                $registrationErrors = array_merge($registrationErrors, $passwordErrors);
            }
        }

        if (!$isAdminOrSubAdmin && ($checkMode != 'modify')) {
            $spamProtectionQuestion = $this->getVar(Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, '');
            $spamProtectionCorrectAnswer = $this->getVar(Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER, '');
            if (!empty($spamProtectionQuestion) && !empty($spamProtectionCorrectAnswer)) {
                if ($spamProtectionUserAnswer != $spamProtectionCorrectAnswer) {
                    $registrationErrors['antispamanswer'] = $this->__('You gave the wrong answer to the anti-spam registration question.');
                }
            }
        }
        
        if (isset($reginfo['theme']) && !empty($reginfo['theme'])) {
            $themeId = ThemeUtil::getIDFromName($reginfo['theme']);
            if (!$themeId) {
                $registrationErrors['theme'] = $this->__f('\'%1$s\' is not a valid theme.', array($reginfo['theme']));
            }
        }

        return !empty($registrationErrors) ? $registrationErrors : false;
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
     * Parameters passed in the $args array:
     * -------------------------------------
     * array   $args['reginfo']                 The core registration or user information collected from the user.
     * numeric $args['reginfo']['uid']          If the information is for a new user registration, then this should not be set. Otherwise,
     *                                                  the uid of the registration record.
     * string  $args['reginfo']['uname']        The user name for the registering user.
     * string  $args['reginfo']['pass']         The password for the registering user.
     * string  $args['reginfo']['passreminder'] The password reminder for the registering user.
     * string  $args['reginfo']['email']        The e-mail address for the registering user.
     * 
     * @param array $args All arguments passed to this function.
     *
     * @return array|bool If the user registration information is successfully saved (either full user record was
     *                      created or a pending registration record was created in the users table), then the array containing
     *                      the information saved is returned; false on error.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user does not have read access.
     */
    public function registerNewUser($args)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!$isAdmin && !$this->getVar('reg_allowreg', false)) {
            $registrationUnavailableReason = $this->getVar('reg_noregreasons', $this->__('New user registration is currently disabled.'));
            $this->registerError($registrationUnavailableReason, 403, System::getHomepageUrl());
            return false;
        }

        if (!isset($args['reginfo']) || empty($args['reginfo']) || !is_array($args['reginfo'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }
        $reginfo = $args['reginfo'];

        $adminWantsVerification = $isAdminOrSubAdmin && ((isset($args['usermustverify']) ? (bool)$args['usermustverify'] : false)
            || !isset($reginfo['pass']) || empty($reginfo['pass']));
        $reginfo['isverified'] = ($isAdminOrSubAdmin && !$adminWantsVerification) || (!$isAdminOrSubAdmin && ($this->getVar('reg_verifyemail') == Users_Constant::VERIFY_NO));
        $reginfo['isapproved'] = $isAdminOrSubAdmin || !$this->getVar('moderation', false);
        $createRegistration = !$reginfo['isapproved'] || !$reginfo['isverified'];

        // Notification flags
        $userNotification = isset($args['usernotification']) ? $args['usernotification'] : true;
        $adminNotification = isset($args['adminnotification']) ? $args['adminnotification'] : true;

        // Handle password
        $sendPassword = $isAdminOrSubAdmin && isset($args['sendpass']) ? $args['sendpass'] : false;

        if ($sendPassword) {
            // Function called by admin adding user/reg, administrator created the password; no approval needed, so must need verification.
            $passwordCreatedForUser = $reginfo['pass'];
        } else {
            $passwordCreatedForUser = '';
        }

        if (isset($reginfo['pass']) && !empty($reginfo['pass']) && ($reginfo['pass'] != Users_Constant::PWD_NO_USERS_AUTHENTICATION)) {
            $reginfo['pass'] = UserUtil::getHashedPassword($reginfo['pass']);
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
     * @param array &$obj The array appropriate for the $table; passed by reference (this function will cause
     *                      the $obj to be modified in the calling function).
     *
     * @return array The $obj, modified for storage as described.
     */
    protected function cleanFieldsToAttributes(&$obj)
    {
        if (!isset($obj) || !is_array($obj)) {
            return $obj;
        }

        $dbinfo = DBUtil::getTables();
        $column = $dbinfo['users_column'];
        if (!isset($column) || empty($column)) {
            return $obj;
        }

        if (!isset($obj['__ATTRIBUTES__'])) {
            $obj['__ATTRIBUTES__'] = array();
        }
        
        if (isset($obj['isverified'])) {
            $obj['__ATTRIBUTES__']['_Users_isVerified'] = $obj['isverified'];
            unset($obj['isverified']);
        } else {
            $obj['__ATTRIBUTES__']['_Users_isVerified'] = 0;
        }
        
        foreach ($obj as $field => $value) {
            if (substr($field, 0, 2) == '__') {
                continue;
            } elseif (!isset($column[$field])) {
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
     *                                          if the user's password was created for him, then he will receive at
     *                                          least that notification without regard to this setting.
     * @param bool   $adminNotification      Whether the configured administrator notification e-mail address should be
     *                                          sent notification of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by an
     *                                          administrator (but not by the user himself).
     * 
     * @return array|bool The registration info, as saved in the users table; false on error.
     *
     * @see    Users_Api_Registration#registerNewUser()
     */
    protected function createRegistration(array $reginfo, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        if (!isset($reginfo) || empty($reginfo)) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        $createdByAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

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
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        } elseif ($reginfo['isapproved'] && $reginfo['isverified']) {
            // One or the other must be false, otherwise why are we in this function?
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        } elseif ((!isset($reginfo['pass']) || empty($reginfo['pass'])) && ($reginfo['isverified'] || !$createdByAdminOrSubAdmin)) {
            // If the password is not set (or is empty) then both isverified must be set to false AND this
            // function call must be the result of an admin or sub-admin creating the record.
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);

        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(Users_Constant::DATETIME_FORMAT);

        // Finally, save it.
        // Note that we have two objects operating here, $userObj for storage, and $reginfo with original information
        $userObj = $reginfo;
        
        $userObj['activated'] = Users_Constant::ACTIVATED_PENDING_REG;
        $userObj['user_regdate'] = $nowUTCStr;
        if (!isset($reginfo['isapproved']) || !$reginfo['isapproved']) {
            // Not yet approved
            $userObj['approved_by'] = 0;
        } elseif ($createdByAdminOrSubAdmin && $reginfo['isapproved']) {
            // Approved by admin
            // If self approved (moderation is off), then see below.
            $userObj['approved_date'] = $nowUTCStr;
            $userObj['approved_by'] = UserUtil::getVar('uid');
        }

        // remove pseudo-properties.
        if (isset($userObj['isapproved'])) {
            unset($userObj['isapproved']);
        }
        if (isset($userObj['verificationsent'])) {
            unset($userObj['verificationsent']);
        }
        $userObj = $this->cleanFieldsToAttributes($userObj);

        // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
        // registration, not a user, so a user account record has really not yet been "created".
        // The item-create hook will be fired when the registration becomes a "real" user
        // account record. This is so that modules that do default actions on the creation
        // of a user account do not perform those actions on a pending registration, which
        // may be deleted at any point.
        $userObj = DBUtil::insertObject($userObj, 'users', 'uid');

        // TODO - Even though we are not firing an item-create hook, should we fire a special
        // registration created event?

        if ($userObj) {
            $reginfo['uid'] = $userObj['uid'];

            $regErrors = array();

            if (!$createdByAdminOrSubAdmin && $reginfo['isapproved']) {
                // moderation is off, so the user "self-approved".
                // We could not set it earlier because we didn't know the uid.
                // Use DBUtil here so we don't get an update event. (The create hasn't happened yet.)
                $userUpdateObj = array(
                    'uid'           => $userObj['uid'],
                    'approved_by'   => $userObj['uid'],
                    'approved_date' => $nowUTCStr,
                );
                DBUtil::updateObject($userUpdateObj, 'users', '', 'uid');
            }
            
            // Force the reload of the user in the cache.
            $userObj = UserUtil::getVars($userObj['uid'], true, 'uid', true);
            
            $createEvent = new Zikula_Event('user.registration.create', $userObj);
            $this->eventManager->notify($createEvent);

            if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
                $siteurl   = System::getBaseUrl();

                $rendererArgs = array();
                $rendererArgs['sitename'] = System::getVar('sitename');
                $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl)-1);
                $rendererArgs['reginfo'] = $reginfo;
                $rendererArgs['createdpassword'] = $passwordCreatedForUser;
                $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
                $rendererArgs['approvalorder'] = $approvalOrder;

                if (!$reginfo['isverified'] && (($approvalOrder != Users_Constant::APPROVAL_BEFORE) || $reginfo['isapproved'])) {
                    $verificationSent = ModUtil::apiFunc($this->name, 'registration', 'sendVerificationCode', array(
                        'reginfo'       => $reginfo,
                        'rendererArgs'  => $rendererArgs,
                    ));
                    if (!$verificationSent) {
                        $regErrors[] = $this->__('Warning! The verification code for the new registration could not be sent.');
                        $loggedErrorMessages = $this->request->getSession()->getMessages(Zikula_Session::MESSAGE_ERROR);
                        $this->request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
                        foreach ($loggedErrorMessages as $lem) {
                            if (!in_array($lem, $regErrors)) {
                                $regErrors[] = $lem;
                            }
                        }
                    }
                    $userObj['verificationsent'] = $verificationSent;
                } elseif (($userNotification && $reginfo['isapproved']) || !empty($passwordCreatedForUser)) {
                    $notificationSent = ModUtil::apiFunc($this->name, 'user', 'sendNotification',
                                            array('toAddress'         => $reginfo['email'],
                                                  'notificationType'  => 'welcome',
                                                  'templateArgs'      => $rendererArgs
                    ));

                    if (!$notificationSent) {
                        $regErrors[] = $this->__('Warning! The welcoming email for the new registration could not be sent.');
                        $loggedErrorMessages = $this->request->getSession()->getMessages(Zikula_Session::MESSAGE_ERROR);
                        $this->request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
                        foreach ($loggedErrorMessages as $lem) {
                            if (!in_array($lem, $regErrors)) {
                                $regErrors[] = $lem;
                            }
                        }
                    }
                }

                if ($adminNotification) {
                    // mail notify email to inform admin about registration
                    $notificationEmail = $this->getVar('reg_notifyemail', '');
                    if (!empty($notificationEmail)) {
                        $notificationSent = ModUtil::apiFunc($this->name, 'user', 'sendNotification',
                                                array('toAddress'         => $notificationEmail,
                                                      'notificationType'  => 'regadminnotify',
                                                      'templateArgs'      => $rendererArgs));

                        if (!$notificationSent) {
                            $regErrors[] = $this->__('Warning! The notification email for the new registration could not be sent.');
                            $loggedErrorMessages = $this->request->getSession()->getMessages(Zikula_Session::MESSAGE_ERROR);
                            $this->request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
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
            $this->registerError($this->__('Unable to store the new user registration record.'));
            return false;
        }
    }

    /**
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
     * @param array  $reginfo                Contains the data gathered about the user for the registration record.
     * @param bool   $userNotification       Whether the user should be notified of the new registration or not;
     *                                          however if the user's password was created for him, then he will
     *                                          receive at least that notification without regard to this setting.
     * @param bool   $adminNotification      Whether the configured administrator notification e-mail address should
     *                                          be sent notification of the new registration.
     * @param string $passwordCreatedForUser The password that was created for the user either automatically or by
     *                                          an administrator (but not by the user himself).
     *
     * @return array|bool The user info, as saved in the users table; false on error.
     *
     * @see    Users_Api_Registration#registerNewUser()
     */
    protected function createUser(array $reginfo, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
         $currentUserIsAdminOrSubadmin = $this->currentUserIsAdminOrSubAdmin();

        if (!isset($reginfo) || empty($reginfo)) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        // It is only considered 'created by admin' if the reginfo has no id. If it has an id, then the
        // registration record was created by an admin, but this is being created after a verification
        $createdByAdminOrSubAdmin = $currentUserIsAdminOrSubadmin && !isset($reginfo['uid']);

        // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
        // Just check some basic things we need directly in this function.
        if (!isset($reginfo['email']) || empty($reginfo['email'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        // Check to see if we are getting a record directly from the registration request process, or one
        // from a later step in the registration process (e.g., approval or verification)
        if (!isset($reginfo['uid']) || empty($reginfo['uid'])) {
            // This is a record directly from the registration request process (never been saved before)

            // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
            // Just check some basic things we need directly in this function.
            if (!isset($reginfo['isapproved']) || empty($reginfo['isapproved'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }

            // Ensure that no user gets created without a password, and that the password is reasonable (no spaces, salted)
            // If the user is being registered with an authentication method other than one from the Users module, then the
            // password will be the unsalted, unhashed string stored in Users_Constant::PWD_NO_USERS_AUTHENTICATION.
            $hasPassword = isset($reginfo['pass']) && is_string($reginfo['pass']) && !empty($reginfo['pass']);
            if ($reginfo['pass'] === Users_Constant::PWD_NO_USERS_AUTHENTICATION) {
                $hasSaltedPassword = false;
                $hasNoUsersAuthenticationPassword = true;
            } else {
                $hasSaltedPassord = $hasPassword && (strpos($reginfo['pass'], Users_Constant::SALT_DELIM) != strrpos($reginfo['pass'], Users_Constant::SALT_DELIM));
                $hasNoUsersAuthenticationPassword = false;
            }
            if (!$hasPassword || (!$hasSaltedPassord && !$hasNoUsersAuthenticationPassword)) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }

            $reginfo['uname'] = mb_strtolower($reginfo['uname']);
            $reginfo['email'] = mb_strtolower($reginfo['email']);

            $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
            $nowUTCStr = $nowUTC->format(Users_Constant::DATETIME_FORMAT);

            // Finally, save it, but first get rid of some pseudo-properties
            $userObj = $reginfo;
            
            // Remove some pseudo-properties
            if (isset($userObj['isapproved'])) {
                unset($userObj['isapproved']);
            }
            if (isset($userObj['isverified'])) {
                unset($userObj['isverified']);
            }
            if (isset($userObj['__ATTRIBUTES__']['_Users_isVerified'])) {
                unset($userObj['__ATTRIBUTES__']['_Users_isVerified']);
            }
            if (isset($userObj['verificationsent'])) {
                unset($userObj['verificationsent']);
            }
            $userObj = $this->cleanFieldsToAttributes($userObj);

            $userObj['user_regdate'] = $nowUTCStr;

            if ($createdByAdminOrSubAdmin) {
                // Current user is admin, so admin is creating this registration.
                // See below if moderation is off and user is self-approved
                $userObj['approved_by'] = UserUtil::getVar('uid');
            }
            // Approved date is set no matter what approved_by will become.
            $userObj['approved_date'] = $nowUTCStr;

            // Set activated state as pending registration for now to prevent firing of update hooks after the insert until the
            // activated state is set properly further below.
            $userObj['activated'] = Users_Constant::ACTIVATED_PENDING_REG;

            // NOTE: See below for the firing of the item-create hook.
            $userObj = DBUtil::insertObject($userObj, 'users', 'uid');

            if ($userObj) {
                if (!$createdByAdminOrSubAdmin) {
                    // Current user is not admin, so moderation is off and user "self-approved" through the registration process
                    // We couldn't do this above because we didn't know the uid.
                    $userUpdateObj = array(
                        'uid'           => $userObj['uid'],
                        'approved_by'   => $userObj['uid'],
                    );
                    // Use DBUtil so we don't get an update event. The create hasn't happened yet.
                    DBUtil::updateObject($userUpdateObj, 'users', '', 'uid');
                }

                $reginfo['uid'] = $userObj['uid'];
            }
        } else {
            // This is a record from intermediate step in the registration process (e.g. verification or approval)

            // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
            // Just check some basic things we need directly in this function.
            if (!isset($reginfo['approved_by']) || empty($reginfo['approved_by'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }

            $userObj = $reginfo;

            $reginfo['isapproved'] = true;

            // Use ObjectUtil so we don't get an update event. (Create hasn't happened yet.);
            ObjectUtil::deleteObjectSingleAttribute($reginfo['uid'], 'users', '_Users_isVerified');

            // NOTE: See below for the firing of the item-create hook.
        }

        if ($userObj) {
            // Set appropriate activated status. Again, use DBUtil so we don't get an update event. (Create hasn't happened yet.)
            // Need to do this here so that it happens for both the case where $reginfo is coming in new, and the case where 
            // $reginfo was already in the database.
            $userUpdateObj = array(
                'uid'       => $userObj['uid'],
                'activated' => Users_Constant::ACTIVATED_ACTIVE,
            );
            DBUtil::updateObject($userUpdateObj, 'users', '', 'uid');
            $userObj['activated'] = Users_Constant::ACTIVATED_ACTIVE;
            
            // Add user to default group
            $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup', false);
            if (!$defaultGroup) {
                $this->registerError($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }
            $groupAdded = ModUtil::apiFunc('Groups', 'user', 'adduser', array('gid' => $defaultGroup, 'uid' => $userObj['uid']));
            if (!$groupAdded) {
                $this->registerError($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }
            
            // Force the reload of the user in the cache.
            $userObj = UserUtil::getVars($userObj['uid'], true);

            // ATTENTION: This is the proper place for the item-create hook, not when a pending
            // registration is created. It is not a "real" record until now, so it wasn't really
            // "created" until now. It is way down here so that the activated state can be properly 
            // saved before the hook is fired.
            $createEvent = new Zikula_Event('user.account.create', $userObj);
            $this->eventManager->notify($createEvent);

            $regErrors = array();

            if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
                $sitename  = System::getVar('sitename');
                $siteurl   = System::getBaseUrl();
                $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);

                $rendererArgs = array();
                $rendererArgs['sitename'] = $sitename;
                $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl)-1);
                $rendererArgs['reginfo'] = $reginfo;
                $rendererArgs['createdpassword'] = $passwordCreatedForUser;
                $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
                $rendererArgs['approvalorder'] = $approvalOrder;

                if ($userNotification || !empty($passwordCreatedForUser)) {
                    $notificationSent = ModUtil::apiFunc($this->name, 'user', 'sendNotification',
                                            array('toAddress'         => $userObj['email'],
                                                  'notificationType'  => 'welcome',
                                                  'templateArgs'      => $rendererArgs));

                    if (!$notificationSent) {
                        $loggedErrorMessages = $this->request->getSession()->getMessages(Zikula_Session::MESSAGE_ERROR);
                        $this->request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
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
                    $notificationEmail = $this->getVar('reg_notifyemail', '');
                    if (!empty($notificationEmail)) {
                        $subject = $this->__f('New registration: %s', $userObj['uname']);

                        $notificationSent = ModUtil::apiFunc($this->name, 'user', 'sendNotification',
                                                array('toAddress'         => $notificationEmail,
                                                      'notificationType'  => 'regadminnotify',
                                                      'templateArgs'      => $rendererArgs,
                                                      'subject'           => $subject));

                        if (!$notificationSent) {
                            $loggedErrorMessages = $this->request->getSession()->getMessages(Zikula_Session::MESSAGE_ERROR);
                            $this->request->getSession()->clearMessages(Zikula_Session::MESSAGE_ERROR);
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
            $this->registerError($this->__('Unable to store the new user registration record.'));
            return false;
        }
    }

    /**
     * Retrieve one registration application for a new user account (one registration request).
     *
     * NOTE: Expired registrations are purged prior to performing the get.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric $args['uid']   The uid of the registration record (registration request) to return;
     *                              required if uname and email are not specified, otherwise not allowed.
     * string  $args['uname'] The uname of the registration record (registration request) to return;
     *                              required if id and email are not specified, otherwise not allowed.
     * string  $args['email'] The e-mail address of the registration record (registration request) to return;
     *                              not allowed if the system allows an e-mail address to be registered
     *                              more than once; required if id and uname are not specified, otherwise not allowed.
     * 
     * @param array $args All parameters passed to this function; either id, uname, or email must be specified, but
     *                      no more than one of those three, and email is not allowed if the system allows an email
     *                      address to be registered more than once.
     *
     * @return array|boolean An array containing the record, or false on error.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user is not logged in and does not have read access, or if the user is logged in
     *                                      and does not have moderate access.
     */
    public function get($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
                || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE))) {
            throw new Zikula_Exception_Forbidden();
        }

        $uniqueEmails = $this->getVar('reg_uniemail', false);
        // Checks the following:
        // - none of the three possible IDs is set
        // - uid is set along with either uname or email
        // - uname is set with email
        // - email is set but the system allows multiple registrations per email
        if ((!isset($args['uid']) && !isset($args['uname']) && !isset($args['email']))
                || (isset($args['uid']) && (isset($args['uname']) || isset($args['email'])))
                || (isset($args['uname']) && isset($args['email']))
                || (isset($args['email']) && !$uniqueEmails)) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        if (isset($args['uid'])) {
            if (empty($args['uid']) || !is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $idField = 'uid';
        } elseif (isset($args['uname'])) {
            if (empty($args['uname']) || !is_string($args['uname'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $idField = 'uname';
        } elseif (isset($args['email'])) {
            if (empty($args['email']) || !is_string($args['email'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $idField = 'email';
        }
        $idValue = $args[$idField];

        $this->purgeExpired();

        if ($idField == 'email') {
            // If reg_uniemail was ever false, or the admin created one or more users with an existing e-mail address,
            // then more than one user with the same e-mail address might exists.  The get function should not return the first
            // one it finds, as that is a security breach. It should return false, because we are not sure which one we want.
            $emailUsageCount = UserUtil::getEmailUsageCount($idValue);
            if ($emailUsageCount > 1) {
                return false;
            }
        }

        $userObj = UserUtil::getVars($idValue, false, $idField, true);

        if ($userObj === false) {
            $this->registerError($this->__('Error! Could not load data.'));
        }

        return $userObj;
    }

    /**
     * Constructs an SQL WHERE clause from a filter array used with getAll and countAll.
     *
     * @param array $filter The filter, see getAll() and countAll().
     *
     * @return string|bool The WHERE clause or an empty string, false on error.
     */
    protected function whereFromFilter(array $filter)
    {
        $dbinfo = DBUtil::getTables();
        $regColumn = $dbinfo['users_column'];

        $where = array();
        foreach ($filter as $field => $value) {
            if (!is_array($value)) {
                $value = array(
                    'operator'  => '=',
                    'operand'   => $value,
                );
            }

            if (preg_match('/^IS (?:NOT )?NULL/i', $value['operator'])) {
                $where[] = $regColumn[$field] . ' ' . strtoupper($value['operator']);
            } elseif (preg_match('/^(?:NOT )?IN/i', $value['operator'])) {
                if (is_null($value['operand']) || (is_array($value['operand']) && empty($value['operand']))) {
                    $where[] = $regColumn[$field] . ' ' . strtoupper($value['operator']) . ' ()';
                } else {
                    if (!is_array($value['operand'])) {
                        $value['operand'] = array($value['operand']);
                    }
                    foreach ($value['operand'] as $key => $operandItem) {
                        $value['operand'][$key] = preg_replace(array('/\\\'/', '/\\\\/'), array('\\\'', '\\\\'), $operandItem);
                    }
                    $where[] = $regColumn[$field] . ' ' . strtoupper($value['operator']) . " ('" . implode("', '", (is_array($value['operand'] ? $value['operand'] : array($value['operand'])))) . "')";
                }
            } else {
                if (is_bool($value['operand'])) {
                    $dbValue = $value['operand'] ? '1' : '0';
                } elseif (is_int($value['operand'])) {
                    $dbValue = $value['operand'];
                } else {
                    $dbValue = "'{$value['operand']}'";
                }

                $where[] = "({$regColumn[$field]} {$value['operator']} {$dbValue})";
            }
        }
        $where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        return $where;
    }

    /**
     * Retrieve all pending registration applications for a new user account (all registration requests).
     *
     * NOTE: The registration table is purged of expired records prior to retrieving results for this function.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array   $args['filter']   An array of field/value combinations used to filter the results. Optional, default
     *                                  is to return all records.
     * array   $args['orderby']  An array of field name(s) by which to order the results, and the order direction. Example:
     *                                  array('uname' => 'ASC') orders by uname in ascending order.
     *                                  The order direction is optional, and if not specified, the
     *                                  database default is used (typically ASC). Optional,
     *                                  default is by id.
     * integer $args['starnum']  The ordinal number of the first item to return.
     * integer $args['numitems'] The number (count) of items to return.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return array|bool Array of registration requests, or false on failure.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user is not logged in and does not have read access, or if the user is logged in
     *                                      and does not have moderate access.
     */
    public function getAll($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
                || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE))) {
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($args['limitoffset']) && is_numeric($args['limitoffset'])
                && ((int)$args['limitoffset'] == $args['limitoffset']) && ($args['limitoffset'] > 0)) {
            $limitOffset = $args['limitoffset'];
        } else {
            $limitOffset = -1;
        }

        if (isset($args['limitnumrows']) && is_numeric($args['limitnumrows'])
                && ((int)$args['limitnumrows'] == $args['limitnumrows']) && ($args['limitnumrows'] > 0)) {
            $limitNumRows = $args['limitnumrows'];
        } else {
            $limitNumRows = -1;
        }

        $dbinfo = DBUtil::getTables();
        $regColumn = $dbinfo['users_column'];

        $where = '';
        if (isset($args['filter'])) {
            if (!is_array($args['filter'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $args['filter']['activated'] = Users_Constant::ACTIVATED_PENDING_REG;
            $where = $this->whereFromFilter($args['filter']);
        } else {
            $where = $this->whereFromFilter(array('activated' => Users_Constant::ACTIVATED_PENDING_REG));
        }
        if ($where === false) {
            return false;
        }

        if (!isset($args['orderby'])) {
            $args['orderby'] = array(
                'user_regdate' => 'DESC',
            );
        }
        if (!is_array($args['orderby'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }
        $orderBy = array();
        foreach ($args['orderby'] as $field => $value) {
            if (is_numeric($field)) {
                $field = $value;
                $value = '';
            }
            $value = strtoupper($value);
            if (!isset($regColumn[$field]) || (!empty($value) && ($value != 'ASC') && ($value != 'DESC'))) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $orderBy[] = $regColumn[$field] . (!empty($value) ? " {$value}" : '');
        }
        $orderBy = !empty($orderBy) ? 'ORDER BY ' . implode(', ', $orderBy) : '';

        $this->purgeExpired();
        $reglist = DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows);

        if ($reglist === false) {
            $this->registerError($this->__('Error! Could not load data.'));
        } elseif (!empty($reglist)) {
            // Fix 'zero dates' and blank dates
            foreach ($reglist as $key => $userObj) {
                $reglist[$key] = UserUtil::postProcessGetRegistration($userObj);
            }
        }

        return $reglist;
    }

    /**
     * Returns the number of pending applications for new user accounts (registration requests).
     *
     * NOTE: Expired registrations are purged before the count is performed.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array $args['filter'] An array of field/value combinations used to filter the results. Optional, default
     *                              is to count all records.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return integer|boolean Numer of pending applications, false on error.
     */
    public function countAll($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
                || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE))) {
            return false;
        }

        $where = '';
        if (isset($args['filter'])) {
            if (!is_array($args['filter'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            if (isset($args['filter']['isverified'])) {
                $isVerifiedFilter = $args['filter']['isverified'];
                unset($args['filter']['isverified']);
            }
            $args['filter']['activated'] = Users_Constant::ACTIVATED_PENDING_REG;
            $where = $this->whereFromFilter($args['filter']);
        } else {
            $where = $this->whereFromFilter(array('activated' => Users_Constant::ACTIVATED_PENDING_REG));
        }
        if ($where === false) {
            return false;
        }

        $this->purgeExpired();

        if (isset($isVerifiedFilter)) {
            // TODO - Can probably do this with a constructed SQL count select and join, but we'll do it this way for now.
            if (!is_array($isVerifiedFilter)) {
                $isVerifiedFilter = array(
                    'operator'  => '=',
                    'operand'   => $isVerifiedFilter,
                );
            }
            // TODO - might want to error if the operator is not =, != or <>, or if the operand is not a boolean
            $isVerifiedValue = ($isVerifiedFilter['operator'] == '=') && (bool)$isVerifiedFilter['operand'];

            $users = DBUtil::selectObjectArray('users', $where, null, null, null, null, null, null, array('uid'));
            $count = 0;
            if ($users) {
                foreach ($users as $userRec) {
                    if ($userRec['__ATTRIBUTES__']['_Users_isVerified'] == $isVerifiedValue) {
                        $count++;
                    }
                }
            }
            return $count;
        } else {
            return DBUtil::selectObjectCount('users', $where);
        }
    }

    /**
     * Processes a delete() operation for registration records.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric $args['uid']     The uid of the registration record to remove; optional; if not set then $args['reginfo'] 
     *                                  must be set with a valid uid.
     * array   $args['reginfo'] An array containing a registration record with a valid uid in $args['reginfo']['uid']; 
     *                                  optional; if not set, then $args['uid'] must be set.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user is not logged in and does not have read access, or if the user is logged in
     *                                      and does not have moderate access.
     */
    public function remove($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
                || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE))) {
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($args['uid'])) {
            if (empty($args['uid']) || !is_numeric($args['uid'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }

            $uid = $args['uid'];
        } elseif (!isset($args['reginfo']) || empty($args['reginfo']) || !is_array($args['reginfo'])
                || !isset($args['reginfo']['uid']) || empty($args['reginfo']['uid']) || !is_numeric($args['reginfo']['uid'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        } else {
            $uid = $args['reginfo']['uid'];
        }
        
        $deleted = false;
        $registration = UserUtil::getVars($uid, true, 'uid', true);

        if (isset($registration) && $registration) {
            $deleted = DBUtil::deleteObjectByID('users', $uid, 'uid');

            if ($deleted) {
                ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
                    'uid'        => $uid,
                    'changetype' => Users_Constant::VERIFYCHGTYPE_REGEMAIL,
                ));

                $deleteEvent = new Zikula_Event('user.registration.delete', $registration);
                $this->eventManager->notify($deleteEvent);
            }
        }
        
        return $deleted;
    }

    /**
     * Removes expired registrations from the users table.
     *
     * @return void
     */
    protected function purgeExpired()
    {
        $dbinfo = DBUtil::getTables();
        $verifyChgColumn = $dbinfo['users_verifychg_column'];

        $regExpireDays = $this->getVar('reg_expiredays', 0);
        if ($regExpireDays > 0) {
            // Expiration date/times, as with all date/times in the Users module, are stored as UTC.
            $staleRecordUTC = new DateTime(null, new DateTimeZone('UTC'));
            $staleRecordUTC->modify("-{$regExpireDays} days");
            $staleRecordUTCStr = $staleRecordUTC->format(Users_Constant::DATETIME_FORMAT);

            // The zero date is there to guard against odd DB errors
            $where = "WHERE ({$verifyChgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_REGEMAIL .") "
                    . "AND ({$verifyChgColumn['created_dt']} IS NOT NULL) "
                    . "AND ({$verifyChgColumn['created_dt']} != '0000-00-00 00:00:00') "
                    . "AND ({$verifyChgColumn['created_dt']} < '{$staleRecordUTCStr}')";

            $staleVerifyChgRecs = DBUtil::selectObjectArray('users_verifychg', $where);

            if (is_array($staleVerifyChgRecs) && !empty($staleVerifyChgRecs)) {
                foreach ($staleVerifyChgRecs as $verifyChg) {
                    $registration = UserUtil::getVars($verifyChg['uid'], true, 'uid', true);
                    
                    DBUtil::deleteObjectByID('users', $verifyChg['uid'], 'uid');
                    ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
                        'uid'       => $verifyChg['uid'],
                        'changetype'=> Users_Constant::VERIFYCHGTYPE_REGEMAIL,
                    ));
                    
                    $deleteEvent = new Zikula_Event('user.registration.delete', $registration);
                    $this->eventManager->notify($deleteEvent);
                }
            }
        }
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array   $args['reginfo']      An array containing a valid registration record; optional; if not set, then $args['uid'] must 
     *                                      be set and point to a valid registration record.
     * numeric $args['uid']          The uid of a valid registration record; optional; if not set, then $args['reginfo'] must be set and valid.
     * boolean $args['force']        Indicates that a verification code should be sent, even if the Users module configuration is
     *                                      set not to verify e-mail addresses; optional; only has an effect if the current user is
     *                                      an administrator.
     * array   $args['rendererArgs'] Optional arguments to send to the Zikula_View instance while rendering the e-mail message.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user is not logged in and does not have read access, or if the user is logged in
     *                                      and does not have moderate access.
     */
    public function sendVerificationCode($args)
    {
        // In the future, it is possible we will add a feature to allow a newly registered user to resend
        // a new verification code to himself after doing a login-like process with information from  his
        // registration record, so allow not-logged-in plus READ, as well as moderator.
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
                || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE))) {
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($args['reginfo'])) {
            // Got a full reginfo record
            if (!is_array($args['reginfo'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $reginfo = $args['reginfo'];
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
        } elseif (!isset($args['uid']) || !is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        } else {
            // Got just a uid.
            $reginfo = UserUtil::getVars($args['uid'], false, 'uid', true);
            if (!$reginfo || empty($reginfo)) {
                $this->registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
                return false;
            }
            if (!isset($reginfo['email'])) {
                $this->registerError($this->__f('Error! The registration record with uid \'%1$s\' does not contain an e-mail address.', $uid));
                return false;
            }
        }

        if ($this->currentUserIsAdmin() && isset($args['force']) && $args['force']) {
            $forceVerification = true;
        } else {
            $forceVerification = false;
        }

        if (isset($args['rendererArgs']) && is_array($args['rendererArgs'])) {
            $rendererArgs = $args['rendererArgs'];
        } else {
            $rendererArgs = array();
        }

        $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);

        // Set the verification code
        if (isset($reginfo['isverified']) && $reginfo['isverified']) {
            $this->registerError($this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It is already verified.', $reginfo['uname']));
            return false;
        } elseif (!$forceVerification && ($approvalOrder == Users_Constant::APPROVAL_BEFORE) && isset($reginfo['approvedby']) && !empty($reginfo['approved_by'])) {
            $this->registerError($this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It must first be approved.', $reginfo['uname']));
            return false;
        }

        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $verificationCode = UserUtil::generatePassword();

        ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
            'uid'       => $reginfo['uid'],
            'changetype'=> Users_Constant::VERIFYCHGTYPE_REGEMAIL,
        ));

        $verifyChgObj = array(
            'changetype'=> Users_Constant::VERIFYCHGTYPE_REGEMAIL,
            'uid'       => $reginfo['uid'],
            'newemail'  => $reginfo['email'],
            'verifycode'=> UserUtil::getHashedPassword($verificationCode),
            'created_dt'=> $nowUTC->format(Users_Constant::DATETIME_FORMAT),
        );
        $verifyChgObj = DBUtil::insertObject($verifyChgObj, 'users_verifychg');

        if (!$verifyChgObj) {
            $this->registerError($this->__f('Error! Unable to save the verification code for the registration for \'%1$s\'.', $reginfo['uname']));
            return false;
        }

        if (empty($rendererArgs)) {
            $siteurl   = System::getBaseUrl();

            $rendererArgs = array();
            $rendererArgs['sitename'] = System::getVar('sitename');
            $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl)-1);
        }
        $rendererArgs['reginfo'] = $reginfo;
        $rendererArgs['verifycode'] = $verificationCode;
        $rendererArgs['approvalorder'] = $approvalOrder;

        $codeSent = ModUtil::apiFunc($this->name, 'user', 'sendNotification', array(
            'toAddress'         => $reginfo['email'],
            'notificationType'  => 'regverifyemail',
            'templateArgs'      => $rendererArgs,
        ));

        if ($codeSent) {
            return $verifyChgObj['created_dt'];
        } else {
            DBUtil::deleteObject($verifyChgObj, 'users_verifychg');
            return false;
        }
    }

    /**
     * Retrieves a verification code for a registration pending e-mail address verification.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric $args['uid'] The uid of the registration for which the code should be retrieved.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return array|bool An array containing the object from the users_verifychg table; an empty array if not found;
     *                      false on error.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user is not logged in and does not have read access, or if the user is logged in
     *                                      and does not have moderate access.
     */
    public function getVerificationCode($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
                || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE))) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!isset($args['uid']) || !is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid']) || ($args['uid'] <= 1)) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        $dbinfo = DBUtil::getTables();
        $verifyChgColumn = $dbinfo['users_verifychg_column'];
        $where = "WHERE ({$verifyChgColumn['uid']} = {$args['uid']}) AND ({$verifyChgColumn['changetype']} = "
            . Users_Constant::VERIFYCHGTYPE_REGEMAIL . ")";
        $verifyChgList = DBUtil::selectObjectArray('users_verifychg', $where, '', -1, 1);
        if (($verifyChgList === false) || !is_array($verifyChgList)) {
            $verifyChg = false;
        } elseif (!empty($verifyChgList) && is_array($verifyChgList[0]) && !empty($verifyChgList[0])) {
            $verifyChg = $verifyChgList[0];
        } else {
            $verifyChg = array();
        }

        return $verifyChg;
    }

    /**
     * Processes the results of a registration e-mail verification.
     *
     * If the registration is also approved (or does not need it) a users table record is created.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     */
    public function verify($args)
    {
        if (isset($args['reginfo'])) {
            // Got a full reginfo record
            if (!is_array($args['reginfo'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $reginfo = $args['reginfo'];
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
                $this->registerError($this->__('Error! Invalid registration record.'));
                return false;
            }
        } elseif (!isset($args['uid']) || !is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        } else {
            // Got just a uid.
            $reginfo = UserUtil::getVars($args['uid'], false, 'uid', true);
            if (!$reginfo || empty($reginfo)) {
                $this->registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
                return false;
            }
            if (!isset($reginfo['email'])) {
                $this->registerError($this->__f('Error! The registration record with uid \'%1$s\' does not contain an e-mail address.', $uid));
                return false;
            }
        }

        UserUtil::setVar('_Users_isVerified', true, $reginfo['uid']);
        ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
            'uid'       => $reginfo['uid'],
            'changetype'=> Users_Constant::VERIFYCHGTYPE_REGEMAIL,
        ));
        
        $reginfo = UserUtil::getVars($reginfo['uid'], true, 'uid', true);

        if (!empty($reginfo['approved_by'])) {
            // The registration is now both verified and approved, time to make an honest user out of him.
            $reginfo = $this->createUser($reginfo);
        }

        return $reginfo;
    }

    /**
     * Approves a registration.
     *
     * If the registration is also verified (or does not need it) then a new users table record
     * is created.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array   $args['reginfo'] An array of registration information containing a valid uid pointing to the registration 
     *                                  record to be approved; optional; if not set, then $args['uid'] should be set.
     * numeric $args['uid']     The uid of the registration record to be set; optional, used only if $args['reginfo'] not set; if not
     *                                  set then $args['reginfo'] must be set and have a valid uid.
     * boolean $args['force']   Force the approval of the registration record; optional; only effective if the current user
     *                                  is an administrator.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     * 
     * @throws Zikula_Exception_Forbidden Thrown if the user does not have add access.
     */
    public function approve($args)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($args['reginfo'])) {
            // Got a full reginfo record
            if (!is_array($args['reginfo'])) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                return false;
            }
            $reginfo = $args['reginfo'];
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['uid']) || !is_numeric($reginfo['uid'])) {
                $this->registerError($this->__('Error! Invalid registration record.'));
                return false;
            }
        } elseif (!isset($args['uid']) || !is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        } else {
            // Got just an id.
            $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $args['uid']));
            if (!$reginfo) {
                $this->registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
                return false;
            }
        }

        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));

        $reginfo['approved_by'] = UserUtil::getVar('uid');
        UserUtil::setVar('approved_by', $reginfo['approved_by'], $reginfo['uid']);

        $reginfo['approved_date'] = $nowUTC->format(Users_Constant::DATETIME_FORMAT);
        UserUtil::setVar('approved_date', $reginfo['approved_date'], $reginfo['uid']);
        
        $reginfo = UserUtil::getVars($reginfo['uid'], true, 'uid', true);

        if (isset($args['force']) && $args['force']) {
            if (!isset($reginfo['email']) || empty($reginfo['email'])) {
                $this->registerError($this->__f('Error: Unable to force registration for \'%1$s\' to be verified during approval. No e-mail address.', array($reginfo['uname'])));
                return false;
            }

            $reginfo['isverified'] = true;

            ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array(
                'uid'       => $reginfo['uid'],
                'changetype'=> Users_Constant::VERIFYCHGTYPE_REGEMAIL,
            ));
        }

        if ($reginfo['isverified']) {
            $reginfo = $this->createUser($reginfo);
        }

        return $reginfo;
    }

    /**
     * LEGACY user account activaton.
     *
     * We must keep this function because there is no way to know whether an
     * inactive account is inactive because it requires activation, or because of some
     * other reason.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string  $args['regdate'] An SQL date-time containing the user's original registration date-time.
     * numeric $args['uid']     The id of the user account to activate.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success, otherwise false.
     */
    public function activateUser($args)
    {
        // This function is an end-user function.
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return false;
        }

        // Preventing reactivation from same link !
        $newregdate = DateUtil::getDatetime(strtotime($args['regdate'])+1);
        UserUtil::setVar('activated', Users_Constant::ACTIVATED_ACTIVE, $args['uid']);
        UserUtil::setVar('user_regdate', DataUtil::formatForStore($newregdate), $args['uid']);

        return true;
    }

}
