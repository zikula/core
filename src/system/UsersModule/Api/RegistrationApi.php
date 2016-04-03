<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Api;

use Zikula\Core\Event\GenericEvent;
use UserUtil;
use SecurityUtil;
use Zikula\UsersModule\Constant as UsersConstant;
use System;
use ModUtil;
use ThemeUtil;
use Zikula;
use Zikula_Session;
use DateUtil;
use DataUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * The system-level and database-level functions for user-initiated actions related to new account registrations.
 */
class RegistrationApi extends \Zikula_AbstractApi
{
    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN);
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrative permission for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_EDIT);
    }

    /**
     * Related to getRegistrationErrors, returns error information related to a new or modified password.
     *
     * @param mixed[] $args {
     *      @type array  $reginfo      An array containing either registration information gathered from the user, or user account
     *                                 information gathered from the user. The contents of the array that are checked by this method
     *                                 include 'uname', 'pass', 'passagain', and 'passreminder'. Optional. If not provided, then
     *                                 the individual elements should be provided on the $args array.
     *      @type string $uname        If not specified in $args['reginfo']['uname'], then the user name to be checked.
     *      @type string $pass         If not specified in $args['reginfo']['pass'], then the password to be checked.
     *      @type string $passreminder If not specified in $args['reginfo']['passreminder'], then the password reminder to be checked.
     *      @type string $passagain    The repeated verification password entered by the user (NOTE: this is never sent in $args['reginfo']).
     *                      }
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

        if ($reginfo['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
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
                $passwordErrors['passagain'] = $this->__('The value entered does not match the password entered in the &quot;Password&quot; field.');
            }

            if (!$this->currentUserIsAdminOrSubAdmin()) {
                if ($this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) {
                    if ((!isset($reginfo['passreminder']) || empty($reginfo['passreminder'])) && $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY, UsersConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY)) {
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
        }

        return $passwordErrors;
    }

    /**
     * Related to getRegistrationErrors, returns error information related to a new or modified e-mail address.
     *
     * @param mixed[] $args {
     *      @type int    $uid        The uid of the user to be checked; optional.
     *      @type string $email      The e-mail address to be checked.
     *      @type string $emailagain The repeated e-mail address entered by the user for verification.
     *                      }
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

            if ($tempValid && $this->getVar(UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL, false)) {
                if ($checkMode == 'modify') {
                    $emailUsageCount = UserUtil::getEmailUsageCount($reginfo['email'], $reginfo['uid']);
                } else {
                    $emailUsageCount = UserUtil::getEmailUsageCount($reginfo['email']);
                }

                if ($emailUsageCount) {
                    $emailErrors['email'] = $this->__('The email address you entered has already been registered.');
                    $tempValid = false;
                }
            }
        }

        if (!isset($emailAgain) || empty($emailAgain)) {
            $emailErrors['emailagain'] = $this->__('You did not repeat the e-mail address for verification. Please enter the same e-mail address once in each field.');
        } elseif (isset($reginfo['email']) && !empty($reginfo['email']) && ($reginfo['email'] !== $emailAgain)) {
            $emailErrors['emailagain'] = $this->__('The value entered does not match the email address entered in the &quot;Email Address&quot; field.');
        }

        return $emailErrors;
    }

    /**
     * Validate new user information entered by the user.
     *
     * @param mixed[] $args {
     *      @type array  $reginfo        The core registration or user information collected from the user.
     *      @type string $emailagain     The e-mail address repeated for verification.
     *      @type string $passagain      The passsword repeated for verification.
     *      @type string $antispamanswer The answer to the antispam question provided by the user.
     *      @type string $checkmode      The "mode" that should be used when checking errors. Either 'new' or 'modify'
     *                                   The checks that are performed depend on whether the record being checked is
     *                                   for a new record or a record being modified.
     *      @type bool   $setpass        A flag indicating whether the password is to be set on the new
     *                                   or modified record, affecting error checking.
     *                      }
     *
     * @return array An array containing errors organized by field.
     *
     * @throws AccessDeniedException Thrown if the user does not have read access.
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function getRegistrationErrors($args)
    {
        $registrationErrors = array();

        // we do not check permissions here (see #1874)
        /*if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }*/

        $isAdmin = $this->currentUserIsAdmin();
        $isAdminOrSubAdmin = $this->currentUserIsAdminOrSubAdmin();

        if (!isset($args['reginfo']) || !is_array($args['reginfo'])) {
            throw new \InvalidArgumentException($this->__('Internal Error! Missing required parameter.'));
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
        $spamProtectionUserAnswer   = isset($args['antispamanswer']) ? $args['antispamanswer']   : '';

        if (!isset($reginfo['uname']) || empty($reginfo['uname'])) {
            $registrationErrors['uname'] = $this->__('You must provide a user name.');
        } elseif (!System::varValidate($reginfo['uname'], 'uname')) {
            $registrationErrors['uname'] = $this->__('The user name you entered contains unacceptable characters. A valid user name consists of lowercase letters, numbers, underscores, periods, and/or dashes.');
        } elseif (mb_strlen($reginfo['uname']) > UsersConstant::UNAME_VALIDATION_MAX_LENGTH) {
            $registrationErrors['uname'] = $this->__f('The user name you entered is too long. The maximum length is %1$d characters.', array(UsersConstant::UNAME_VALIDATION_MAX_LENGTH));
        } else {
            $tempValid = true;
            if (!$isAdmin) {
                $illegalUserNames = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, '');
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

        $verificationAndPassword = $this->getVar(UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE, UsersConstant::VERIFY_NO);
        if ($verificationAndPassword == UsersConstant::VERIFY_SYSTEMPWD) {
            throw new \InvalidArgumentException($this->__('Internal Error! System-generated passwords are no longer supported!'));
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
            $spamProtectionQuestion = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, '');
            $spamProtectionCorrectAnswer = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER, '');
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
     * LEGACY user account activation.
     *
     * We must keep this function because there is no way to know whether an
     * inactive account is inactive because it requires activation, or because of some
     * other reason.
     *
     * @param mixed[] $args {
     *      @type string  $regdate An SQL date-time containing the user's original registration date-time.
     *      @type int     $uid     The id of the user account to activate.
     *                      }
     *
     * @return bool True on success, otherwise false.
     */
    public function activateUser($args)
    {
        // This function is an end-user function.

        // we do not check permissions here (see #1874)
        /*if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            return false;
        }*/

        // Preventing reactivation from same link !
        $newregdate = DateUtil::getDatetime(strtotime($args['regdate']) + 1);
        UserUtil::setVar('activated', UsersConstant::ACTIVATED_ACTIVE, $args['uid']);
        UserUtil::setVar('user_regdate', DataUtil::formatForStore($newregdate), $args['uid']);

        return true;
    }
}
