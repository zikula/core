<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Api;

use LogUtil;
use ModUtil;
use UserUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Helper\AuthenticationMethodHelper;
use Zikula_Api_AbstractAuthentication;

/**
 * @deprecated 
 * The user authentication services for the log-in process through the core Users table.
 */
class AuthenticationApi extends \Zikula_Api_AbstractAuthentication
{
    /**
     * The list of valid authentication methods that this module supports.
     *
     * This list is meant to be immutable, therefore it would be prudent to
     * only expose copies, and unwise to expose explicit references.
     *
     * @var array
     */
    protected $authenticationMethods = array();

    /**
     * Initialize the API instance with the list of valid authentication methods supported.
     *
     * @return void
     */
    protected function postInitialize()
    {
        parent::postInitialize();

        $loginViaOption = $this->getVar(UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::LOGIN_METHOD_UNAME);

        // Register the uname authentication method
        $authenticationMethod = new AuthenticationMethodHelper(
                $this->name,
                'uname',
                $this->__('User name'),
                $this->__('User name and password'),
                true,
                "fa-user"
        );
        if (($loginViaOption == UsersConstant::LOGIN_METHOD_UNAME)) {
            $authenticationMethod->enableForAuthentication();
            $authenticationMethod->enableForRegistration();
        } else {
            $authenticationMethod->disableForAuthentication();
            $authenticationMethod->disableForRegistration();
        }
        $this->authenticationMethods['uname'] = $authenticationMethod;

        // Register the email authentication method
        $authenticationMethod = new AuthenticationMethodHelper(
                $this->name,
                'email',
                $this->__('E-mail address'),
                $this->__('E-mail address and password'),
                true,
                "fa-envelope"
        );
        if (($loginViaOption == UsersConstant::LOGIN_METHOD_EMAIL)) {
            $authenticationMethod->enableForAuthentication();
            $authenticationMethod->enableForRegistration();
        } else {
            $authenticationMethod->disableForAuthentication();
            $authenticationMethod->disableForRegistration();
        }
        $this->authenticationMethods['email'] = $authenticationMethod;

        // Register the unameoremail authentication method
        $authenticationMethod = new AuthenticationMethodHelper(
            $this->name,
            'unameoremail',
            $this->__('User name or e-mail'),
            $this->__('User name / e-mail address and password'),
            true,
            "fa-user"
        );
        if ($loginViaOption == UsersConstant::LOGIN_METHOD_ANY) {
            $authenticationMethod->enableForAuthentication();
            $authenticationMethod->enableForRegistration();
        } else {
            $authenticationMethod->disableForAuthentication();
            $authenticationMethod->disableForRegistration();
        }
        $this->authenticationMethods['unameoremail'] = $authenticationMethod;
    }

