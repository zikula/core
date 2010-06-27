<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract Authentication API for Auth modules.
 */
abstract class Zikula_AuthApi extends Zikula_Api
{
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
    abstract public function checkPassword($args);

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
    abstract public function getAuthinfoForUser($args);

    /**
     * Retrieves the Zikula User ID (uid) for the given authinfo
     * 
     * From the mapping maintained by this authmodule.
     *
     * Custom authmodules should pay extra special attention to the accurate association of authinfo and user
     * ids (uids). Returning the wrong uid for a given authinfo will potentially expose a user's account to
     * unauthorized access. Custom authmodules must also ensure that they keep their mapping table in sync with
     * the user's account.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authentication information uniquely associated with a user.
     *
     * @return integer|boolean The integer Zikula uid uniquely associated with the given authinfo;
     *                         otherwise false if user not found or error.
     */
    abstract public function getUidForAuthinfo($args);

    /**
     * Authenticates authinfo with the authenticating source, returning the matching Zikula user id.
     * 
     * This function may be called to initially authenticate a user during the login process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from checkPassword() in that the authinfo must match and be mapped to a Zikula user account,
     * and therefore must return a Zikula user id (uid). If it cannot, then it should return false, even if the authinfo
     * provided would otherwise authenticate with the authenticating authority.
     *
     * This function differs from login() in that this function makes no attempt to perform any login-related processes
     * on the authenticating system. (If there is no login-related process on the authenticating system, then this and
     * login() are functionally equivalent, however they are still logically distinct in their intent.)
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authinfo needed for this authmodule, including any user-entered password.
     *
     * @return integer|boolean If the authinfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                         otherwise false on authentication failure or error.
     */
    abstract public function authenticateUser($args);

    /**
     * Login the user.
     *
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
     * This function differs from checkPassword() in that the authinfo must match and be mapped to a Zikula user account,
     * and therefore must return a Zikula user id (uid). If it cannot, then it should return false, even if the authinfo
     * provided would otherwise authenticate with the authenticating authority. In addition, it performs login-related functions
     * on the authenticating system, if any.
     *
     * This function differs from authenticateUser() in that this performs login-related processes on the authenticating
     * system, if any. (If there is no login-related process on the authenticating system, then this and
     * authenticateUser() are functionally equivalent, however they are still logically distinct in their intent.)
     *
     * @param array $args All arguments passed to this function.
     *                      array authinfo  The information necessary to authenticate the user, typically a login ID and a password.
     *
     * @return integer|boolean If the authinfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                         otherwise false on authentication failure or error.
     */
    abstract public function login($args);

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
    abstract public function logout($args);
}