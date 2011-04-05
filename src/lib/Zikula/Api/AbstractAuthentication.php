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
 * Abstract Authentication API for authentication modules.
 */
abstract class Zikula_Api_AbstractAuthentication extends Zikula_AbstractApi
{
    const FILTER_NONE = 0;
    const FILTER_ENABLED = 1;
    const FILTER_REGISTRATION_ENABLED = 2;

    /**
     * Informs the calling function whether this authentication module is reentrant or not.
     * 
     * A reentrant authentication module is one that may need to redirect the user's browser to an external server in
     * order to check the user's password, authenticate the user, log the user in, or log the user out.
     *
     * Both the getUidForAuthenticationInfo() function and the logout() function must NEVER be reentrant.
     *
     * @return bool True if the authentication module is reentrant; otherwise false.
     */
    abstract public function isReentrant();

    abstract public function supportsAuthenticationMethod(array $args);

    abstract public function isEnabledForAuthentication(array $args);

    abstract public function getAuthenticationMethods(array $args = null);
    
    abstract public function register(array $args);

    /**
     * Authenticates authentication info with the authenticating source, returning a simple boolean result.
     *
     * Note that, despite this function's name, there is no requirement that a password be part of the authentication info.
     * Merely that enough information be provided in the authentication info array to unequivocally authenticate the user. For
     * most authenticating authorities this will be the equivalent of a user name and password, but--again--there
     * is no restriction here. This is not, however, a "user exists in the system" function. It is expected that
     * the authenticating authority validate what ever is used as a password or the equivalent thereof.
     *
     * This function makes no attempt to match the given authentication info with a Zikula user id (uid). It simply asks the
     * authenticating authority to authenticate the authentication info provided. No "login" should take place as a result of
     * this authentication.
     *
     * This function may be called to initially authenticate a user during the registration process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from authenticateUser() in that no attempt is made to match the authentication info with and map to a
     * Zikula user account. It does not return a Zikula user id (uid).
     *
     * @param array $args All arguments passed to this function.
     *                      array   authenticationInfo  The authentication info needed for this authentication module, including any user-entered password.
     *
     * @return boolean True if the authentication info authenticates with the source; otherwise false on authentication failure or error.
     */
    abstract public function checkPassword(array $args);

    /**
     * Authenticates authentication info with the authenticating source, returning simple registration information.
     *
     * ATTENTION: Any function that causes this function to be called MUST specify a return URL, and therefore
     * must be reentrant. In other words, in order to call this function, there must exist a controller function
     * (specified by the return URL) that the OpenID server can return to, and that function must restore the
     * pertinent state for the user as if he never left this site! Session variables should be used to store all
     * pertinent variables, and those must be re-read into the user's state when the return URL is called back
     * by the OpenID server.
     *
     * Note that, despite this function's name, there is no requirement that a password be part of the authentication info.
     * Merely that enough information be provided in the authentication info array to unequivocally authenticate the user. For
     * most authenticating authorities this will be the equivalent of a user name and password, but--again--there
     * is no restriction here. This is not, however, a "user exists in the system" function. It is expected that
     * the authenticating authority validate what ever is used as a password or the equivalent thereof.
     *
     * This function makes no attempt to match the given authentication info with a Zikula user id (uid). It simply asks the
     * authenticating authority to authenticate the authentication info provided. No "login" should take place as a result of
     * this authentication.
     *
     * This function may be called to initially authenticate a user during the registration process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from authenticateUser() in that no attempt is made to match the authentication info with and map to a
     * Zikula user account. It does not return a Zikula user id (uid).
     *
     * This function differs from login()  in that no attempt is made to match the authentication info with and map to a
     * Zikula user account. It does not return a Zikula user id (uid). In addition this function makes no attempt to
     * perform any login-related processes on the authenticating system.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authentication_info    The authentication info needed for this authmodule, including any user-entered password.
     *
     * @return array|boolean If the authentication info authenticates with the source, then an array is returned containing the user's 'claimed_id',
     *                              plus requested simple registration information from the OpenID server; otherwise false on authentication failure or error.
     */
    public function checkPasswordForRegistration(array $args)
    {
        throw new Zikula_Exception_Fatal($this->__('Registration authentication is not supported by this authentication method.'));
    }

    /**
     * Retrieves the Zikula User ID (uid) for the given authentication info from the mapping maintained by this authentication module.
     *
     * ATTENTION: This function must never be reentrant. It must be able to return a value without redirecting
     * the user to an external server for authentication.
     *
     * Custom authentication modules should pay extra special attention to the accurate association of authentication info and user
     * ids (uids). Returning the wrong uid for a given authentication info will potentially expose a user's account to
     * unauthorized access. Custom authentication modules must also ensure that they keep their mapping table in sync with
     * the user's account.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authenticationInfo  The authentication information uniquely associated with a user.
     *
     * @return integer|boolean The integer Zikula uid uniquely associated with the given authentication info;
     *                         otherwise false if user not found or error.
     */
    abstract public function getUidForAuthenticationInfo(array $args);

    /**
     * Authenticates authentication info with the authenticating source, returning the matching Zikula user id.
     *
     * This function may be called to authenticate a user during the login process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from checkPassword() in that the authentication info must match and be mapped to a Zikula user account,
     * and therefore must return a Zikula user id (uid). If it cannot, then it should return false, even if the authentication info
     * provided would otherwise authenticate with the authenticating authority.
     *
     * @param array $args All arguments passed to this function.
     *                      array   authenticationInfo  The authentication info needed for this authentication module, including any user-entered password.
     *
     * @return integer|boolean If the authentication info authenticates with the source, then the Zikula uid associated with that login ID;
     *                         otherwise false on authentication failure or error.
     */
    abstract public function authenticateUser(array $args);

}