    /**
     * Informs the calling function whether this authenticationModule is reentrant or not.
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
     * Indicate whether this module supports the indicated authentication method.
     *
     * @param string[] $args {
     *      @type string $method The name of the authentication method for which support is enquired.
     *                       }
     *
     * @return boolean True if the indicated authentication method is supported by this module; otherwise false.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function supportsAuthenticationMethod(array $args)
    {
        if (isset($args['method']) && is_string($args['method'])) {
            $methodName = $args['method'];
        } else {
            throw new \InvalidArgumentException($this->__('An invalid \'method\' parameter was received.'));
        }

        $isSupported = (bool)isset($this->authenticationMethods[$methodName]);

        return $isSupported;
    }

    /**
     * Indicates whether a specified authentication method that is supported by this module is enabled for use.
     *
     * @param string[] $args {
     *      @type string $method The name of the authentication method for which support is enquired.
     *                       }
     *
     * @return boolean True if the indicated authentication method is enabled by this module; otherwise false.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function isEnabledForAuthentication(array $args)
    {
        if (isset($args['method']) && is_string($args['method'])) {
            if (isset($this->authenticationMethods[$args['method']])) {
                $authenticationMethod = $this->authenticationMethods[$args['method']];
            } else {
                throw new \InvalidArgumentException($this->__f('An unknown method (\'%1$s\') was received.', array($args['method'])));
            }
        } else {
            throw new \InvalidArgumentException($this->__('An invalid \'method\' parameter was received.'));
        }

        return $authenticationMethod->isEnabledForAuthentication();
    }

    /**
     * Retrieves an array of authentication methods defined by this module, possibly filtered by only those that are enabled.
     *
     * @param int[] $args {
     *      @type int $filter Either {@link FILTER_ENABLED} (value 1), {@link FILTER_NONE} (value 0), or not present; allows the result to be filtered.
     *                        If this argument is FILTER_ENABLED, then only those authentication methods that are also enabled are returned.
     *                    }
     *
     * @return array An array containing the authentication methods defined by this module, possibly filtered by only those that are enabled.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function getAuthenticationMethods(array $args = null)
    {
        if (isset($args) && isset($args['filter'])) {
            if (is_numeric($args['filter']) && ((int)$args['filter'] == $args['filter'])) {
                switch ($args['filter']) {
                    case Zikula_Api_AbstractAuthentication::FILTER_NONE:
                    case Zikula_Api_AbstractAuthentication::FILTER_ENABLED:
                    case Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED:
                        $filter = $args['filter'];
                        break;
                    default:
                        throw new \InvalidArgumentException($this->__f('An unknown value for the \'filter\' parameter was received (\'%1$d\').', array($args['filter'])));
                        break;
                }
            } else {
                throw new \InvalidArgumentException($this->__f('An invalid value for the \'filter\' parameter was received (\'%1$s\').', array($args['filter'])));
            }
        } else {
            $filter = Zikula_Api_AbstractAuthentication::FILTER_NONE;
        }

        switch ($filter) {
            case Zikula_Api_AbstractAuthentication::FILTER_ENABLED:
                $authenticationMethods = array();
                foreach ($this->authenticationMethods as $index => $authenticationMethod) {
                    if ($authenticationMethod->isEnabledForAuthentication()) {
                        $authenticationMethods[$authenticationMethod->getMethod()] = $authenticationMethod;
                    }
                }
                break;
            case Zikula_Api_AbstractAuthentication::FILTER_REGISTRATION_ENABLED:
                $authenticationMethods = array();
                foreach ($this->authenticationMethods as $index => $authenticationMethod) {
                    if ($authenticationMethod->isEnabledForRegistration()) {
                        $authenticationMethods[$authenticationMethod->getMethod()] = $authenticationMethod;
                    }
                }
                break;
            default:
                $authenticationMethods = $this->authenticationMethods;
                break;
        }

        return $authenticationMethods;
    }

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
     * @throws \InvalidArgumentException Thrown if invalid parameters are sent in $args.
     */
    public function getAuthenticationMethod(array $args)
    {
        if (!isset($args['method'])) {
            throw new \InvalidArgumentException($this->__f('An invalid value for the \'method\' parameter was received (\'%1$s\').', array($args['method'])));
        }

        if (!isset($this->authenticationMethods[($args['method'])])) {
            throw new \InvalidArgumentException($this->__f('The requested authentication method \'%1$s\' does not exist.', array($args['method'])));
        }

        return $this->authenticationMethods[($args['method'])];
    }

    /**
     * Registers a user account record or a user registration request with the authentication method.
     *
     * This is called during the user registration process to associate an authentication method provided by this authentication module
     * with a user (either a full user account, or a user's registration request).
     *
     * @param mixed[] $args {
     *      @type array   $authentication_method Not used by the Users module.
     *      @type array   $authentication_info   Not used by the Users module.
     *      @type numeric $uid                   Not used by the Users module.
     *                      }
     *
     * @param array $args All parameters passed to this function.
     *
     * @return boolean True if the user account or registration request was successfully associated with the authentication method and
     *                      authentication information; otherwise false.
     *
     * @throws \RuntimeException Thrown in all cases by the Users module. This module handles registrations as part of the core functionality.
     */
    public function register(array $args)
    {
        throw new \RuntimeException($this->__f('The %1$s function is not implemented for the %1$s module. This core module handles registration of authentication information as part of the core registration process.', array('register()', 'ZikulaUsersModule')));
    }

