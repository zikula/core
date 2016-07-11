<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Core\Exception\FatalErrorException;

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

    /**
     * Indicate whether this module supports the indicated authentication method.
     *
     * Parameters passed in $args:
     * ---------------------------
     * string 'method' The name of the authentication method for which support is enquired.
     *
     * @param array $args All arguments passed to this function, see above.
     *
     * @return boolean True if the indicated authentication method is supported by this module; otherwise false.
     *
     * @throws FatalErrorException Thrown if invalid parameters are sent in $args.
     */
    abstract public function supportsAuthenticationMethod(array $args);

    /**
     * Indicates whether a specified authentication method that is supported by this module is enabled for use.
     *
     * Parameters passed in $args:
     * ---------------------------
     * string 'method' The name of the authentication method for which support is enquired.
     *
     * @param array $args All arguments passed to this function, see above.
     *
     * @return boolean True if the indicated authentication method is enabled by this module; otherwise false.
     *
     * @throws FatalErrorException Thrown if invalid parameters are sent in $args.
     */
    abstract public function isEnabledForAuthentication(array $args);

    /**
     * Retrieves an array of authentication methods defined by this module, possibly filtered by only those that are enabled.
     *
     * Parameters passed in $args:
     * ---------------------------
     * integer 'filter' Either {@link FILTER_ENABLED} (value 1), {@link FILTER_NONE} (value 0), or not present; allows the result to be filtered.
     *                      If this argument is FILTER_ENABLED, then only those authentication methods that are also enabled are returned.
     *
     * @param array $args All arguments passed to this function.
     *
     * @return array An array containing the authentication methods defined by this module, possibly filtered by only those that are enabled.
     *
     * @throws FatalErrorException Thrown if invalid parameters are sent in $args.
     */
    abstract public function getAuthenticationMethods(array $args = null);

    /**
     * Retrieves an authentication method defined by this module.
     *
     * Parameters passed in $args:
     * ---------------------------
     * string 'method' The name of the authentication method.
     *
     * @param array $args All arguments passed to this function.
     *
     * @return array An array containing the authentication method requested.
     *
     * @throws FatalErrorException Thrown if invalid parameters are sent in $args.
     */
    abstract public function getAuthenticationMethod(array $args);

    /**
     * Registers a user account record or a user registration request with the authentication method.
     *
     * This is called during the user registration process to associate an authentication method provided by this authentication module
     * with a user (either a full user account, or a user's registration request).
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array   'authentication_method' An array identifying the authentication method to associate with the user account or registration
     *                                      record. The array should contain two elements: 'modname' containing the authentication module's
     *                                      name (the name of this module), and 'method' containing the name of an authentication method
     *                                      defined by this module.
     * array   'authentication_info'   An array containing the authentication information for the user. The contents of the array are defined
     *                                      by each authentication module.
     * numeric 'uid'                   The user id of the user account record or registration request to associate with the authentication method and
     *                                      authentication information.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return boolean True if the user account or registration request was successfully associated with the authentication method and
     *                      authentication information; otherwise false.
     *
     * @throws FatalErrorException Thrown if the arguments array is invalid, or the user id, authentication method, or authentication information
     *                                      is invalid.
     */
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
     * Parameters passed in the $args array:
     * -------------------------------------
     * array authentication_info The authentication info needed for this authmodule, including any user-entered password.
     *
     * @param array $args All arguments passed to this function.
     *
     * @return array|boolean If the authentication info authenticates with the source, then an array is returned containing the user's authentication
     *                          information and any additional optional registration information that is passed back by the authentication method.
     *                          The optional registration information can be used to pre-fill the registration form.
     *
     * @throws FatalErrorException Thrown if the authentication method does not support registration, or if the arguments array or any of the
     *                                  arguments are invalid.
     */
    public function checkPasswordForRegistration(array $args)
    {
        throw new FatalErrorException($this->__('Registration authentication is not supported by this authentication method.'));

        return false;
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

    /**
     * Retrieve the account recovery information for the specified user.
     *
     * The array returned by this function should be an empty array (not null) if the specified user does not have any
     * authentication methods registered with the authentication module that are enabled for log-in.
     *
     * If the specified user does have one or more authentication methods, then the array should contain one or more elements
     * indexed numerically. Each element should be an associative array containing the following:
     *
     * - 'modname' The authentication module name.
     * - 'short_description' A brief (a few words) description or name of the authentication method.
     * - 'long_description' A longer description or name of the authentication method.
     * - 'uname' The user name _equivalent_ for the authentication method (e.g., the claimed OpenID).
     * - 'link' If the authentication method is for an external service, then a link to the user's account on that service, or a general link to the service,
     *            otherwise, an empty string (not null).
     *
     * For example:
     *
     * <code>
     * $accountRecoveryInfo[] = [
     *     'modname'           => $this->name,
     *     'short_description' => $this->__('E-mail Address'),
     *     'long_description'  => $this->__('E-mail Address'),
     *     'uname'             => $userObj['email'],
     *     'link'              => '',
     * ]
     * </code>
     *
     * Parameters passed in the $arg array:
     * ------------------------------------
     * numeric 'uid' The user id of the user for which account recovery information should be retrieved.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return An array of account recovery information.
     */
    abstract public function getAccountRecoveryInfoForUid(array $args);
}
