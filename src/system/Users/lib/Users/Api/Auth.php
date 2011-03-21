<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * The user authentication services for the log-in process through the core Users table.
 */
class Users_Api_Auth extends Zikula_Api_AbstractAuthentication
{
     /**
      * Informs the calling function whether this authmodule is reentrant or not.
      *
      * The Users module is guaranteed never to be reentrant.
      *
      * @return bool False.
      */
    public function isReentrant()
    {
        return false;
    }

    /**
     * Authenticates authinfo with the authenticating source, returning a simple boolean result.
     *
     * Note that, despite this function's name, there is no requirement that a password be part of the authinfo.
     * Merely that enough information be provided in the authinfo array to unequivocally authenticate the user. For
     * most authenticating authorities this will be the equivalent of a user name and password, but--again--there
     * is no restriction here. This is not, however, a "user exists in the system" function. It is expected that
     * the authenticating authority validate what ever is used as a password or the equivalent thereof.
     *
     * This function makes no attempt to match the given authinfo with a Zikula user id (uid). It simply asks the
     * authenticating authority to authenticate the authinfo provided. No "login" should take place as a result of
     * this authentication.
     *
     * This function may be called to initially authenticate a user during the registration process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from authenticateUser() in that no attempt is made to match the authinfo with and map to a
     * Zikula user account. It does not return a Zikula user id (uid).
     *
     * This function differs from login()  in that no attempt is made to match the authinfo with and map to a
     * Zikula user account. It does not return a Zikula user id (uid). In addition this function makes no attempt to
     * perform any login-related processes on the authenticating system.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authinfo needed for this authmodule, including any user-entered password.
     *
     * @return boolean True if the authinfo authenticates with the source; otherwise false on authentication failure or error.
     */
    public function checkPassword($args)
    {
        // Note that this is a poor example function for external authmodules, because the authenticating
        // information for the Users module is stored in the users table, necessitating a lookup of the uid.

        if (!isset($args['authinfo']) || !is_array($args['authinfo']) || empty($args['authinfo'])) {
            LogUtil::registerArgsError();
            return false;
        }

        $authinfo = $args['authinfo'];

        $uid = ModUtil::apiFunc('Users', 'auth', 'getUidForAuthinfo', array(
            'authinfo'  => $authinfo,
        ));

        if (!$uid) {
            return false;
        }

        if (!isset($authinfo['pass']) || !is_string($authinfo['pass']) || empty($authinfo['pass'])) {
            return LogUtil::registerError($this->__f('Sorry! That %1$s is not registered with us, or the %1$s and password do not match our records.', array($idFieldDesc)));
        }

        // For a custom authmodule, we'd map the authinfo to a uid above, and then execute the custom
        // authentication process here. On success the uid would be returned, otherwise false is returned. Note that
        // any "log in" is not done here, but is done up above in the login() function. This is simply authentication
        // (verification that the authinfo, including the password, is valid as a unit).
        //
        // $userEnteredPassword = $authinfo['pass'];
        //
        // $fooAuthentication = new FooAuthenticationService($loginID);
        // if (!$fooAuthentication->isValidPassword($userEnteredPassword) {
        //     return false;
        // } else {
        //     return $uid;
        // }
        //
        // Essentially, what follows is the Users module's "custom" authentication process.

        $userObj = UserUtil::getVars($uid);
        if (!$userObj) {
            // Must be a registration. Acting as an authmodule, we should not care at this point about the user's
            // account status. The account status is something for UserUtil::loginUsing() to deal with after we
            // tell it whether the account authenticates or not.
            $userObj = UserUtil::getVars($uid, false, '', true);
        }

        if (!$userObj) {
            return false;
        } elseif (empty($userObj['pass'])) {
            if ($userObj['activated'] == UserUtil::ACTIVATED_PENDING_REG) {
                // Special case - admin created a user account without a password, and user has not verified (and thus
                // created a password) yet.
                if ($this->getVar('login_displayverify', false)) {
                    return LogUtil::registerError($this->__("Sorry! Your e-mail address must be verified before you can log in. Check for an e-mail message containing verification instructions. If you need another verification e-mail sent, contact an administrator."));
                }
            } else {
                // Blank password automatically means no login.
                return false;
            }
        }

        // The following check for non-salted passwords and the old 'hash_method' field is to allow the admin to log in
        // during an upgrade from 1.2.  It needs to be kept for any version that allows an upgrade from Zikula 1.2.X.
        $methodSaltDelimPosition = strpos($userObj['pass'], UserUtil::SALT_DELIM);
        $saltPassDelimPosition = ($methodSaltDelimPosition === false) ? false : strpos($userObj['pass'], UserUtil::SALT_DELIM, ($methodSaltDelimPosition + 1));
        if ($saltPassDelimPosition !== false) {
            // New style salted password including hash method code
            $currentPasswordHashed = $userObj['pass'];
        } else {
            // Old style unsalted password with hash_method in separate field
            if (!isset($userObj['hash_method'])) {
                return false;
            }
            $currentPasswordHashed = $userObj['hash_method'] . '$$' . $userObj['pass'];
        }

        if (!UserUtil::passwordsMatch($authinfo['pass'], $currentPasswordHashed)) {
            // Need to check for an override of loginviaoption.
            $loginViaOption = $this->getVar('loginviaoption', 0);
            if ((isset($authinfo['loginid']) && ($loginViaOption == 1)) || (!isset($authinfo['loginid']) && isset($authinfo['email']))) {
                $idFieldDesc = $this->__('e-mail address');
            } else {
                $idFieldDesc = $this->__('user name');
            }
            return LogUtil::registerError($this->__f('Sorry! That %1$s is not registered with us, or the %1$s and password do not match our records.', array($idFieldDesc)));
        } else {
            // Password in $authinfo['pass'] is good at this point.

            if (version_compare($this->modinfo['version'], '2.0.0') >= 0) {
                // Check stored hash matches the current system type, if not convert it--but only if the module version is sufficient.
                // Note: this is purely specific to the Users module authentication. A custom module might do something similar if it
                // changed the way it stored some piece of data between versions, but in general this would be uncommon.
                list($currentPasswordHashCode, $currentPasswordSaltStr, $currentPasswordHashStr) = explode(UserUtil::SALT_DELIM, $currentPasswordHashed);
                $systemHashMethodCode = UserUtil::getPasswordHashMethodCode($this->getVar('hash_method', 'sha256'));
                if (($systemHashMethodCode != $currentPasswordHashCode) || empty($currentPasswordSaltStr)) {
                    if (!UserUtil::setPassword($authinfo['pass'], $uid)) {
                        LogUtil::log($this->__('Internal Error! Unable to update the user\'s password with the new hashing method and/or salt.'), 'CORE');
                    }
                }
            }
            return true;
        }
    }