    /**
     * Authenticates authentication_info with the authenticating source, returning a simple boolean result.
     *
     * Note that, despite this function's name, there is no requirement that a password be part of the authentication_info.
     * Merely that enough information be provided in the authentication_info array to unequivocally authenticate the user. For
     * most authenticating authorities this will be the equivalent of a user name and password, but--again--there
     * is no restriction here. This is not, however, a "user exists in the system" function. It is expected that
     * the authenticating authority validate what ever is used as a password or the equivalent thereof.
     *
     * This function makes no attempt to match the given authentication_info with a Zikula user id (uid). It simply asks the
     * authenticating authority to authenticate the authentication_info provided. No "login" should take place as a result of
     * this authentication.
     *
     * This function may be called to initially authenticate a user during the registration process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes.
     *
     * This function differs from authenticateUser() in that no attempt is made to match the authentication_info with and map to a
     * Zikula user account. It does not return a Zikula user id (uid).
     *
     * @param array[] $args {
     *           @type array $authentication_info   The information needed for this authenticationModule, including any user-entered
     *                                              information. For the Users module, this contains the elements 'login_id' and 'pass'.
     *                                              The 'login_id' element contains either the user name or the e-mail address of the
     *                                              user logging in, depending on the authentication_method. The 'pass' contains the
     *                                              password entered by the user.
     *           @type array $authentication_method An array containing the authentication method, including the 'modname' (which should match this
     *                                              module's module name), and the 'method' method name. For the Users module, 'modname' would
     *                                              be 'ZikulaUsersModule' and 'method' would contain either 'email', 'uname' or 'unameoremail'.
     *                      }
     *
     * @return boolean True if the authentication_info authenticates with the source; otherwise false on authentication failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     * @throws AccessDeniedException Thrown if the login fails due to invalid credentials
     */
    public function checkPassword(array $args)
    {
        // Note that this is a poor example function for external authenticationModules, because the authenticating
        // information for the Users module is stored in the users table, necessitating a lookup of the uid.
        //
        // For authentication modules other than the Users module, no attempt to look up the user account in the Users tables should be
        // made in the checkPassword function.

        if (!isset($args['authentication_info']) || !is_array($args['authentication_info']) || empty($args['authentication_info'])) {
            throw new \InvalidArgumentException($this->__f('Invalid \'%1$s\' parameter received in a call to %2$s', array('authentication_info', __METHOD__)));
        }

        if (!isset($args['authentication_method']) || !is_array($args['authentication_method']) || empty($args['authentication_method'])) {
            throw new \InvalidArgumentException($this->__f('Invalid \'%1$s\' parameter received in a call to %2$s', array('authentication_method', __METHOD__)));
        }

        $authenticationInfo = $args['authentication_info'];
        $authenticationMethod = $args['authentication_method'];

        $passwordAuthenticates = false;

        $getUidArgs = array(
            'authentication_info'   => $authenticationInfo,
            'authentication_method' => $authenticationMethod,
        );
        $uid = ModUtil::apiFunc($this->name, 'Authentication', 'getUidForAuthenticationInfo', $getUidArgs, 'Zikula_Api_AbstractAuthentication');

        if ($uid) {
            if (!isset($authenticationInfo['pass']) || !is_string($authenticationInfo['pass'])
                    || empty($authenticationInfo['pass'])) {
                // The user did not specify a password, or the one specified is invalid.
                throw new \InvalidArgumentException($this->__('Error! A password must be provided.'));
            }

            // For a custom authenticationModule, we'd map the authenticationInfo to a uid above, and then execute the custom
            // authentication process here. On success the uid would be returned, otherwise false is returned. Note that
            // any "log in" into the Zikula site is not done here. This is simply verification that the authenticationInfo,
            // including the password, is valid as a unit.

            $userObj = UserUtil::getVars($uid, true);
            if (!$userObj) {
                // Must be a registration. Acting as an authenticationModule, we should not care at this point about the user's
                // account status. We will deal with the account status in a moment.
                $userObj = UserUtil::getVars($uid, true, '', true);

                if (!$userObj) {
                    // Neither an account nor a pending registration request. This should really not happen since we have a uid.
                    throw new \InvalidArgumentException($this->__f('A user id was located, but the user account record could not be retrieved in a call to %1$s.', array(__METHOD__)));
                }
            }

            // Check for an empty password, or the special marker indicating that the account record does not
            // authenticate with a uname/password (or email/password or unameoremail/password, depending on the 'loginviaoption' setting) from
            // the Users module. An empty password can be created when an administrator creates a user registration
            // record pending e-mail verification and does not set a password for the user (the user will set it
            // upon verifying his email address). The special marker indicating that the account does not authenticate
            // with the Users module is used when a user registers a new account with the system using an authentication
            // method other than uname/pass, email/pass or unameoremail/pass. In both cases, authentication automatically fails.
            if (!empty($userObj['pass']) && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
                // The following check for non-salted passwords and the old 'hash_method' field is to allow the admin to log in
                // during an upgrade from 1.2.
                // *** IMPORTANT ***
                // This needs to be kept for any version that allows an upgrade from Zikula 1.2.X.
                $methodSaltDelimPosition = strpos($userObj['pass'], UsersConstant::SALT_DELIM);
                $saltPassDelimPosition = ($methodSaltDelimPosition === false) ? false : strpos($userObj['pass'], UsersConstant::SALT_DELIM, ($methodSaltDelimPosition + 1));
                if ($saltPassDelimPosition === false) {
                    // Old style unsalted password with hash_method in separate field
                    // If this release version of Zikula Users Module allows upgrade from 1.2.X, then this part must be
                    // kept. If this release version of Zikula Users Module DOES NOT support upgrade from 1.2.X then this
                    // is the part that can go away.
                    if (!isset($userObj['hash_method'])) {
                        // Something is horribly wrong. The password on the user account record does not look like the
                        // new style of hashing, and yet the old-style hash method field is nowhere to be found.
                        throw new \InvalidArgumentException($this->__('Invalid account password state.'));
                    }
                    $currentPasswordHashed = $userObj['hash_method'] . '$$' . $userObj['pass'];
                } else {
                    // New style salted password including hash method code.
                    // If this release version of Zikula Users module does not allow upgrade from 1.2.X, then this
                    // is the part to keep.
                    $currentPasswordHashed = $userObj['pass'];
                }
                // *** IMPORTANT ***
                // End of old-style versus new-style hashing handling. When the possiblity to upgrade from 1.2.X is
                // removed from the released version of Zikula Users Module, then delete this section, and replace
                // $currentPasswordHashed with $userObj['pass'] in the call to passwordsMatch below.

                if (UserUtil::passwordsMatch($authenticationInfo['pass'], $currentPasswordHashed)) {
                    // Password in $authenticationInfo['pass'] is good at this point.

                    // *** IMPORTANT ***
                    // Again, this section is for converting old-style hashing to new-style hashing. Same as noted
                    // above applies to this section.
                    // See if we need to convert the password hashing to the new configuration.
                    if (version_compare($this->modinfo['version'], '2.0.0') >= 0) {
                        // Check stored hash matches the current system type, if not convert it--but only if the module version is sufficient.
                        // Note: this is purely specific to the Users module authentication. A custom module might do something similar if it
                        // changed the way it stored some piece of data between versions, but in general this would be uncommon.
                        list($currentPasswordHashCode, $currentPasswordSaltStr, $currentPasswordHashStr) = explode(UsersConstant::SALT_DELIM, $currentPasswordHashed);
                        $systemHashMethodCode = UserUtil::getPasswordHashMethodCode($this->getVar('hash_method', 'sha256'));
                        if (($systemHashMethodCode != $currentPasswordHashCode) || empty($currentPasswordSaltStr)) {
                            if (!UserUtil::setPassword($authenticationInfo['pass'], $uid)) {
                                LogUtil::log($this->__('Internal Error! Unable to update the user\'s password with the new hashing method and/or salt.'), 'CORE');
                            }
                        }
                    }
                    // *** IMPORTANT ***
                    // End of old-style to new-style hashing conversion.

                    // The password is good, so the password is authenticated.
                    $passwordAuthenticates = true;
                }
            }
        }

        if (!$passwordAuthenticates && !LogUtil::hasErrors()) {
            if ($authenticationMethod['method'] == 'email') {
                throw new AccessDeniedException($this->__('Sorry! The e-mail address or password you entered was incorrect.'));
            } else {
                throw new AccessDeniedException($this->__('Sorry! The user name or password you entered was incorrect.'));
            }
        }

        return $passwordAuthenticates;
    }

