<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract Authentication API for Auth modules.
 */
abstract class Zikula_Api_AbstractAuthentication extends Zikula_AbstractApi
{
    /**
     * Informs the calling function whether this authmodule is reentrant or not.
     * 
     * A reentrant authmodule is one that may need to redirect the user's browser to an external server in
     * order to check the user's password, authenticate the user, log the user in, or log the user out.
     *
     * Both the getUidForAuthinfo() function and the logout() function must NEVER be reentrant.
     *
     * @return bool True if the authmodule is reentrant; otherwise false.
     */
    abstract public function isReentrant();

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
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authinfo needed for this authmodule, including any user-entered password.
     *
     * @return boolean True if the authinfo authenticates with the source; otherwise false on authentication failure or error.
     */
    abstract public function checkPassword($args);

    /**
     * Retrieves the Zikula User ID (uid) for the given authinfo from the mapping maintained by this authmodule.
     *
     * ATTENTION: This function must never be reentrant. It must be able to return a value without redirecting
     * the user to an external server for authentication.
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
     * This function may be called to authenticate a user during the login process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from checkPassword() in that the authinfo must match and be mapped to a Zikula user account,
     * and therefore must return a Zikula user id (uid). If it cannot, then it should return false, even if the authinfo
     * provided would otherwise authenticate with the authenticating authority.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authinfo    The authinfo needed for this authmodule, including any user-entered password.
     *
     * @return integer|boolean If the authinfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                         otherwise false on authentication failure or error.
     */
    abstract public function authenticateUser($args);

}