<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

/**
 * The Register API provides system-level and database-level functions for user-initiated actions related to
 * new account registrations.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Api_Registration extends Zikula_Api
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
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD);
    }

    /**
     * Related to getRegistrationErrors, returns error information related to a new or
     * modified password.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return array An array of error information organized by registration form field.
     */
    public function getPasswordErrors($args)
    {
        $reginfo = array();
        if (isset($args['pass'])) {
            $reginfo['pass'] = $args['pass'];
        }
        if (isset($args['passreminder'])) {
            $reginfo['passreminder'] = $args['passreminder'];
        }

        $passwordAgain = isset($args['passagain']) ? $args['passagain'] : '';

        $minPasswordLength = $this->getVar('minpass', 5);

        $passwordErrors = array();

        if (!isset($reginfo['pass']) || empty($reginfo['pass'])) {
            $passwordErrors['reginfo_pass'][] = $this->__('Please enter a password.');
        } elseif (isset($reginfo['pass']) && (strlen($reginfo['pass']) < $minPasswordLength)) {
            $passwordErrors['reginfo_pass'][] = $this->_fn(
                'Your password must be at least %s character long',
                'Your password must be at least %s characters long',
                $minPasswordLength,
                $minPasswordLength
            );
        } elseif (!isset($passwordAgain) || empty($passwordAgain) || ($reginfo['pass'] !== $passwordAgain)) {
            $passwordErrors['passagain'][] = $this->__('You did not enter the same password in each password field. '
                                . 'Please enter the same password once in each password field (this is required for verification).');
        }

        if (!$this->currentUserIsAdminOrSubAdmin()) {
            if (!isset($reginfo['passreminder']) || empty($reginfo['passreminder'])) {
                $passwordErrors['reginfo_passreminder'][] = $this->__('Please enter a password reminder.');
            } else {
                $testPass = mb_strtolower(trim($reginfo['pass']));
                $testPassreminder = mb_strtolower(trim($reginfo['passreminder']));

                if ((strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false)) {
                    $passwordErrors['reginfo_passreminder'][] = $this->__('You cannot include your password in your password reminder.');
                } else {
                    // See if they included their password with extra character in the middle--only tests if they included non alpha-numerics in the middle.
                    // Removes non-alphanumerics (mb-safe), and then checks to see that the strings are still of sufficient length to have a reasonable test.
                    $testPass = preg_replace('/[^\p{L}\p{N}]+/', '', preg_quote($testPass));
                    $testPassreminder = preg_replace('/[^\p{L}\p{N}]+/', '', preg_quote($testPassreminder));
                    if (!empty($testPass) && !empty($testPassreminder) && (strlen($testPass) >= $minPasswordLength)
                        && (strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false))
                    {
                        $passwordErrors['reginfo_passreminder'][] = $this->__('Your password reminder is too similar to your password.');
                    }
                }
            }
        }

        return $passwordErrors;
    }

    /**
     * Related to getRegistrationErrors, returns error information related to a new or
     * modified e-mail address.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return array An array of error information organized by registration form field.
     */
    public function getEmailErrors($args)
    {
        $reginfo = array();
        if (isset($args['id'])) {
            $reginfo['id'] = $args['id'];
        } elseif (isset($args['uid'])) {
            $reginfo['uid'] = $args['uid'];
        }

        if (isset($args['email'])) {
            $reginfo['email'] = $args['email'];
        }

        if (isset($args['emailagain'])) {
            $emailAgain = $args['emailagain'];
        }

        if (isset($args['checkmode'])) {
            $checkMode = $args['checkMode'];
        } else {
            $checkMode = 'new';
        }

        $emailErrors = array();

        if (!isset($reginfo['email']) || empty($reginfo['email'])) {
            $emailErrors['reginfo_email'][] = $this->__('You must provide an e-mail address.');
        } elseif (!System::varValidate($reginfo['email'], 'email')) {
            $emailErrors['reginfo_email'][] = $this->__('The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons.');
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
                            $emailErrors['reginfo_email'][] = $this->__('Sorry! The domain of the e-mail address you specified is banned.');
                        }
                    }
                }
            } else {
                $tempValid = false;
                $emailErrors['reginfo_email'][] = $this->__('The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons.');
            }


            if ($tempValid && $this->getVar('reg_uniemail', false)) {
                // Probably best not to use API calls to countAll
                $ucount = DBUtil::selectObjectCountByID ('users', $reginfo['email'], 'email');
                $rcount = DBUtil::selectObjectCountByID ('users_registration', $reginfo['email'], 'email');

                if ($checkMode == 'modify') {
                    if (isset($reginfo['id']) && ($rcount == 1)) {
                        // Probably best not to use an API call
                        $duplicateRecord = DBUtil::selectObjectByID('users_registration', $reginfo['email'], 'email');
                        if ($reginfo['id'] == $duplicateRecord['id']) {
                            $rcount = 0;
                        }
                    } elseif (isset($reginfo['uid']) && ($ucount == 1)) {
                        // Probably best not to use an API call
                        $duplicateRecord = DBUtil::selectObjectByID('users', $reginfo['email'], 'email');
                        if ($reginfo['uid'] == $duplicateRecord['uid']) {
                            $ucount = 0;
                        }
                    }
                }

                if ($ucount || $rcount) {
                    $emailErrors['reginfo_email'][] = $this->__('The e-mail address you entered has already been registered.');
                    $tempValid = false;
                }
            }
        }

        if (!isset($emailAgain) || empty($emailAgain)) {
            $emailErrors['emailagain'][] = $this->__('You did not repeat the e-mail address for verification. '
                                . 'Please enter the same e-mail address once in each field.');
        } elseif (isset($reginfo['email']) && !empty($reginfo['email']) && ($reginfo['email'] !== $emailAgain)) {
            $emailErrors['emailagain'][] = $this->__('You did not enter the same e-mail address in each e-mail address field. '
                                . 'Please enter the same e-mail address once in each field (this is required for verification).');
        }

        return $emailErrors;
    }

    /**
     * Validate new user information entered by the user.
     *
     * @param array $args All parameters passed to this function.
     *                    array  $args['reginfo']
     *                    string $args['emailagain']
     *                    string $args['passagain']
     *                    string $args['antispamanswer']
     *
     * @return array An array containing errors organized by field.
     *
     */
    public function getRegistrationErrors($args)
    {
        $registrationErrors = array();

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return LogUtil::registerPermissionError(System::getHomepageUrl(), false);
        }

        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!isset($args['reginfo']) || !is_array($args['reginfo'])) {
            return z_exit($this->__('Internal Error! Missing required parameter.'));
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
            $registrationErrors['reginfo_uname'][] = $this->__('You must provide a user name.');
        } elseif (strrpos($reginfo['uname'], ' ') > 0) {
            $registrationErrors['reginfo_uname'][] = $this->__('A user name cannot contain any space characters.');
        } elseif (strlen($reginfo['uname']) > 25) {
            $registrationErrors['reginfo_uname'][] = $this->__('The user name you entered is too long. The maximum length is 25 characters.');
        } elseif (preg_match("/[[:space:]]/", $reginfo['uname']) || !System::varValidate($reginfo['uname'], 'uname')) {
            $registrationErrors['reginfo_uname'][] = $this->__('The user name you entered contains unacceptable characters.');
        } else {
            $tempValid = true;
            if (!$isAdmin) {
                // Yes, the capital I in the module var name below is required.
                $illegalUserNames = $modvars['reg_Illegalusername'];
                if (!empty($illegalUserNames)) {
                    $pattern = array('/^(\s*,\s*|\s+)+/D', '/\b(\s*,\s*|\s+)+\b/D', '/(\s*,\s*|\s+)+$/D');
                    $replace = array('', '|', '');
                    $illegalUserNames = preg_replace($pattern, $replace, preg_quote($illegalUserNames, '/'));
                    if (preg_match("/^({$illegalUserNames})/iD", $reginfo['uname'])) {
                        $registrationErrors['reginfo_uname'][] = $this->__('The user name you entered is a reserved name.');
                        $tempValid = false;
                    }
                }
            }

            if ($tempValid) {
                // Probably best not to use API calls to countAll
                $ucount = DBUtil::selectObjectCountByID ('users', $reginfo['uname'], 'uname');
                $rcount = DBUtil::selectObjectCountByID ('users_registration', $reginfo['uname'], 'uname');

                if (($checkMode == 'modify') && ($rcount == 1)) {
                    // Probably best not to use an API call
                    $duplicateRegistration = DBUtil::selectObjectByID('users_registration', $reginfo['uname'], 'uname');
                    if ($reginfo['id'] == $duplicateRegistration['id']) {
                        $rcount = 0;
                    }
                }

                if ($ucount || $rcount) {
                    $registrationErrors['reginfo_uname'][] = $this->__('The user name you entered has already been registered.');
                    $tempValid = false;
                }
            }
            unset($tempValid);
        }

        $emailErrors = ModUtil::apiFunc('Users', 'registration', 'getEmailErrors', array(
            'id'         => isset($reginfo['id'])         ? $reginfo['id']         : null,
            'email'      => isset($reginfo['email'])      ? $reginfo['email']      : null,
            'emailagain' => isset($emailAgain)            ? $emailAgain            : null,
            'checkmode'  => isset($checkMode)             ? $checkMode             : null,
        ));
        if (!empty($emailErrors)) {
            $registrationErrors = array_merge($registrationErrors, $emailErrors);
        }

        if ($checkMode != 'modify') {
            $verificationAndPassword = $this->getVar('reg_verifyemail', UserUtil::VERIFY_NO);
            if ($verificationAndPassword == UserUtil::VERIFY_SYSTEMPWD) {
                return z_exit($this->__('Internal Error! System-generated passwords are no longer supported!'));
            }
            if (($verificationAndPassword != UserUtil::VERIFY_SYSTEMPWD) && (!$isAdminOrSubAdmin || $setPassword)) {
                $passwordErrors = ModUtil::apiFunc('Users', 'registration', 'getPasswordErrors', array(
                    'pass'          => isset($reginfo['pass'])          ? $reginfo['pass']          : null,
                    'passagain'     => isset($passwordAgain)            ? $passwordAgain            : null,
                    'passreminder'  => isset($reginfo['passreminder'])  ? $reginfo['passreminder']  : null,
                ));

                if (!empty($passwordErrors)) {
                    $registrationErrors = array_merge($registrationErrors, $passwordErrors);
                }
            }
        }

        if ($checkMode != 'modify') {
            if (ModUtil::available('legal')) {
                $touInEffect = ModUtil::getVar('legal', 'termsofuse', true);
                $ppInEffect  = ModUtil::getVar('legal', 'privacypolicy', true);
                if ($touInEffect || $ppInEffect) {
                    if ($isAdminOrSubAdmin && !isset($reginfo['agreetoterms'])) {
                        $registrationErrors['reginfo_agreetoterms'][] = $this->__('The registration record must indicate whether the user agreed to the terms of use or privacy policy or not.');
                    } elseif (!$isAdminOrSubAdmin && (!isset($reginfo['agreetoterms']) || !$reginfo['agreetoterms'])) {
                        if ($touInEffect && $ppInEffect) {
                            $registrationErrors['reginfo_agreetoterms'][] = $this->__('You must agree to our terms of use and privacy policy before you can register a new account.');
                        } elseif ($touInEffect) {
                            $registrationErrors['reginfo_agreetoterms'][] = $this->__('You must agree to our terms of use before you can register a new account.');
                        } else {
                            $registrationErrors['reginfo_agreetoterms'][] = $this->__('You must agree to our privacy policy before you can register a new account.');
                        }
                    }
                }
            }
        }

        $showProfile = $this->getVar('reg_optitems', false);
        $profileModule = System::getVar('profilemodule', '');
        if ($showProfile && !empty($profileModule) && ModUtil::available($profileModule)) {
            if (isset($reginfo['dynadata'])) {
                $dynadata = $reginfo['dynadata'];
            } else {
                $dynadata = array();
            }
            if (is_array($dynadata)) {
                $dudError = ModUtil::apiFunc($profileModule, 'user', 'checkrequired', array(
                    'dynadata'  => $dynadata,
                ));

                if ($dudError) {
                    $fieldCount = count($dudError['fields']);
                    $dudError['result'] = $this->_fn(
                        'You must provide a value for the \'%s\' field in the \'Personal info\' section.',
                        'The following fields in the \'Personal info\' section are required: %s. You must provide a value for each of them.',
                        $fieldCount,
                        array($dudError['translatedFieldsStr']));
                    $registrationErrors['reginfo_dynadata'] = $dudError;
                }
            } else {
                return z_exit($this->__('Internal Error! The profile properties field(s) on the form are not constructed properly!'));
            }
        }

        if (!$isAdminOrSubAdmin && ($checkMode != 'modify')) {
            $spamProtectionQuestion = $this->getVar('reg_question', '');
            $spamProtectionCorrectAnswer = $this->getVar('reg_answer', '');
            if (!empty($spamProtectionQuestion) && !empty($spamProtectionCorrectAnswer)) {
                if ($spamProtectionUserAnswer != $spamProtectionCorrectAnswer) {
                    $registrationErrors['antispamanswer'][] = $this->__('You gave the wrong answer to the anti-spam registration question.');
                }
            }
        }

        return !empty($registrationErrors) ? $registrationErrors : false;
    }

    /**
     * Create a new user, which may yield a new user record in the users table, or may yield a new registration
     * record in the users_registration table, depending on the configuration of the system and on the registration data
     * provided.
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
     * @param array $args All arguments passed to this function.
     *                      array $args['reginfo'] The information for the new user, in the form of a registration, even
     *                                              if a fully active user is expected.
     *
     * @return array|bool If the user registration information is successfully saved (either a users table entry was
     *                      created or a pending registration created in the users_registration table), then the array containing
     *                      the information saved is returned; false on error.
     */
    public function registerNewUser($args)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!$isAdmin && !$this->getVar('reg_allowreg', false)) {
            $registrationUnavailableReason = $this->getVar('reg_noregreasons', $this->__('New user registration is currently disabled.'));
            return LogUtil::registerError($registrationUnavailableReason, 403, System::getHomepageUrl());
        }

        if (!isset($args['reginfo']) || empty($args['reginfo']) || !is_array($args['reginfo'])) {
            return LogUtil::registerArgsError();
        }
        $reginfo = $args['reginfo'];

        $adminWantsVerification = $isAdminOrSubAdmin && ((isset($args['usermustverify']) ? (bool)$args['usermustverify'] : false)
            || !isset($reginfo['pass']) || empty($reginfo['pass']));
        $reginfo['isverified'] = ($this->getVar('reg_verifyemail', UserUtil::VERIFY_NO) == UserUtil::VERIFY_NO)
            && !$adminWantsVerification;
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

        if (isset($reginfo['pass']) && !empty($reginfo['pass'])) {
            $reginfo['pass'] = UserUtil::getHashedPassword($reginfo['pass']);
        }

        LogUtil::log('reginfo = ' . var_export($reginfo, true), 'DEBUG');

        // Dispatch to the appropriate function, depending on whether a registration record or a full user record is needed.
        if ($createRegistration) {
            // We need a registration record (users_registration table)
            $registeredObj = $this->createRegistration($reginfo, $userNotification, $adminNotification, $passwordCreatedForUser);
        } else {
            // Everything is in order for a full user record (users table)
            $registeredObj = $this->createUser($reginfo, $userNotification, $adminNotification, $passwordCreatedForUser);
        }

        return $registeredObj;
    }

    /**
     * Utility method to clean up an object in preparation for storage, by moving any fields in the
     * array that are not core database fields into the __ATTRIBUTES__ array.
     *
     * @param string    $table  The name of the table to which the array will be stored.
     * @param array     $obj    The array appropriate for the $table; passed by reference (this function will cause
     *                              the $obj to be modified in the calling function).
     *
     * @return array The $obj, modified for storage as described.
     */
    protected function cleanFieldsToAttributes($table, &$obj)
    {
        if (!isset($table) || (($table != 'users') && ($table != 'users_registration')) || !isset($obj) || !is_array($obj)) {
            return $obj;
        }

        $dbinfo = DBUtil::getTables();
        $column = $dbinfo[$table.'_column'];
        if (!isset($column) || empty($column)) {
            return $obj;
        }

        if (!isset($obj['__ATTRIBUTES__'])) {
            $obj['__ATTRIBUTES__'] = array();
        }
        foreach ($obj as $field => $value) {
            if (substr($field, 0, 1) == '_') {
                continue;
            } elseif (!isset($column[$field])) {
                LogUtil::log(__CLASS__ . '#' . __FUNCTION__ . '[' . __LINE__ . '] ' . "{$table} object: cleaned '{$field}' to ['__ATTRIBUTES__']", 'DEBUG');
                $obj['__ATTRIBUTES__'][$field] = is_array($value) ? serialize($value) : $value;
                unset($obj[$field]);
            }
        }

        return $obj;
    }

    /**
     * Creates a new users_registration record.
     *
     * This is an internal function that creates a new user registration. External calls to create either a new
     * registration record or a new users record are made to Users_Api_Registration#registerNewUser(), which
     * dispatches either this function or createUser(). Users_Api_Registration#registerNewUser() should be the
     * primary and exclusive function used to create either a user record or a registraion, as it knows how to
     * decide which gets created based on the system configuration and the data provided.
     *
     * @see Users_Api_Registration#registerNewUser()
     *
     * @param array     $reginfo                    Contains the data gathered about the user for the registration record.
     * @param bool      $userNotification           Whether the user should be notified of the new registration or not; however if the
     *                                                  user's password was created for him, then he will receive at least that notification
     *                                                  without regard to this setting.
     * @param bool      $adminNotification          Whether the configured administrator notification e-mail address should be sent
     *                                                  notification of the new registration.
     * @param string    $passwordCreatedForUser     The password that was created for the user either automatically or by an administrator
     *                                                  (but not by the user himself).
     *
     * @return array|bool The registration info, as saved in the users_registration table; false on error.
     */
    protected function createRegistration(array $reginfo, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
        if (!isset($reginfo) || empty($reginfo)) {
            return LogUtil::registerArgsError();
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
            return LogUtil::registerArgsError();
        } elseif ($reginfo['isapproved'] && $reginfo['isverified']) {
            // One or the other must be false, otherwise why are we in this function?
            return LogUtil::registerArgsError();
        } elseif ((!isset($reginfo['pass']) || empty($reginfo['pass'])) && ($reginfo['isverified'] || !$createdByAdminOrSubAdmin)) {
            // If the password is not set (or is empty) then both isverified must be set to false AND this
            // function call must be the result of an admin or sub-admin creating the record.
            return LogUtil::registerArgsError();
        }

        $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);

        // Set the verification code
        if (!$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved'])) {
            $verificationCode = UserUtil::generatePassword();
            $reginfo['verifycode'] = UserUtil::getHashedPassword($verificationCode);
        }

        // Anything that is not a core field is set as an attribute.
        $reginfo = $this->cleanFieldsToAttributes('users_registration', $reginfo);

        if (!isset($reginfo['dynadata'])) {
            $reginfo['dynadata'] = array();
        }
        $reginfo['dynadata'] = serialize($reginfo['dynadata']);

        // Finally, save it.
        $reginfo = DBUtil::insertObject($reginfo, 'users_registration', 'id');

        if ($reginfo) {
            if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
                $siteurl   = System::getBaseUrl();

                $rendererArgs = array();
                $rendererArgs['sitename'] = System::getVar('sitename');
                $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl)-1);
                $rendererArgs['reginfo'] = $reginfo;
                $rendererArgs['verifycode'] = $verificationCode;
                $rendererArgs['createdpassword'] = $passwordCreatedForUser;
                $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
                $rendererArgs['approvalorder'] = $approvalOrder;

                if (!$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved'])) {
                    $verificationSent = ModUtil::apiFunc('Users', 'registration', 'sendVerificationCode', array(
                        'reginfo'       => $reginfo,
                        'rendererArgs'  => $rendererArgs,
                    ));
                    if (!$verificationSent) {
                        LogUtil::registerError($this->__('Warning! The verification code for the new registration could not be sent.'));
                    }
                } elseif (($userNotification && $reginfo['isapproved']) || !empty($passwordCreatedForUser)) {
                    ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
                        'toAddress'         => $reginfo['email'],
                        'notificationType'  => 'welcome',
                        'templateArgs'      => $rendererArgs
                    ));
                }

                if ($adminNotification) {
                    // mail notify email to inform admin about registration
                    $notificationEmail = $this->getVar('reg_notifyemail', '');
                    if (!empty($notificationEmail)) {
                        ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
                            'toAddress'         => $notificationEmail,
                            'notificationType'  => 'regadminnotify',
                            'templateArgs'      => $rendererArgs,
                        ));
                    }
                }
            }

            return $reginfo;
        } else {
            return LogUtil::registerError($this->__('Unable to store the new user registration record.'));
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
     * @see Users_Api_Registration#registerNewUser()
     *
     * @param array     $reginfo                    Contains the data gathered about the user for the registration record.
     * @param bool      $userNotification           Whether the user should be notified of the new registration or not; however if the
     *                                                  user's password was created for him, then he will receive at least that notification
     *                                                  without regard to this setting.
     * @param bool      $adminNotification          Whether the configured administrator notification e-mail address should be sent
     *                                                  notification of the new registration.
     * @param string    $passwordCreatedForUser     The password that was created for the user either automatically or by an administrator
     *                                                  (but not by the user himself).
     *
     * @return array|bool The user info, as saved in the users table; false on error.
     */
    protected function createUser(array $reginfo, $userNotification = true, $adminNotification = true, $passwordCreatedForUser = '')
    {
         $currentUserIsAdminOrSubadmin = $this->currentUserIsAdminOrSubAdmin();

        if (!isset($reginfo) || empty($reginfo)) {
            return LogUtil::registerArgsError();
        }

        // It is only considered 'created by admin' if the reginfo has no id. If it has an id, then the
        // registration record was created by an admin, but this is being created after a verification
        $createdByAdminOrSubAdmin = $currentUserIsAdminOrSubadmin && !isset($reginfo['id']);

        // Protected method (not callable from the api), so assume that the data has been validated in registerNewUser().
        // Just check some basic things we need directly in this function.
        if (isset($reginfo['isapproved'])) {
            if (!$reginfo['isapproved']) {
                // Should not be in this function if pending approval
                return LogUtil::registerArgsError();
            } else {
                // It's true, so we are in the right place.
                // Clear the field, since it is not used on the users table.
                unset($reginfo['isapproved']);
            }
        } elseif (isset($reginfo['isverified'])) {
            if (!$reginfo['isverified']) {
                // Should not be in this function if pending verification
                return LogUtil::registerArgsError();
            } else {
                // It's true, so we are in the right place.
                // Clear the field, since it is not used on the users table.
                unset($reginfo['isverified']);
            }
        }

        // Ensure that no user gets created without a password, and that the password is reasonable (no spaces, salted)
        $hasPassword = isset($reginfo['pass']) && is_string($reginfo['pass']) && !empty($reginfo['pass']);
        $hasSaltedPassord = $hasPassword && (strpos($reginfo['pass'], UserUtil::SALT_DELIM) != strrpos($reginfo['pass'], UserUtil::SALT_DELIM));
        if (!$hasPassword || !$hasSaltedPassord) {
            return LogUtil::registerArgsError();
        }

        $dbinfo = DBUtil::getTables();
        $usersColumn = $dbinfo['users_column'];
        foreach ($usersColumn as $field => $dbField) {
            if (isset($reginfo[$field])) {
                $userinfo[$field] = $reginfo[$field];
            }
        }

        if (isset($userinfo['uname']) && !empty($userinfo['uname'])) {
            $userinfo['uname'] = mb_strtolower($userinfo['uname']);
        }
        if (isset($reginfo['email']) && !empty($reginfo['email'])) {
            $userinfo['email'] = mb_strtolower($userinfo['email']);
        }

        // Set appropriate activated status
        $legalModuleAvailable = ModUtil::available('legal');
        $termsActive = $legalModuleAvailable && ModUtil::getVar('legal', 'termsofuse', true);
        $privacyActive = $legalModuleAvailable && ModUtil::getVar('legal', 'privacypolicy', true);
        $userAgreementRequired = $legalModuleAvailable && ($termsActive || $privacyActive);
        if (!$userAgreementRequired || ($userAgreementRequired && isset($reginfo['agreetoterms']) && $reginfo['agreetoterms'])) {
            $userinfo['activated'] = UserUtil::ACTIVATED_ACTIVE;
        } else {
            // $userAgreementRequired && (!isset($reginfo['agreetoterms']) || !$reginfo['agreetoterms'])
            $userinfo['activated'] = UserUtil::ACTIVATED_INACTIVE_TOUPP;
        }

        // Set a registration date/time
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $userinfo['user_regdate'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);

        // Finally, save it.
        LogUtil::log('Saving userinfo = '.var_export($userinfo, true), 'DEBUG');
        $userinfo = DBUtil::insertObject($userinfo, 'users', 'uid');

        if ($userinfo) {
            // Add user to default group
            $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup', false);
            if (!$defaultGroup) {
                LogUtil::registerError($this->__('Warning! The user account was created, but there was a problem granting access to the account.'));
            }
            $groupAdded = ModUtil::apiFunc('Groups', 'user', 'adduser', array('gid' => $defaultGroup, 'uid' => $userinfo['uid']));
            if (!$groupAdded) {
                LogUtil::registerError($this->__('Warning! The user account was created, but there was a problem granting access to the account.'));
            }

            // Process profile data
            $profileModuleName = System::getVar('profilemodule', '');
            $gatherProfileProperties = $this->getVar('reg_optitems', false);
            $profileModuleInUse = $gatherProfileProperties && !empty($profileModuleName) && ModUtil::available($profileModuleName);

            if ($profileModuleInUse && !empty($reginfo['dynadata'])) {
                $profileArgs['uid'] = $userinfo['uid'];
                $profileArgs['dynadata'] = $reginfo['dynadata'];
                $profileData = ModUtil::apiFunc($profileModuleName, 'user', 'insertDyndata', $profileArgs);

                if ($profileData && is_array($profileData)) {
                    // From array_merge: If the input arrays have the same string keys, then the later value for that key will overwrite the previous one.
                    // We want profileData to overwrite userinfo if __ATTRIBUTES__ is returned. The assumption is that the profile module will get the
                    // existing attributes and merge with its data before it returns here.
                    // Note that if the profile module removes attributes for some reason, then they will no longer be on the $userinfo object after this,
                    // but they will have been saved by DBUtil::insertObject().
                    $userinfo = array_merge($userinfo, $profileData);
                } else {
                    LogUtil::registerError($this->__('Warning! The new user was created, but the additional profile module properties were not saved.'));
                }
            }

            if ($adminNotification || $userNotification || !empty($passwordCreatedForUser)) {
                $sitename  = System::getVar('sitename');
                $siteurl   = System::getBaseUrl();
                $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);

                $rendererArgs = array();
                $rendererArgs['sitename'] = $sitename;
                $rendererArgs['siteurl'] = substr($siteurl, 0, strlen($siteurl)-1);
                $rendererArgs['reginfo'] = $reginfo;
                $rendererArgs['createdpassword'] = $passwordCreatedForUser;
                $rendererArgs['admincreated'] = $createdByAdminOrSubAdmin;
                $rendererArgs['approvalorder'] = $approvalOrder;

                if ($userNotification || !empty($passwordCreatedForUser)) {
                    ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
                        'toAddress'         => $userinfo['email'],
                        'notificationType'  => 'welcome',
                        'templateArgs'      => $rendererArgs
                    ));
                }

                if ($adminNotification) {
                    // mail notify email to inform admin about registration
                    $notificationEmail = $this->getVar('reg_notifyemail', '');
                    if (!empty($notificationEmail)) {
                        $subject = $this->__('New registration: %s', $userinfo['uname']);
                        ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
                            'toAddress'         => $notificationEmail,
                            'notificationType'  => 'regadminnotify',
                            'templateArgs'      => $rendererArgs,
                            'subject'           => $subject,
                        ));
                    }
                }
            }

            return $userinfo;
        } else {
            return LogUtil::registerError($this->__('Unable to store the new user registration record.'));
        }
    }

    /**
     * Retrieve one registration application for a new user account (one registration request). Expired
     * registrations are purged prior to performing the get.
     *
     * @param array $args All parameters passed to this function; either id, uname, or email must be specified, but
     *                      no more than one of those three, and email is not allowed if the system allows an email
     *                      address to be registered more than once.
     *                    numeric $args['id']       The id of the registration record (registration request) to return;
     *                                                  required if uname and email are not specified, otherwise not allowed.
     *                    string  $args['uname']    The uname of the registration record (registration request) to return;
     *                                                  required if id and email are not specified, otherwise not allowed.
     *                    string  $args['email']    The e-mail address of the registration record (registration request) to return;
     *                                                  not allowed if the system allows an e-mail address to be registered
     *                                                  more than once; required if id and uname are not specified, otherwise not allowed.
     *
     * @return array|bool An array containing the record, or false on error.
     */
    public function get($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
            || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)))
        {
            return LogUtil::registerPermissionError();
        }

        $uniqueEmails = $this->getVar('reg_uniemail', false);
        // Checks the following:
        // - none of the three possible IDs is set
        // - id is set along with either uname or email
        // - uname is set with email
        // - email is set but the system allows multiple registrations per email
        if ((!isset($args['id']) && !isset($args['uname']) && !isset($args['email']))
            || (isset($args['id']) && (isset($args['uname']) || isset($args['email'])))
            || (isset($args['uname']) && isset($args['email']))
            || (isset($args['email']) && !$uniqueEmails)
            )
        {
            return LogUtil::registerArgsError();
        }

        if (isset($args['id'])) {
            if (empty($args['id']) || !is_numeric($args['id']) || ((int)$args['id'] != $args['id'])) {
                return LogUtil::registerArgsError ();
            }
            $idField = 'id';
        } elseif (isset($args['uname'])) {
            if (empty($args['uname']) || !is_string($args['uname'])) {
                return LogUtil::registerArgsError ();
            }
            $idField = 'uname';
        } elseif (isset($args['email'])) {
            if (empty($args['email']) || !is_string($args['email'])) {
                return LogUtil::registerArgsError ();
            }
            $idField = 'email';
        }
        $idValue = $args[$idField];

        $this->purgeExpired();

        if ($idField == 'email') {
            // If reg_uniemail was ever false, or the admin created one or more users with an existing e-mail address,
            // then more than one user with the same e-mail address might exists.  The get function should not return the first
            // one it finds, as that is a security breach. It should return false, because we are not sure which one we want.
            $rcount = ModUtil::apiFunc('Users', 'registration', 'countAll', array('filter' => array('uname' => $idValue)));
            if ($rcount > 1) {
                return false;
            }
        }

        $reginfo = DBUtil::selectObjectByID('users_registration', $idValue, $idField);

        if ($reginfo === false) {
            LogUtil::registerError($this->__('Error! Could not load data.'));
        } else {
            // Fix 'zero date' and empty date
            if (isset($reginfo['validuntil']) && (empty($reginfo['validuntil']) || ($reginfo['validuntil'] == '0000-00-00 00:00:00'))) {
                unset($reginfo['validuntil']);
            }
            // unserialize dynadata
            $reginfo['dynadata'] = unserialize($reginfo['dynadata']);
        }

        return $reginfo;
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
        $regColumn = $dbinfo['users_registration_column'];

        $where = array();
        foreach ($args['filter'] as $field => $value) {
            if (is_bool($value)) {
                $dbValue = $value ? '1' : '0';
            } elseif (is_int($value)) {
                $dbValue = $value;
            } else {
                $dbValue = "'{$value}'";
            }
            $where[] = "({$regColumn[$field]} = {$dbValue})";
        }
        $where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        return $where;
    }

    /**
     * Retrieve all pending registration applications for a new user account (all registration requests). Note: The
     * registration table is purged of expired records prior to retrieving results for this function.
     *
     * @param array $args All parameters passed to this function.
     *                      array $args['filter']   An array of field/value combinations used to filter the results. Optional, default
     *                                                  is to return all records. For example,
     *                                                  array('isapproved' => true, 'isverified' => false) will filter and return
     *                                                  only those registrations that are approved and waiting for e-mail verification.
     *                      array $args['orderby']  An array of field name(s) by which to order the results, and the order direction. Example:
     *                                                  array('isapproved' => 'DESC', 'uname' => 'ASC') orders by the approval status
     *                                                  in descending order (approved first--1 in the database, then not approved--0 in the
     *                                                  database), and then by uname in ascending order. The order direction is optional,
     *                                                  and if not specified, the database default is used (typically ASC). Optional,
     *                                                  default is by id.
     *                      int   $args['starnum']  The ordinal number of the first item to return.
     *                      int   $args['numitems'] The number (count) of items to return.
     *
     * @return array|bool Array of registration requests, or false on failure.
     */
    public function getAll($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
            || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)))
        {
            return LogUtil::registerPermissionError();
        }

        if (isset($args['limitoffset']) && is_numeric($args['limitoffset'])
            && ((int)$args['limitoffset'] == $args['limitoffset']) && ($args['limitoffset'] > 0))
        {
            $limitOffset = $args['limitoffset'];
        } else {
            $limitOffset = -1;
        }

        if (isset($args['limitnumrows']) && is_numeric($args['limitnumrows'])
            && ((int)$args['limitnumrows'] == $args['limitnumrows']) && ($args['limitnumrows'] > 0))
        {
            $limitNumRows = $args['limitnumrows'];
        } else {
            $limitNumRows = -1;
        }

        $dbinfo = DBUtil::getTables();
        $regColumn = $dbinfo['users_registration_column'];

        $where = '';
        if (isset($args['filter'])) {
            if (!is_array($args['filter'])) {
                return LogUtil::registerArgsError();
            }

            $where = $this->whereFromFilter($args['filter']);
            if ($where === false) {
                return false;
            }
        }

        if (isset($args['orderby'])) {
            if (!is_array($args['orderby'])) {
                return LogUtil::registerArgsError();
            }

            $orderBy = array();
            foreach ($args['orderby'] as $field => $value) {
                if (is_numeric($field)) {
                    $field = $value;
                    $value = '';
                }
                $value = strtoupper($value);
                if (!isset($regColumn[$field]) || (!empty($value) && ($value != 'ASC') && ($value != 'DESC'))) {
                    return LogUtil::registerArgsError();
                }
                $orderBy[] = $regColumn[$field] . (!empty($value) ? " {$value}" : '');
            }
        }
        $orderBy = 'ORDER BY ' . (!empty($orderBy) ? implode(', ', $orderBy) : $regColumn['id']);

        $this->purgeExpired();
        $reglist = DBUtil::selectObjectArray('users_registration', $where, $orderBy, $limitOffset, $limitNumRows);

        if ($reglist === false) {
            LogUtil::registerError($this->__('Error! Could not load data.'));
        } else {
            // Fix 'zero dates' and blank dates
            foreach ($reglist as $key => $reginfo) {
                // Fix 'zero dates' and blank dates
                if (isset($reginfo['validuntil']) && (empty($reginfo['validuntil']) || ($reginfo['validuntil'] == '0000-00-00 00:00:00'))) {
                    unset($reglist[$key]['validuntil']);
                }
                // unserialize dynadata
                $reginfo['dynadata'] = unserialize($reginfo['dynadata']);
            }
        }

        return $reglist;
    }

    /**
     * Returns the number of pending applications for new user accounts (registration requests). Expired registrations
     * are purged before the count is performed.
     *
     * @param array $args All parameters passed to this function.
     *                      array $args['filter']   An array of field/value combinations used to filter the results. Optional, default
     *                                                  is to count all records. For example,
     *                                                  array('isapproved' => true, 'isverified' => false) will filter and count
     *                                                  only those registrations that are approved and waiting for e-mail verification.
     *
     * @return int|bool Numer of pending applications, false on error.
     */
    public function countAll($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
            || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)))
        {
            return false;
        }

        $dbinfo = DBUtil::getTables();
        $regColumn = $dbinfo['users_registration_column'];

        $where = '';
        if (isset($args['filter'])) {
            if (!is_array($args['filter'])) {
                return LogUtil::registerArgsError();
            }

            $where = $this->whereFromFilter($args['filter']);
            if ($where === false) {
                return false;
            }
        }

        $this->purgeExpired();
        return DBUtil::selectObjectCount('users_registration', $where);
    }

    /**
     * Processes the results of a registration modify() operation.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     */
    public function update($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
            || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)))
        {
            return LogUtil::registerPermissionError();
        }

        if (!isset($args['reginfo']) || empty($args['reginfo']) || !is_array($args['reginfo'])
            || !isset($args['reginfo']['id']) || empty($args['reginfo']['id']) || !is_numeric($args['reginfo']['id']))
        {
            return LogUtil::registerArgsError();
        } else {
            $reginfo = $args['reginfo'];
            $checkExistence = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $reginfo['id']));
            if (!$checkExistence) {
                return LogUtil::registerError($this->__f('Error! Unable to update registration with id \'%1$s\'. That record does not exist.', $reginfo['id']));
            }
        }

        $reginfo = $this->cleanFieldsToAttributes('users_registration', $reginfo);

        if (!isset($reginfo['dynadata'])) {
            $reginfo['dynadata'] = array();
        }
        $reginfo['dynadata'] = serialize($reginfo['dynadata']);

        if (isset($reginfo['validuntil']) && (empty($reginfo['validuntil']) || ($reginfo['validuntil'] == '0')
            || ($reginfo['validuntil'] == '0000-00-00 00:00:00')))
        {
            unset($reginfo['validuntil']);
        }

        $reginfo = DBUtil::updateObject($reginfo, 'users_registration', '', 'id');

        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Sorry! There was an error updating the registration with id \'%1$s\'.', $reginfo['id']));
        } else {
            // Set dynadata back to unserialized.
            $reginfo['dynadata'] = unserialize($reginfo['dynadata']);
            return $reginfo;
        }
    }

    /**
     * Processes a delete() operation for registration records.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     */
    public function remove($args)
    {
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
            || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)))
        {
            return LogUtil::registerPermissionError();
        }

        if (isset($args['id'])) {
            if (empty($args['id']) || !is_numeric($args['id'])) {
                return LogUtil::registerArgsError();
            }

            return DBUtil::deleteObjectByID('users_registration', $args['id'], 'id');
        } elseif (!isset($args['reginfo']) || empty($args['reginfo']) || !is_array($args['reginfo'])
            || !isset($args['reginfo']['id']) || empty($args['reginfo']['id']) || !is_numeric($args['reginfo']['id']))
        {
            return LogUtil::registerArgsError();
        } else {
            return DBUtil::deleteObject($args['reginfo'], 'users_registration', '', 'id');
        }
    }

    /**
     * Removes expired registrations from the users_registration table.
     */
    protected function purgeExpired()
    {
        $dbinfo = DBUtil::getTables();
        $regColumn = $dbinfo['users_registration_column'];

        // Expiration date/times, as with all date/times in the Users module, are stored as UTC.
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UserUtil::DATETIME_FORMAT);

        // The zero date is there to guard against odd DB errors
        $where = "WHERE ({$regColumn['isverified']} = 0) AND ({$regColumn['validuntil']} IS NOT NULL) AND ({$regColumn['validuntil']} != '0000-00-00 00:00:00') AND ({$regColumn['validuntil']} < '{$nowUTCStr}')";

        DBUtil::deleteWhere('users_registration', $where);
    }

    /**
     * Creates, saves and sends a registration e-mail address verification code.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     */
    public function sendVerificationCode($args)
    {
        // In the future, it is possible we will add a feature to allow a newly registered user to resend
        // a new verification code to himself after doing a login-like process with information from  his
        // registration record, so allow not-logged-in plus READ, as well as moderator.
        if ((!UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_READ))
            || (UserUtil::isLoggedIn() && !SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)))
        {
            return LogUtil::registerPermissionError();
        }

        if (isset($args['reginfo'])) {
            // Got a full reginfo record
            if (!is_array($args['reginfo']) || !isset($args['reginfo']['id']) || !is_numeric($args['reginfo']['id'])) {
                return LogUtil::registerArgsError();
            }
            $reginfo = $args['reginfo'];
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['id']) || !is_numeric($reginfo['id'])) {
                return LogUtil::registerError($this->__('Error! Invalid registration record.'));
            }
        } elseif (!isset($args['id']) || !is_numeric($args['id']) || ((int)$args['id'] != $args['id'])) {
            return LogUtil::registerArgsError();
        } else {
            // Got just an id.
            $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $args['id']));
            if (!$reginfo) {
                return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
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

        $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);

        // Set the verification code
        if ($reginfo['isverified']) {
            return LogUtil::registerError($this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It is already verified.', $reginfo['uname']));
        } elseif (!$forceVerification && ($approvalOrder == UserUtil::APPROVAL_BEFORE) && !$reginfo['isapproved']) {
            return LogUtil::registerError($this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It must first be approved.', $reginfo['uname']));
        }

        $verificationCode = UserUtil::generatePassword();
        $reginfo['verifycode'] = UserUtil::getHashedPassword($verificationCode);

        $regExpireDays = $this->getVar('regexpiredays', 0);
        if (is_numeric($regExpireDays) && ((int)$regExpireDays == $regExpireDays) && ($regExpireDays > 0)) {
            $nowUTC = new DateTime(null, DateTimeZone('UTC'));
            // Do not use DateTimeInterval. It is not defined in PHP 5.2.6
            $nowUTC->modify("+{$regExpireDays} days");

            $reginfo['validuntil'] = $nowUTC->format(UserUtil::DATETIME_FORMAT);
        }

        $updateResult = ModUtil::apiFunc('Users', 'registration', 'update', array('reginfo' => $reginfo));
        if (!$updateResult) {
            return LogUtil::registerError($this->__f('Error! Unable to save the verification code for the registration for \'%1$s\'.', $reginfo['uname']));
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

        $codeSent = ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
            'toAddress'         => $reginfo['email'],
            'notificationType'  => 'regverifyemail',
            'templateArgs'      => $rendererArgs
        ));

        if ($codeSent) {
            return $reginfo;
        } else {
            return false;
        }
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
            if (!is_array($args['reginfo']) || !isset($args['reginfo']['id']) || !is_numeric($args['reginfo']['id'])) {
                return LogUtil::registerArgsError();
            }
            $reginfo = $args['reginfo'];
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['id']) || !is_numeric($reginfo['id'])) {
                return LogUtil::registerError($this->__('Error! Invalid registration record.'));
            }
        } elseif (!isset($args['id']) || !is_numeric($args['id']) || ((int)$args['id'] != $args['id'])) {
            return LogUtil::registerArgsError();
        } else {
            // Got just an id.
            $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $args['id']));
            if (!$reginfo) {
                return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
            }
        }

        $reginfoSave = $reginfo;

        $reginfo['isverified'] = true;

        // Save the registration, even if it is also approved, in case there is a problem later in the function
        $updateResult = ModUtil::apiFunc('Users', 'registration', 'update', array('reginfo' => $reginfo));

        if ($updateResult) {
            $reginfo = $updateResult;

            if ($reginfo['isapproved']) {
                $userinfo = $this->createUser($reginfo);

                if ($userinfo) {
                    $removed = ModUtil::apiFunc('Users', 'registration', 'remove', array('reginfo' => $reginfo));
                    if (!$removed) {
                        LogUtil::log($this->__f('Warning! There was an error removing the registration for \'%1$s\' from the database.', $reginfo['uname']));
                    }
                    return $userinfo;
                } else {
                    ModUtil::apiFunc('Users', 'registration', 'update', array('reginfo' => $reginfoSave));
                    return LogUtil::registerError($this->__('Sorry! There was an error while creating a new user account from your registration. Please contact an administrator.'));
                }
            } else {
                return $reginfo;
            }
        } else {
            return LogUtil::registerError($this->__f('Sorry! There was an error while marking your registration as approved. Please contact an administrator.'));
        }
    }

    /**
     * Approves a registration.
     *
     * If the registration is also verified (or does not need it) then a new users table record
     * is created.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; otherwise false.
     */
    public function approve($args)
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (isset($args['reginfo'])) {
            // Got a full reginfo record
            if (!is_array($args['reginfo']) || !isset($args['reginfo']['id']) || !is_numeric($args['reginfo']['id'])) {
                return LogUtil::registerArgsError();
            }
            $reginfo = $args['reginfo'];
            if (!$reginfo || !is_array($reginfo) || !isset($reginfo['id']) || !is_numeric($reginfo['id'])) {
                return LogUtil::registerError($this->__('Error! Invalid registration record.'));
            }
        } elseif (!isset($args['id']) || !is_numeric($args['id']) || ((int)$args['id'] != $args['id'])) {
            return LogUtil::registerArgsError();
        } else {
            // Got just an id.
            $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('id' => $args['id']));
            if (!$reginfo) {
                return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with id \'%1$s\'', $id));
            }
        }

        $reginfoSave = $reginfo;

        $reginfo['isapproved'] = true;

        if (isset($args['force']) && $args['force']) {
            $reginfo['isverified'] = true;
        }

        // Save the registration, even if it is also verified (either before or now by skipping), in case there is a problem later in the function
        $updateResult = ModUtil::apiFunc('Users', 'registration', 'update', array('reginfo' => $reginfo));

        if ($updateResult) {
            $reginfo = $updateResult;

            if ($reginfo['isverified']) {
                $userinfo = $this->createUser($reginfo);

                if ($userinfo) {
                    $removed = ModUtil::apiFunc('Users', 'registration', 'remove', array('reginfo' => $reginfo));
                    if (!$removed) {
                        LogUtil::registerError($this->__f('Warning! There was an error removing the registration for \'%1$s\' from the database.', $reginfo['uname']));
                    }
                    return $userinfo;
                } else {
                    if (ModUtil::apiFunc('Users', 'registration', 'update', array('reginfo' => $reginfoSave))) {
                        return LogUtil::registerError($this->__f('Sorry! There was an error while creating a new user account. The registration for \'%1$s\' has been restored.', $reginfo['uname']));
                    } else {
                        return LogUtil::registerError($this->__f('Sorry! The registration for \'%1$s\' has been marked as approved, however there was an error while creating a new user account.', $reginfo['uname']));
                    }
                }
            } else {
                return $reginfo;
            }
        } else {
            return LogUtil::registerError($this->__f('Sorry! Unable to approve the registration for \'%1$s\'. There was an error while updating the database.', $reginfo['uname']));
        }
    }

    /**
     * LEGACY user account activaton.
     *
     * We must keep this function because there is no way to know whether an
     * inactive account is inactive because it requires activation, or because of some
     * other reason.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['regdate'] (string)  An SQL date-time containing the user's original registration date-time.
     *                    $args['uid']     (numeric) The id of the user account to activate.
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
        $obj = array('uid'          => $args['uid'],
                'activated'    => UserUtil::ACTIVATED_ACTIVE,
                'user_regdate' => DataUtil::formatForStore($newregdate));

        $this->callHooks('item', 'update', $args['uid'], array('module' => 'Users'));

        return (boolean)DBUtil::updateObject($obj, 'users', '', 'uid');
    }

}