    /**
     * Returns a "clean" version of the authenticationInfo used to log in, without any password-like information.
     *
     * This function strips off any password-like information from an authenticationInfo array, leaving only user name-like
     * identifying information. This "clean" authenticationInfo is intended to be stored along with the session, and the
     * session is not a secure place to retain password-like information.
     *
     * @param array[] $args {
     *        @type array $authentication_info The information needed for this authenticationModule, including any user-entered
     *                                          information. For the Users module, this contains the elements 'login_id' and 'pass'.
     *                                          The 'login_id' element contains either the user name or the e-mail address of the
     *                                          user logging in, depending on the authentication_method. The 'pass' contains the
     *                                          password entered by the user.
     *                      }
     *
     * @return array A "clean" version of the authenticationInfo passed in, devoid of any password-like fields, but retaining
     *                  enough information--such as user name-like fields--to identify the account on the authenticating
     *                  system.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function getAuthenticationInfoForSession(array $args)
    {
        // Validate authenticationInfo
        if (!isset($args['authentication_info']) || !is_array($args['authentication_info'])
                || empty($args['authentication_info'])) {
            throw new \InvalidArgumentException($this->__f('Invalied \'%1$s\' parameter received in a call to %2$s', array('authentication_info', __METHOD__)));
        }

        $authenticationInfo = $args['authentication_info'];

        $fieldsToClean = array(
            'pass',
            'new_pass',
            'confirm_new_pass',
            'pass_reminder',
        );
        foreach ($fieldsToClean as $fieldName) {
            if (array_key_exists($fieldName, $authenticationInfo)) {
                unset($authenticationInfo[$fieldName]);
            }
        }

        return $authenticationInfo;
    }

    /**
     * Retrieves the Zikula User ID (uid) for the given authenticationInfo, from the mapping maintained by this authenticationModule.
     *
     * Custom authenticationModules should pay extra special attention to the accurate association of authenticationInfo and user
     * ids (uids). Returning the wrong uid for a given authenticationInfo will potentially expose a user's account to
     * unauthorized access. Custom authenticationModules must also ensure that they keep their mapping table in sync with
     * the user's account.
     *
     * Note: (Specific to Zikula Users module authentication) This function uses mb_strtolower, and assumes that
     * locale == charset.
     *
     * @param mixed[] $args {
     *         @type array $authentication_info   The information needed for this authenticationModule, including any user-entered
     *                                            information. For the Users module, this contains the elements 'login_id' and 'pass'.
     *                                            The 'login_id' element contains either the user name or the e-mail address of the
     *                                            user logging in, depending on the authentication_method. The 'pass' contains the
     *                                            password entered by the user.
     *         @type array $authentication_method An array containing the authentication method, including the 'modname' (which should match this
     *                                            module's module name), and the 'method' method name. For the Users module, 'modname' would
     *                                            be 'ZikulaUsersModule' and 'method' would contain either 'email', 'uname' or 'unameoremail'.
     *                      }
     *
     * @return integer|boolean The integer Zikula uid uniquely associated with the given authenticationInfo;
     *                          otherwise false if user not found or error.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args, or if the data cannot be loaded from the database.
     */
    public function getUidForAuthenticationInfo(array $args)
    {
        // authenticationInfo can contain anything necessary for the authentication method, but most of the time will contain
        // a login ID of some sort, and a password. Set up authenticationInfo in templates as name="authenticationInfo[fieldname]" to
        // gather what is needed. In this case, we don't care about any password that might be in authenticationInfo.

        $authenticatedUid = false;

        // Validate authenticationInfo
        if (!isset($args['authentication_info']) || !is_array($args['authentication_info'])
                || empty($args['authentication_info'])) {
            throw new \InvalidArgumentException($this->__f('Invalid \'%1$s\' parameter provided in a call to %2$s.', array('authentication_info', __METHOD__)));
        }
        $authenticationInfo = $args['authentication_info'];

        if (!isset($args['authentication_method']) || !is_array($args['authentication_method'])
                || empty($args['authentication_method'])) {
            throw new \InvalidArgumentException($this->__f('Invalid \'%1$s\' parameter provided in a call to %2$s.', array('authentication_method', __METHOD__)));
        }
        $authenticationMethod = $args['authentication_method'];

        // Custom authenticationModules can expect whatever they need in authentication_info. The authentication_method
        // parameter will contain the module name (which is a bit redundant) and the specific method name.

        $loginID = $authenticationInfo['login_id'];

        if (!isset($loginID) || (is_string($loginID) && empty($loginID))) {
            if ($authenticationMethod == 'email') {
                $detailedMessage = $this->__f('An e-mail address was not provided in a call to %1$s.', array(__METHOD__));
            } else {
                $detailedMessage = $this->__f('A user name was not provided in a call to %1$s.', array(__METHOD__));
            }
            throw new \InvalidArgumentException($detailedMessage);
        } elseif (!is_string($loginID)) {
            throw new \InvalidArgumentException($this->__f('Invalid type for \'%1$s\' parameter in a call to %2$s.', array('login_id', __METHOD__)));
        }

        // The users module expects the loginID to be lower case. Custom authenticationModules would do whatever
        // they needed here, if anything.
        $loginID = mb_strtolower($loginID);

        // Look up the authenticationInfo in the authentication-source to/from Zikula uid mapping table.
        //
        // Note: the following is a bad example for custom modules because there no mapping table for the Users module.
        // A custom authentication module would look up a uid using its own mapping tables, not the users table or UserUtil.
        if ($authenticationMethod['method'] == 'email' || ($authenticationMethod['method'] == 'unameoremail' && filter_var($loginID, FILTER_VALIDATE_EMAIL))) {
            $authenticatedUid = UserUtil::getIdFromEmail($loginID);
            if (!$authenticatedUid) {
                // Might be a registration. Acting as an authenticationModule, we should not care at this point about the user's
                // account status. The account status is something for UserUtil::loginUsing() to deal with after we
                // tell it whether the account authenticates or not.
                $authenticatedUid = UserUtil::getIdFromEmail($loginID, true);
            }
        } elseif ($authenticationMethod['method'] == 'uname' || ($authenticationMethod['method'] == 'unameoremail' && preg_match('/^'. UsersConstant::UNAME_VALIDATION_PATTERN .'$/uD', $loginID))) {
            $authenticatedUid = UserUtil::getIdFromName($loginID);
            if (!$authenticatedUid) {
                // Might be a registration. See above.
                $authenticatedUid = UserUtil::getIdFromName($loginID, true);
            }
        }

        return $authenticatedUid;
    }