    /**
     * Returns a "clean" version of the authinfo used to log in, without any password-like information.
     *
     * This function strips off any password-like information from an authinfo array, leaving only user name-like
     * identifying information that can later be used to log the user out of the authenticating system. This "clean"
     * authinfo is intended to be stored along with the session, and the session is not a secure place to retain
     * password-like information.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authentication information uniquely associated with a user.
     *
     * @return array A "clean" version of the authinfo passed in, devoid of any password-like fields, but retaining
     *                  enough information--such as user name-like fields--to identify the account on the authenticating
     *                  system for later log out operations.
     */
    public function getAuthinfoForSession($args)
    {
        // Validate authinfo
        if (!isset($args['authinfo']) || !is_array($args['authinfo']) || empty($args['authinfo'])) {
            LogUtil::registerArgsError();
            return false;
        }

        unset($authinfo['pass']);

        return $authinfo;
    }

    /**
     * Retrieves the Zikula User ID (uid) for the given authinfo, from the mapping maintained by this authmodule.
     *
     * Custom authmodules should pay extra special attention to the accurate association of authinfo and user
     * ids (uids). Returning the wrong uid for a given authinfo will potentially expose a user's account to
     * unauthorized access. Custom authmodules must also ensure that they keep their mapping table in sync with
     * the user's account.
     *
     * Note: (Specific to Zikula Users module authentication) This function uses mb_strtolower, and assumes that
     * locale == charset.
     * 
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authentication information uniquely associated with a user.
     *
     * @return integer|boolean The integer Zikula uid uniquely associated with the given authinfo;
     *                          otherwise false if user not found or error.
     */
    public function getUidForAuthinfo($args)
    {
        // authinfo can contain anything necessary for the authentication method, but most of the time will contain
        // a login ID of some sort, and a password. Set up authinfo in templates as name="authinfo[fieldname]" to
        // gather what is needed. In this case, we don't care about any password that might be in authinfo.

        // Validate authinfo
        if (!isset($args['authinfo']) || !is_array($args['authinfo']) || empty($args['authinfo'])) {
            LogUtil::registerArgsError();
            return false;
        }

        // Custom authmodules can expect whatever they need in authinfo, but by convention if all it needs is a single user
        // identifier of some sort (user name, e-mail address, whatever), then it might be a good idea for
        // consistency to expect it in 'loginid'.
        //
        // For the Users module, we expect 'loginid'. We'll accept either 'uname' or 'email' in order to override the 'loginviaoption'
        // modulevar if 'loginid' is not set. Custom authmodules might not need to be this complicated.
        $authinfo = $args['authinfo'];
        if (isset($authinfo['loginid'])) {
            $loginID = $authinfo['loginid'];
        } elseif (isset($authinfo['uname'])) {
            $loginID = $authinfo['uname'];
            $loginOptionOverride = 0;
        } elseif (isset($authinfo['email'])) {
            $loginID = $authinfo['email'];
            $loginOptionOverride = 1;
        }

        if (isset($loginOptionOverride)) {
            $loginOption = $loginOptionOverride;
        } else {
            $loginOption = $this->getVar('loginviaoption', 0);
        }
        if ($loginOption == 1) {
            $idFieldDesc = $this->__('an e-mail address');
        } else {
            $idFieldDesc = $this->__('a user name');
        }

        if (!isset($loginID) || (is_string($loginID) && empty($loginID))) {
            return LogUtil::registerError($this->__f('Sorry! You must provide %s.', $idFieldDesc));
        } elseif (!is_string($loginID)) {
            return LogUtil::registerArgsError();
        }

        // The users module expects the loginid to be lower case. Custom authmodules would do whatever
        // they needed here, if anything.
        $loginID = mb_strtolower($loginID);

        // Look up the authinfo in the authentication-source to/from Zikula uid mapping table.
        //
        // Note: the following is a bad example for custom modules because there no mapping table for the Users module.
        // A custom authentication module would look up a uid using its own mapping tables, not the users table or UserUtil.

        if ($loginOption == 1) {
            $uid = UserUtil::getIdFromEmail($loginID);
            if (!$uid) {
                // Might be a registration. Acting as an authmodule, we should not care at this point about the user's
                // account status. The account status is something for UserUtil::loginUsing() to deal with after we
                // tell it whether the account authenticates or not.
                $uid = UserUtil::getIdFromEmail($loginID, true);
            }
        } else {
            $uid = UserUtil::getIdFromName($loginID);
            if (!$uid) {
                // Might be a registration. See above.
                $uid = UserUtil::getIdFromName($loginID, true);
            }
        }

        return $uid;
    }

    /**
     * Authenticates authinfo with the authenticating source, returns the Zikula user ID (uid) of the user.
     *
     * This function may be called to initially authenticate a user during the login process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authinfo needed for this authmodule, including any user-entered password.
     *
     * @return integer|boolean If the authinfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                          otherwise false on authentication failure or error.
     */
    public function authenticateUser($args)
    {
        $checkPassword = ModUtil::apiFunc('Users', 'auth', 'checkPassword', $args);
        if ($checkPassword) {
            $uid = ModUtil::apiFunc('Users', 'auth', 'getUidForAuthinfo', $args);

            if ($uid) {
                return $uid;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
