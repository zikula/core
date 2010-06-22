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
 * The Auth API provides user authentication services for the log-in process; this class
 * provides user authentication through the core Users table.
 *
 * While the deletion of user accounts is currently not supported, custom authmodule authors
 * should plan for the eventual inclusion of this capability, and allow for the possible deletion of
 * associations between login ids and uid in the future.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Api_Auth extends Zikula_Api
{
    /**
     * Authenticates the user-entered authinfo with the authenticating source, and (if authenticated)
     * returns the Zikula user ID (uid) of the user associated with the authinfo.
     *
     * If any actions need to be taken at the authenticating source to indicate that the user is logged in, they are
     * taken at this point. If the login fails after returning to the Zikula login process, then logout() will be
     * called.
     *
     * It is likely that this function will call the authenticateUser() function within this same API to actually
     * determine if the user is valid. The primary job of this function is likely to perform
     * any authentication-source-specific tasks to indicate the user is logged in, if needed.
     *
     * NOTE: This function does not change the state of the user in Zikula. In other words, this function does not
     * actually log the user into Zikula. It merely returns the uid to Zikula, indicating that the user's supplied
     * credentials were valid. The core Zikula login process that called this function will perform the actual
     * change of state for the user.
     *
     * @param array $args All arguments passed to this function.
     *                      array authinfo  The information necessary to authenticate the user, typically a login ID and a password.
     *
     * @return int|bool If the authinfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                      otherwise false on authentication failure or error.
     */
    public function login($args)
    {
        // authinfo can contain anything necessary for the authentication method, but most of the time will contain
        // a login ID of some sort, and a password. Set up authinfo in templates as name="authinfo[fieldname]" to
        // gather what is needed.

        // Do any pre-authentication checks here (such as validation of authinfo parameters not directly
        // handled by authenticateUser(), etc.)

        // For the Users module authentication method, we'll simply pass the authinfo along and allow
        // authenticateUser() to validate them.
        $authinfo = isset($args['authinfo']) ? $args['authinfo'] : array();

        // Authenticate the user using the authinfo. (authenticateUser just gives a yes or no; it does not actually
        // perform any login-related actions.)
        $authenticatedUid = ModUtil::apiFunc('Users', 'auth', 'authenticateUser', array(
            'authinfo'  => $authinfo,
        ));

        // Perform post-authentication actions here related to logging in
        if ($authenticatedUid) {
            // Perform any post-authentication actions when authentication was successful.
            // $fooAuthentication = new FooAuthenticationService($loginID);
            // $fooAuthentication->login();
        }
        // Optionally include an else here and perform any post-authentication actions on a failed authentication.
        // The Users module authentication method relies on authenticateUser() to set an appropriate message using
        // LogUtil on failure of authentication.

        // Return the results.
        return $authenticatedUid;
    }

    /**
     * Retrieves the authinfo for the authentication source associated with a given Zikula user, not including any password.
     *
     * The authinfo will likely only include some sort of login ID for most authentication methods. (Passwords are not returned.)
     *
     * Custom authmodules should pay extra special attention to the accurate association of authinfo and user
     * ids (uids). Returning the wrong authinfo for a given uid will potentially expose a user's account to
     * unauthorized access. Custom authmodules must also ensure that they keep their mapping table in sync with
     * the user's account.
     *
     * @param array $args All arguments passed to this function.
     *                      int    uid      The Zikula user ID (uid) of a user.
     *
     * @return array|bool The authinfo for the authentication source of the specified Zikula user--not including any password-like information
     *                      (enough authinfo to uniquely identify the user if passed back along with a user-entered password, such as the
     *                      user's unique user name); otherwise false if user not found or error.
     */
    public function getAuthinfoForUser($args)
    {
        // Validate uid
        if (!isset($args['uid']) || !is_numeric($args['uid']) || empty($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
            LogUtil::registerArgsError();
            return false;
        }
        $uid = $args['uid'];

        // Look up the uid in the authentication-source to/from Zikula uid mapping table.
        // Note: the following is a bad example for custom modules because there is no mapping necessary.
        // A custom authentication module would not use UserUtil! It would query its own tables.

        $userObj = UserUtil::getVars($uid);
        if (!$userObj) {
            // No register error here. Not an error, simply doesn't exist.
            return false;
        }

        $loginOption = ModUtil::getVar('Users', 'loginviaoption', 0);
        $idField = ($loginOption == 0) ? 'uname' : 'email';

        $authinfo = array();
        $authinfo['loginid'] = $userObj[$idField];

        return $authinfo;
    }

    /**
     * Retrieves the Zikula User ID (uid) for the given authinfo, from the mapping maintained by
     * this authmodule.
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
     * @return int|bool The integer Zikula uid uniquely associated with the given authinfo;
     *                      otherwise false if user not found or error.
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
        // For the Users module, we expect 'loginid'. We'll accept either 'uname' or 'email' for historical reasons,
        // if 'loginid' is not set. Custom authmodules might not need to be this complicated.
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
            $loginOption = ModUtil::getVar('Users', 'loginviaoption', 0);
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
        } else {
            $uid = UserUtil::getIdFromName($loginID);
        }

        if (!$uid) {
            if ($loginOption == 1) {
                $idFieldDesc = $this->__('e-mail address');
            } else {
                $idFieldDesc = $this->__('user name');
            }
            return LogUtil::registerError($this->__f('Sorry! That %1$s is not registered with us, or the %1$s and password do not match our records.', $idFieldDesc));
        }

        return $uid;
    }

    /**
     * Authenticates authinfo with the authenticating source, and (if authenticated) returns the Zikula
     * user ID (uid) of the user associated with the login ID.
     *
     * This function may be called to initially authenticate a user during the login process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authinfo needed for this authmodule, including any user-entered password.
     *
     * @return int|bool If the authinfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                      otherwise false on authentication failure or error.
     */
    public function authenticateUser($args)
    {
        // Custom authmodules can expect whatever they need in authinfo, but by convention if all it needs is a single user
        // identifier of some sort (user name, e-mail address, whatever), then it might be a good idea for
        // consistency to expect it in 'loginid'. Likewise if only a typical password is expected, then it might be a good
        // idea for consistency to expect it as 'pass'.
        //
        // For the Users module, we expect 'loginid' and 'pass'. We'll accept either 'uname' or 'email' in place of 'loginid'
        // for historical reasons, if 'loginid' is not set. Custom authmodules might not need to be this complicated. In our
        // case the 'loginid' (or 'uname' or 'email') is validated by getUidForAuthInfo() for us.
        // Validate authinfo
        if (!isset($args['authinfo']) || !is_array($args['authinfo']) || empty($args['authinfo'])) {
            LogUtil::registerArgsError();
            return false;
        }

        $authinfo = $args['authinfo'];

        $uid = ModUtil::apiFunc('Users', 'auth', 'getUidForAuthinfo', array(
            'authinfo'  => $authinfo,
        ));

        if (!$uid) {
            // We count on getUidForAuthinfo to set an appropriate message.
            return false;
        }

        if (!isset($authinfo['pass']) || !is_string($authinfo['pass']) || empty($authinfo['pass'])) {
            return LogUtil::registerError($this->__('Sorry! Invalid password!'));
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
            if (ModUtil::getVar('Users', 'loginviaoption', 0) == 1) {
                $idFieldDesc = $this->__('e-mail address');
            } else {
                $idFieldDesc = $this->__('user name');
            }
            return LogUtil::registerError($this->__f('Sorry! That %1$s is not registered with us, or the %1$s and password do not match our records.', array($idFieldDesc)));
        }

        // Password in $authinfo['pass'] is good at this point.

        if (version_compare($this->modinfo['version'], '1.19.0') >= 0) {
            // Check stored hash matches the current system type, if not convert it--but only if the module version is sufficient.
            // Note: this is purely specific to the Users module authentication, and has nothing to do with what a custom
            // authmodule might do.
            list($currentPasswordHashCode, $currentPasswordSaltStr, $currentPasswordHashStr) = explode(UserUtil::SALT_DELIM, $currentPasswordHashed);
            $systemHashMethodCode = UserUtil::getPasswordHashMethodCode(ModUtil::getVar('Users', 'hash_method', 'sha1'));
            if (($systemHashMethodCode != $currentPasswordHashCode) || empty($currentPasswordSaltStr)) {
                if (!UserUtil::setPassword($authinfo['pass'], $uid)) {
                    LogUtil::log($this->__('Internal Error! Unable to update the user\'s password with the new hashing method and/or salt.'), 'CORE');
                }
            }
        }

        // Custom authmodules should take extra special care to not return a valid uid if authentication fails.
        // If the loginid and password do not authenticate, please ensure that false is returned!
        return $uid;
    }

    /**
     * Logs the user out of the authentication source.
     *
     * If any actions need to be taken at the authenticating source to indicate that the user is logged out, they are
     * taken at this point.
     *
     * NOTE: This function does not change the state of the user in Zikula. In other words, this function does not
     * actually log the user out of The core Zikula logout process that called this function will perform the actual
     * change of state for the user.
     *
     * @param array $args All arguments passed to this function.
     *                      int uid  The Zikula user ID of the user logging out, used to look up the authentication-source login ID;
     *                                  optional if UserUtil::isLoggedIn(), otherwise required.
     *
     * @return bool True if successfully logged out of the authenticating source; otherwise false.
     */
    public function logout($args)
    {
        // There's really nothing to do here for the Zikula Users module, but a custom authmodule might have to
        // undo something done during login. If not, simply returing true is enough, although some basic
        // validation of parameters and accounts might be in order.
        //
        //  What follows is intended to be an example of what might be done in a custom authmodule.

        // Validate uid
        if (!isset($args['uid'])) {
            if (UserUtil::isLoggedIn()) {
                $uid = UserUtil::getVar('uid');
            } else {
                return LogUtil::registerArgsError();
            }
        } elseif (!is_numeric($args['uid']) || empty($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
            return LogUtil::registerArgsError();
        } else {
            $uid = $args['uid'];
        }

        // Look up the authentication-source login ID of the user here.
        $authinfo = ModUtil::apiFunc('Users', 'auth', 'getAuthinfoForUser', array(
            'uid'   => $uid
        ));
        if (!$authinfo) {
            return LogUtil::registerError($this->__('Error! No such user registered with this authentication method.'));
        }

        // Do any authentication-source specific log out tasks here, now that we have the login ID.
        // $fooAuthentication = new FooAuthenticationService($authinfo['loginid']);
        // return $fooAuthentication->logout();

        return true;
    }

}