    /**
     * Authenticates authenticationInfo with the authenticating source, returns the Zikula user account record for the user.
     *
     * This function may be called to initially authenticate a user during the login process, or may be called
     * for a user already logged in to re-authenticate his password for a security-sensitive operation. This function
     * should merely authenticate the user, and not perform any additional login-related processes. It should not validate
     * any account status. Account status is not authentication.
     *
     * @param mixed[] $args {
     *         @type array $authentication_info   The information needed for this authenticationModule, including any user-entered
     *                                            information. For the Users module, this contains the elements 'login_id' and 'pass'.
     *                                            The 'login_id' element contains either the user name or the e-mail address of the
     *                                            user logging in, depending on the authentication_method. The 'pass' contains the
     *                                            password entered by the user.
     *         @type array $authentication_method An array containing the authentication method, including the 'modname' (which should match this
     *                                            module's module name), and the 'method' method name. For the Users module, 'modname' would
     *                                            be 'ZikulaUsersModule' and 'method' would contain either 'email', 'uname' or 'unameoremail'.
     *                      }
     *
     * @return integer|boolean If the authenticationInfo authenticates with the source, then the Zikula uid associated with that login ID;
     *                          otherwise false on authentication failure or error.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function authenticateUser(array $args)
    {
        $authenticatedUid = false;

        $checkPassword = ModUtil::apiFunc($this->name, 'Authentication', 'checkPassword', $args, 'Zikula_Api_AbstractAuthentication');
        if ($checkPassword) {
            $authenticatedUid = ModUtil::apiFunc($this->name, 'Authentication', 'getUidForAuthenticationInfo', $args, 'Zikula_Api_AbstractAuthentication');

            if (!$authenticatedUid) {
                if ($args['authentication_method']['method'] == 'email') {
                    throw new \InvalidArgumentException($this->__('Sorry! The e-mail address or password you entered was incorrect.'));
                } else {
                    throw new \InvalidArgumentException($this->__('Sorry! The user name or password you entered was incorrect.'));
                }
            }
        }

        return $authenticatedUid;
    }

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
     * $accountRecoveryInfo[] = array(
     *     'modname'           => $this->name,
     *     'short_description' => $this->__('E-mail Address'),
     *     'long_description'  => $this->__('E-mail Address'),
     *     'uname'             => $userObj['email'],
     *     'link'              => '',
     * )
     * </code>
     *
     * @param int[] $args {
     *      @type int $uid The user id of the user for which account recovery information should be retrieved.
     *                    }
     *
     * @return An array of account recovery information.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function getAccountRecoveryInfoForUid(array $args)
    {
        if (!isset($args) || empty($args)) {
            throw new \InvalidArgumentException($this->__('An invalid parameter array was received.'));
        }

        $uid = isset($args['uid']) ? $args['uid'] : false;
        if (!isset($uid) || !is_numeric($uid) || ((string)((int)$uid) != $uid)) {
            throw new \InvalidArgumentException($this->__('An invalid user id was received.'));
        }

        $userObj = UserUtil::getVars($uid);

        $lostUserNames = array();
        if ($userObj) {
            if (!empty($userObj['pass']) && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
                $loginOption = $this->getVar(UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::DEFAULT_LOGIN_METHOD);

                if (($loginOption == UsersConstant::LOGIN_METHOD_UNAME) || ($loginOption == UsersConstant::LOGIN_METHOD_ANY)) {
                    $lostUserNames[] = array(
                        'modname'           => $this->name,
                        'short_description' => $this->__('User name'),
                        'long_description'  => $this->__('User name'),
                        'uname'             => $userObj['uname'],
                        'link'              => '',
                    );
                }

                if (($loginOption == UsersConstant::LOGIN_METHOD_EMAIL) || ($loginOption == UsersConstant::LOGIN_METHOD_ANY)) {
                    $lostUserNames[] = array(
                        'modname'           => $this->name,
                        'short_description' => $this->__('E-mail Address'),
                        'long_description'  => $this->__('E-mail Address'),
                        'uname'             => $userObj['email'],
                        'link'              => '',
                    );
                }
            }
        }

        return $lostUserNames;
    }

    /**
     * Check whether the user shall be redirected to the registration screen if the login process fails.
     *
     * Possible reasons for the login process to fail:
     * - User does not exist yet.
     * - User provides wrong credentials.
     *
     * @param array $args {
     *     @type array $authentication_method An array identifying the selected authentication method by 'modname' and 'method'.
     *     @type array $authentication_info   An array containing the authentication information supplied by the user; for this module, a 'supplied_id'.
     * }
     *
     * @return bool True if the user shall be redirected to the registration screen, false otherwise.
     */
    public function redirectToRegistrationOnLoginError(array $args)
    {
        unset($args);

        return false;
    }
}
