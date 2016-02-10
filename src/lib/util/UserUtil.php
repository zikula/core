<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Exception\ExtensionNotAvailableException;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * UserUtil
 */
class UserUtil
{
    /**
     * Cache for groups.
     *
     * @var array
     */
    protected static $groupCache = array();

    /**
     * Clear group cache.
     *
     * @return void
     */
    public function clearGroupCache()
    {
        self::$groupCache = array();
    }

    /**
     * Return a hash structure mapping uid to username.
     *
     * @param array   $where        Array of field values to filter by (optional, default=array()).
     * @param array   $orderBy      Array fields to sort by (optional, default=array()).
     * @param integer $limitOffset  The select-limit offset (optional, default=null).
     * @param integer $limitNumRows The number of rows to fetch (optional, default=null).
     * @param string  $assocKey     The associative key to apply (optional) (default='uid').
     *
     * @deprecated since 1.3.0
     *
     * @return array An array mapping uid to username.
     */
    public static function getUsers($where = array(), $orderBy = array(), $limitOffset = null, $limitNumRows = null, $assocKey = 'uid')
    {
        // first check for string based parameters and use dbutil if found
        if (System::isLegacyMode() && (is_string($where) || is_string($orderBy))) {
            if ($where == array()) {
                $where = '';
            }
            if ($orderBy == array()) {
                $orderBy = '';
            }

            return DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
        }

        // we've now ruled out BC parameters
        $em = \ServiceUtil::get('doctrine.entitymanager');
        $users = $em->getRepository('ZikulaUsersModule:UserEntity')->findBy($where, $orderBy, $limitNumRows, $limitOffset);

        $items = array();
        foreach ($users as $user) {
            $items[$user[$assocKey]] = $user->toArray();
        }

        return $items;
    }

    /**
     * Return a group object.
     *
     * @param integer $gid The groupID to retrieve.
     *
     * @todo   Decouple UserUtil and Groups?
     *
     * @return array The resulting group object.
     */
    public static function getGroup($gid)
    {
        return ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));
    }

    /**
     * Return a hash structure mapping gid to groupname.
     *
     * @param string  $where        The where clause to use (optional) (default=array()).
     * @param string  $orderBy      The order by clause to use (optional) (default=array()).
     * @param integer $limitOffset  The select-limit offset (optional) (default=null).
     * @param integer $limitNumRows The number of rows to fetch (optional) (default=null).
     * @param string  $assocKey     The associative key to apply (optional) (default='gid').
     *
     * @return array An array mapping gid to groupname.
     */
    public static function getGroups($where = array(), $orderBy = array(), $limitOffset = null, $limitNumRows = null, $assocKey = 'gid')
    {
        $em = \ServiceUtil::get('doctrine.entitymanager');
        $groups = $em->getRepository('ZikulaGroupsModule:GroupEntity')->findBy($where, $orderBy, $limitNumRows, $limitOffset);

        $items = array();
        foreach ($groups as $group) {
            $items[$group[$assocKey]] = $group->toArray();
        }

        return $items;
    }

    /**
     * Return a (string) list of user-ids which can then be used in a SQL 'IN (...)' clause.
     *
     * @param string $where     The where clause to use (optional).
     * @param string $orderBy   The order by clause to use (optional).
     * @param string $separator The field separator to use (default=",") (optional).
     *
     * @return string A string list of user ids.
     */
    public static function getUserIdList($where = '', $orderBy = '', $separator = ',')
    {
        $userdata = self::getUsers($where, $orderBy);

        $list = '-1';
        if ($userdata && count($userdata)) {
            $uids = array_keys($userdata);
            sort($uids);
            $list = implode((string)$separator, $uids);
        }

        return $list;
    }

    /**
     * Return a (string) list of group-ids which can then be used in a SQL 'IN (...)' clause.
     *
     * @param string $where     The where clause to use (optional).
     * @param string $orderBy   The order by clause to use (optional).
     * @param string $separator The field separator to use (default=",") (optional).
     *
     * @return string A string list of group ids.
     */
    public static function getGroupIdList($where = array(), $orderBy = array(), $separator = ',')
    {
        $groupdata = self::getGroups($where, $orderBy);

        $list = '';
        if ($groupdata && count($groupdata)) {
            $gids = array_keys($groupdata);
            sort($gids);
            $list = implode((string)$separator, $gids);
        }

        return $list;
    }

    /**
     * Return a (string) list of user-ids which can then be used in a SQL 'IN (...)' clause.
     *
     * @param string $where     The where clause to use (optional).
     * @param string $orderBy   The order by clause to use (optional).
     * @param string $separator The field separator to use (default=",") (optional).
     *
     * @deprecated since 1.3.0
     * @see    UserUtil::getUserIdList()
     *
     * @return string A string list of user ids.
     */
    public static function getPNUserIdList($where = '', $orderBy = '', $separator = ',')
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getUserIdList')), E_USER_DEPRECATED);

        return self::getUserIdList($where, $orderBy, $separator);
    }

    /**
     * Return an array of group-ids for the specified user.
     *
     * @param integer $uid The user ID for which we want the groups.
     *
     * @return array An array of group IDs.
     */
    public static function getGroupsForUser($uid)
    {
        if (empty($uid)) {
            return array();
        }

        return ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getusergroups', array('uid' => $uid, 'clean' => true));
    }

    /**
     * Return a string list of group-ids for the specified user.
     *
     * @param integer $uid       The user ID for which we want the groups.
     * @param string  $separator The field separator to use (default=",") (optional).
     *
     * @return string A string list of group ids.
     */
    public static function getGroupListForUser($uid = null, $separator = ',')
    {
        if (!$uid) {
            $uid = self::getVar('uid');
        }

        if (!$uid) {
            return '-1';
        }

        if (!isset(self::$groupCache[$uid])) {
            $gidArray = self::getGroupsForUser($uid);

            if ($gidArray && (bool)count($gidArray)) {
                sort($gidArray);
                self::$groupCache[$uid] = implode((string)$separator, $gidArray);
            } else {
                self::$groupCache[$uid] = '-1';
            }
        }

        return self::$groupCache[$uid];
    }

    /**
     * Return a string list of user-ids for the specified group.
     *
     * @param integer $gid The group ID for which we want the users.
     *
     * @return array An array of user IDs.
     */
    public static function getUsersForGroup($gid)
    {
        if (!$gid) {
            return array();
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $gid));

        $members = $group['members'];

        $uids = array();

        foreach ($members as $uid => $membership) {
            $uids[] = $uid;
        }

        return $uids;
    }

    /**
     * Get a unique string for a user, depending on this group memberships.
     *
     * String ready to be used as part of the CacheID of the output views.
     * Useful when there aren't another user-based access privilegies, just group permissions.
     *
     * @param integer $uid User ID to get the group memberships from. Default: current user.
     *
     * @return string Cache GIDs string to use on Zikula_View.
     */
    public static function getGidCacheString($uid = null)
    {
        $str = self::getGroupListForUser($uid, '_');

        return $str == '-1' ? 'guest' : 'groups_'.$str;
    }

    /**
     * Get a unique string for a user, based on the uid.
     *
     * String ready to be used as part of the CacheID of the output views.
     * Useful for user-based access privilegies.
     *
     * @param integer $uid User ID to get string from. Default: current user.
     *
     * @return string Cache UID string to use on Zikula_View.
     */
    public static function getUidCacheString($uid = null)
    {
        $uid = $uid ? (int)$uid : self::getVar('uid');

        return !$uid ? 'guest' : 'uid_'.$uid;
    }

    /**
     * Return the defined dynamic user data fields.
     *
     * @return array An array of dynamic data field definitions.
     */
    public static function getDynamicDataFields()
    {
        // decide if we have to use the (obsolete) DUDs from the Profile module
        $profileModule = System::getVar('profilemodule', '');

        if (empty($profileModule) || $profileModule != 'Profile' || !ModUtil::available($profileModule)) {
            return array();
        }

        return DBUtil::selectObjectArray('user_property');
    }

    /**
     * Return a array structure for the user group selector.
     *
     * @param mixed  $defaultValue The default value of the selector (default=0) (optional).
     * @param string $defaultText  The text of the default value (optional).
     * @param array  $ignore       An array of keys to ignore (optional).
     * @param mixed  $includeAll   Whether to include an "All" choice (optional).
     * @param string $allText      The text to display for the "All" choice (optional).
     *
     * @return array The array structure for the user group selector.
     */
    public static function getSelectorData_Group($defaultValue = 0, $defaultText = '', $ignore = array(), $includeAll = 0, $allText = '')
    {
        $dropdown = array();

        if ($defaultText) {
            $dropdown[] = array('id' => $defaultValue, 'name' => $defaultText);
        }

        $groupdata = self::getGroups(array(), array('name' => 'ASC'));

        if (!$groupdata || !count($groupdata)) {
            return $dropdown;
        }

        if ($includeAll) {
            $dropdown[] = array('id' => $includeAll, 'name' => $allText);
        }

        foreach (array_keys($groupdata) as $gid) {
            if (!isset($ignore[$gid])) {
                $gname = $groupdata[$gid]['name'];
                $dropdown[$gname] = array('id' => $gid, 'name' => $gname);
            }
        }

        ksort($dropdown);

        return $dropdown;
    }

    /**
     * Return a array strcuture for the user dropdown box.
     *
     * @param mixed $defaultValue The default value of the selector (optional) (default=0).
     * @param string $defaultText  The text of the default value (optional) (default='').
     * @param array  $ignore       An array of keys to ignore (optional) (default=array()).
     * @param mixed $includeAll   Whether to include an "All" choice (optional) (default=0).
     * @param string $allText      The text to display for the "All" choice (optional) (default='').
     * @param string $exclude      An SQL IN-LIST string to exclude specified uids.
     *
     * @return array The array structure for the user group selector.
     */
    public static function getSelectorData_User($defaultValue = 0, $defaultText = '', $ignore = array(), $includeAll = 0, $allText = '', $exclude = '')
    {
        $dropdown = array();

        if ($defaultText) {
            $dropdown[] = array('id' => $defaultValue, 'name' => $defaultText);
        }

        $where = '';
        if ($exclude) {
            $where = "WHERE uid NOT IN (" . DataUtil::formatForStore($exclude) . ")";
        }

        $userdata = self::getUsers($where, 'ORDER BY uname');

        if (!$userdata || !count($userdata)) {
            return $dropdown;
        }

        if ($includeAll) {
            $dropdown[] = array('id' => $includeAll, 'name' => $allText);
        }

        foreach (array_keys($userdata) as $uid) {
            if (!isset($ignore[$uid])) {
                $uname = $userdata[$uid]['uname'];
                $dropdown[$uname] = array('id' => $uid, 'name' => $uname);
            }
        }

        ksort($uname);

        return $dropdown;
    }

    /**
     * Retrieve the account recovery information for a user from the various authentication modules.
     *
     * @param integer $uid The user id of the user for which account recovery information should be retrieved; optional, defaults to the
     *                          currently logged in user (an exception occurs if the current user is not logged in).
     *
     * @return array An array of account recovery information.
     *
     * @throws InvalidArgumentException If the $uid parameter is not valid.
     * @throws NotFoundHttpException If the user is not logged in
     */
    public static function getUserAccountRecoveryInfo($uid = -1)
    {
        if (!isset($uid) || !is_numeric($uid) || ((string)((int)$uid) != $uid) || (($uid < -1) || ($uid == 0) || ($uid == 1))) {
            throw new \InvalidArgumentException('Attempt to get authentication information for an invalid user id.');
        }

        if ($uid == -1) {
            if (self::isLoggedIn()) {
                $uid = self::getVar('uid');
            } else {
                throw new NotFoundHttpException('Attempt to get authentication information for an unknown user id.');
            }
        }

        $userAuthenticationInfo = array();

        $authenticationModules = ModUtil::getModulesCapableOf(UsersConstant::CAPABILITY_AUTHENTICATION);
        if ($authenticationModules) {
            $accountRecoveryArgs = array(
                'uid' => $uid,
            );
            foreach ($authenticationModules as $authenticationModule) {
                $moduleUserAuthenticationInfo = ModUtil::apiFunc($authenticationModule['name'], 'authentication', 'getAccountRecoveryInfoForUid', $accountRecoveryArgs, 'Zikula_Api_AbstractAuthentication');
                if (is_array($moduleUserAuthenticationInfo)) {
                    $userAuthenticationInfo = array_merge($userAuthenticationInfo, $moduleUserAuthenticationInfo);
                }
            }
        }

        return $userAuthenticationInfo;
    }

    /**
     * Login.
     *
     * @param string  $loginID             Login Id.
     * @param string  $userEnteredPassword The Password.
     * @param boolean $rememberme          Whether or not to remember login.
     * @param boolean $checkPassword       Whether or not to check the password.
     *
     * @deprecated since 1.3.0
     * @see        UserUtil::loginUsing()
     *
     * @return boolean
     */
    public static function login($loginID, $userEnteredPassword, $rememberme = false, $checkPassword = true)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::loginUsing()')), E_USER_DEPRECATED);

        $authenticationInfo = array(
            'login_id' => $loginID,
            'pass'     => $userEnteredPassword,
        );
        $authenticationMethod = array(
            'modname'   => 'ZikulaUsersModule',
        );

        if (ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_METHOD, UsersConstant::DEFAULT_LOGIN_METHOD) == UsersConstant::LOGIN_METHOD_EMAIL) {
            $authenticationMethod['method'] = 'email';
        } else {
            $authenticationMethod['method'] = 'uname';
        }

        return self::loginUsing($authenticationMethod, $authenticationInfo, $rememberme, null, $checkPassword);
    }

    /**
     * Validation method previous authentication.
     *
     * @param array  $authenticationMethod Auth method.
     * @param string $reentrantURL         Reentrant URL (optional).
     *
     * @return true
     *
     * @throws InvalidArgumentException|ExtensionNotAvailableException|MethodNotAllowedHttpException
     */
    private static function preAuthenticationValidation(array $authenticationMethod, $reentrantURL = null)
    {
        if (empty($authenticationMethod) || (count($authenticationMethod) != 2)) {
            throw new \InvalidArgumentException(__f('An invalid %1$s parameter was received.', array('authenticationMethod')));
        }

        if (!isset($authenticationMethod['modname']) || !is_string($authenticationMethod['modname']) || empty($authenticationMethod['modname'])) {
            throw new \InvalidArgumentException(__f('An invalid %1$s parameter was received.', array('modname')));
        } elseif (!ModUtil::getInfoFromName($authenticationMethod['modname'])) {
            throw new \InvalidArgumentException(__f('The authentication module \'%1$s\' could not be found.', array($authenticationMethod['modname'])));
        } elseif (!ModUtil::available($authenticationMethod['modname'])) {
            throw new ExtensionNotAvailableException(__f('The authentication module \'%1$s\' is not available.', array($authenticationMethod['modname'])));
        } elseif (!ModUtil::loadApi($authenticationMethod['modname'], 'Authentication')) {
            throw new ExtensionNotAvailableException(__f('The authentication module \'%1$s\' could not be loaded.', array($authenticationMethod['modname'])));
        }

        if (!isset($authenticationMethod['method']) || !is_string($authenticationMethod['method']) || empty($authenticationMethod['method'])) {
            throw new \InvalidArgumentException(__f('An invalid %1$s parameter was received.', array('method')));
        } elseif (!ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'supportsAuthenticationMethod', array('method' => $authenticationMethod['method']), 'Zikula_Api_AbstractAuthentication')) {
            throw new MethodNotAllowedHttpException(__f('The authentication method \'%1$s\' is not supported by the authentication module \'%2$s\'.', array($authenticationMethod['method'], $authenticationMethod['modname'])));
        }

        if (ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'isReentrant', null, 'Zikula_Api_AbstractAuthentication') && (!isset($reentrantURL) || empty($reentrantURL))) {
            throw new \InvalidArgumentException(__f('The authentication module \'%1$s\' is reentrant. A %2$s is required.', array($authenticationMethod['modname'], 'reentrantURL')));
        }

        return true;
    }

    /**
     * Authenticate a user's credentials against an authentication module, without any attempt to log the user in or look up a Zikula user account record.
     *
     * NOTE: Checking a password with an authentication method defined by the Users module is a special case.
     * The password is stored along with the account information, therefore the account information has to be
     * looked up by the checkPassword function in that module. Authentication modules other than the Users module should
     * make no attempt to look up account information,
     *
     * This function is used to check that a user is who he says he is without any attempt to log the user into the
     * Zikula system or look up his account information or status. It could be used, for example, to check the user's
     * credentials prior to registering with an authentication method like OpenID or Google Federated Login.
     *
     * This function differs from {@link authenticateUserUsing()} in that it does not make any attempt to look up a Zikula account
     * record for the user (nor should the authentication method specified).
     *
     * This function differs from {@link loginUsing()} in that it does not make any attempt to look up a Zikula account
     * record for the user (nor should the authentication method specified), and additionally it makes no attempt to log the user into
     * the Zikula system.
     *
     * ATTENTION: The authentication module function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling checkPasswordUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param array  $authenticationMethod Authentication module and method name.
     * @param array  $authenticationInfo   Auth info array.
     * @param string $reentrantURL         If the authentication module needs to redirect to an external authentication server (e.g., OpenID), then
     *                                         this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                         state must have already been saved by the function calling checkPasswordUsing(), and the URL must
     *                                         point to a Zikula_AbstractController function that is equipped to detect reentry, restore the
     *                                         saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                         is only optional if the authentication module identified by $authenticationMethod reports that it is not
     *                                         reentrant (e.g., Users is guaranteed to not be reentrant).
     *
     * @return bool True if authentication info authenticates; otherwise false.
     */
    public static function checkPasswordUsing(array $authenticationMethod, array $authenticationInfo, $reentrantURL = null)
    {
        if (self::preAuthenticationValidation($authenticationMethod, $reentrantURL)) {
            // Authenticate the loginID and userEnteredPassword against the specified authentication module.
            // This should return the uid of the user logging in. Note that there are two routes here, both get a uid.
            $checkPasswordArgs = array(
                'authentication_info'   => $authenticationInfo,
                'authentication_method' => $authenticationMethod,
                'reentrant_url'         => $reentrantURL,
            );

            return ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'checkPassword', $checkPasswordArgs, 'Zikula_Api_AbstractAuthentication');
        } else {
            return false;
        }
    }

    /**
     * Authenticate a user's credentials against an authentication module, without any attempt to log the user in.
     *
     * This function is used to check that a user is who he says he is, and that he has a valid user account with the
     * Zikula system. No attempt is made to log the user in to the Zikula system. It could be used, for example, to check
     * the user's credentials and Zikula system accoun status prior to performing a sensitive operation.
     *
     * This function differs from {@link checkPasswordUsing()} in that it attempts to look up a Zikula account
     * record for the user, and takes the user's account status into account when returning a value.
     *
     * This function differs from {@link loginUsing()} in that it makes no attempt to log the user into the Zikula system.
     *
     * ATTENTION: The authentication module function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling authenticateUserUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param array  $authenticationMethod The name of the authentication module to use for authentication and the method name as defined by that module.
     * @param array  $authenticationInfo   The information needed by the authentication module for authentication, typically a loginID and pass.
     * @param string $reentrantURL         If the authentication module needs to redirect to an external authentication server (e.g., OpenID), then
     *                                         this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                         state must have already been saved by the function calling authenticateUserUsing(), and the URL must
     *                                         point to a Zikula_AbstractController function that is equipped to detect reentry, restore the
     *                                         saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                         is only optional if the authentication module identified by $authenticationMethod reports that it is not
     *                                         reentrant (e.g., Users is guaranteed to not be reentrant).
     *
     * @return mixed Zikula uid if the authentication info authenticates with the authentication module; otherwise false.
     */
    private static function internalAuthenticateUserUsing(array $authenticationMethod, array $authenticationInfo, $reentrantURL = null)
    {
        $authenticatedUid = false;

        if (self::preAuthenticationValidation($authenticationMethod, $reentrantURL)) {
            $authenticateUserArgs = array(
                'authentication_info'   => $authenticationInfo,
                'authentication_method' => $authenticationMethod,
                'reentrant_url'         => $reentrantURL,
            );
            $authenticatedUid = ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'authenticateUser', $authenticateUserArgs, 'Zikula_Api_AbstractAuthentication');
        }

        return $authenticatedUid;
    }

    private static function internalUserAccountValidation($uid, $reportErrors = false, $userObj = false)
    {
        if (!$uid || !is_numeric($uid) || ((int)$uid != $uid)) {
            // We got something other than a uid from the authentication process.

            if (!LogUtil::hasErrors() && $reportErrors) {
                LogUtil::registerError(__('Sorry! Login failed. The information you provided was incorrect.'));
            }
        } else {
            if (!$userObj) {
                // Need to make sure the Users module stuff is loaded and available, especially if we are authenticating during
                // an upgrade or install.
                ModUtil::loadApi('ZikulaUsersModule', 'user', true);

                // The user's credentials have authenticated with the authentication module's method, but
                // now we have to check the account status itself. If the account status would not allow the
                // user to log in, then we return false.
                $userObj = self::getVars($uid);

                if (!$userObj) {
                    // Might be a registration
                    $userObj = self::getVars($uid, false, 'uid', true);
                }
            }

            if (!$userObj || !is_array($userObj)) {
                // Note that we have not actually logged into anything yet, just authenticated.

                throw new NotFoundHttpException(__f('A %1$s (%2$s) was returned by the authenticating module, but a user account record (or registration request record) could not be found.', array('uid', $uid)));
            }

            if (!isset($userObj['activated'])) {
                // Provide a sane value.
                $userObj['activated'] = UsersConstant::ACTIVATED_INACTIVE;
            }

            if ($userObj['activated'] != UsersConstant::ACTIVATED_ACTIVE) {
                if ($reportErrors) {
                    $displayVerifyPending = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                    $displayApprovalPending = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                    if (($userObj['activated'] == UsersConstant::ACTIVATED_PENDING_REG) && ($displayApprovalPending || $displayVerifyPending)) {
                        $moderationOrder = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
                        if (!$userObj['isverified']
                                && (($moderationOrder == UsersConstant::APPROVAL_AFTER) || ($moderationOrder == UsersConstant::APPROVAL_ANY)
                                        || (!empty($userObj['approved_by'])))
                                && $displayVerifyPending
                                ) {
                            $message = __('Your request to register with this site is still waiting for verification of your e-mail address. Please check your inbox for a message from us.');
                        } elseif (empty($userObj['approved_by'])
                                && (($moderationOrder == UsersConstant::APPROVAL_BEFORE) || ($moderationOrder == UsersConstant::APPROVAL_ANY))
                                && $displayApprovalPending
                                ) {
                            $message = __('Your request to register with this site is still waiting for approval from a site administrator.');
                        }

                        if (isset($message) && !empty($message)) {
                            return LogUtil::registerError($message);
                        }
                        // It is a pending registration but the site admin elected to not display this to the user.
                        // No exception here because the answer is simply "no." This will fall through to return false.
                    } elseif (($userObj['activated'] == UsersConstant::ACTIVATED_INACTIVE) && ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS)) {
                        $message = __('Your account has been disabled. Please contact a site administrator for more information.');
                    } elseif (($userObj['activated'] == UsersConstant::ACTIVATED_PENDING_DELETE) && ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS)) {
                        $message = __('Your account has been disabled and is scheduled for removal. Please contact a site administrator for more information.');
                    } else {
                        $message = __('Sorry! Either there is no active user in our system with that information, or the information you provided does not match the information for your account.');
                    }
                    LogUtil::registerError($message);
                }
                $userObj = false;
            }
        }

        return $userObj;
    }

    /**
     * Authenticate a user's credentials against an authentication module, without any attempt to log the user in.
     *
     * This function is used to check that a user is who he says he is, and that he has a valid user account with the
     * Zikula system. No attempt is made to log the user in to the Zikula system. It could be used, for example, to check
     * the user's credentials and Zikula system accoun status prior to performing a sensitive operation.
     *
     * This function differs from {@link checkPasswordUsing()} in that it attempts to look up a Zikula account
     * record for the user, and takes the user's account status into account when returning a value.
     *
     * This function differs from {@link loginUsing()} in that it makes no attempt to log the user into the Zikula system.
     *
     * ATTENTION: The authentication module function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling authenticateUserUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param array  $authenticationMethod The name of the authentication module to use for authentication and the method name as defined by that module.
     * @param array  $authenticationInfo   The information needed by the authentication module for authentication, typically a loginID and pass.
     * @param string $reentrantURL         If the authentication module needs to redirect to an external authentication server (e.g., OpenID), then
     *                                          this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                          state must have already been saved by the function calling authenticateUserUsing(), and the URL must
     *                                          point to a Zikula_AbstractController function that is equipped to detect reentry, restore the
     *                                          saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                          is only optional if the authentication module identified by $authenticationMethod reports that it is not
     *                                          reentrant (e.g., Users is guaranteed to not be reentrant).
     * @param boolean $reportErrors If true, then when validation of the account's ability to log in is performed, if errors are detected then
     *                                          they will be reported through registering errors with Zikula's logging and error reporting system. If
     *                                          false, then error reporting is supressed, and only the return value will indicate success or failure.
     *
     * @return array|bool The user account record of the user with the given credentials, if his credentials authenticate; otherwise false
     */
    public static function authenticateUserUsing(array $authenticationMethod, array $authenticationInfo, $reentrantURL = null, $reportErrors = false)
    {
        $userObj = false;

        $authenticatedUid = self::internalAuthenticateUserUsing($authenticationMethod, $authenticationInfo, $reentrantURL);
        if ($authenticatedUid) {
            $userObj = self::internalUserAccountValidation($authenticatedUid, $reportErrors);
        }

        return $userObj;
    }

    /**
     * Authenticate a user's credentials against an authentication module, logging him into the Zikula system.
     *
     * If the user is already logged in, then this function should behave as if {@link authenticateUserUsing()} was called.
     *
     * This function is used to check that a user is who he says he is, and that he has a valid user account with the
     * Zikula system. If so, the user is logged in to the Zikula system (if he is not already logged in). This function
     * should be used only to log a user into the Zikula system.
     *
     * This function differs from {@link checkPasswordUsing()} in that it attempts to look up a Zikula account
     * record for the user, and takes the user's account status into account when returning a value. Additionally,
     * the user is logged into the Zikula system if his credentials are verified with the authentication module specified.
     *
     * This function differs from {@link authenticateUserUsing()} in that it attempts to log the user into the Zikula system,
     * if he is not already logged in. If he is already logged in, then it should behave similarly to authenticateUserUsing().
     *
     * ATTENTION: The authentication module function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling loginUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param array   $authenticationMethod Auth module name.
     * @param array   $authenticationInfo   Auth info array.
     * @param boolean $rememberMe           Whether or not to remember login.
     * @param string  $reentrantURL         If the authentication module needs to redirect to an external authentication server (e.g., OpenID), then
     *                                          this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                          state must have already been saved by the function calling loginUsing(), and the URL must
     *                                          point to a Zikula_AbstractController function that is equipped to detect reentry, restore the
     *                                          saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                          is only optional if the authentication module identified by $authenticationMethod reports that it is not
     *                                          reentrant (e.g., Users is guaranteed to not be reentrant), or if $checkPassword is false.
     * @param boolean $checkPassword        Whether or not to check the password.
     * @param boolean $preauthenticatedUser Whether ot not is a preauthenticated user.
     *
     * @return array|bool The user account record of the user that has logged in successfully, otherwise false
     *
     * @throws InvalidArgumentException|AccessDeniedException
     */
    public static function loginUsing(array $authenticationMethod, array $authenticationInfo, $rememberMe = false, $reentrantURL = null, $checkPassword = true, $preauthenticatedUser = null)
    {
        $userObj = false;

        if (self::preAuthenticationValidation($authenticationMethod, $authenticationInfo, $reentrantURL)) {
            // Authenticate the loginID and userEnteredPassword against the specified authentication module.
            // This should return the uid of the user logging in. Note that there are two routes here, both get a uid.
            // We do the authentication check first, before checking any account status information, because if the
            // person logging in cannot supply the proper credentials, then we should not show any detailed account status
            // to them. Instead they should just get the generic "no such user found or bad password" message.
            if ($checkPassword) {
                $authenticatedUid = self::internalAuthenticateUserUsing($authenticationMethod, $authenticationInfo, $reentrantURL, true);
            } elseif (isset($preauthenticatedUser)) {
                if (is_numeric($preauthenticatedUser)) {
                    $authenticatedUid = $preauthenticatedUser;
                } elseif (is_array($preauthenticatedUser)) {
                    $authenticatedUid = $preauthenticatedUser['uid'];
                    $userObj = $preauthenticatedUser;
                } else {
                    throw new \InvalidArgumentException();
                }
            } else {
                $authArgs = array(
                    'authentication_info'   => $authenticationInfo,
                    'authentication_method' => $authenticationMethod,
                );
                $authenticatedUid = ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'getUidForAuththenticationInfo', $authArgs, 'Zikula_Api_AbstractAuthentication');
            }

            $userObj = self::internalUserAccountValidation($authenticatedUid, true, isset($userObj) ? $userObj : null);
            if ($userObj && is_array($userObj)) {
                // BEGIN ACTUAL LOGIN
                // Made it through all the checks. We can actually log in now.

                // Give any interested module one last chance to prevent the login from happening.
                $eventArgs = array(
                    'authentication_method' => $authenticationMethod,
                    'uid'                   => $userObj['uid'],
                );
                $event = new GenericEvent($userObj, $eventArgs);
                $event = EventUtil::dispatch('user.login.veto', $event);

                if ($event->isPropagationStopped()) {
                    // The login attempt has been vetoed by one or more modules.
                    $eventData = $event->getData();

                    if (isset($eventData['retry']) && $eventData['retry']) {
                        $sessionVarName = 'Users_Controller_User_login';
                        $sessionNamespace = 'Zikula_Users';
                        $redirectURL = ModUtil::url('ZikulaUsersModule', 'user', 'login', array('csrftoken' => SecurityUtil::generateCsrfToken()));
                    } elseif (isset($eventData['redirect_func'])) {
                        if (isset($eventData['redirect_func']['session'])) {
                            $sessionVarName = $eventData['redirect_func']['session']['var'];
                            $sessionNamespace = isset($eventData['redirect_func']['session']['namespace']) ? $eventData['redirect_func']['session']['namespace'] : '';
                        }
                        $redirectURL = ModUtil::url($eventData['redirect_func']['modname'], $eventData['redirect_func']['type'], $eventData['redirect_func']['func'], $eventData['redirect_func']['args']);
                    }

                    if (isset($redirectURL)) {
                        if (isset($sessionVarName)) {
                            SessionUtil::requireSession();

                            $sessionVars = SessionUtil::getVar('Users_User_Controller_login', array(), 'Zikula_Users', false, false);

                            $sessionVars = array(
                                'returnpage'            => isset($sessionVars['returnpage']) ? $sessionVars['returnpage'] : '',
                                'authentication_info'   => $authenticationInfo,
                                'authentication_method' => $authenticationMethod,
                                'rememberme'            => $rememberMe,
                                'user_obj'              => $userObj,
                            );
                            SessionUtil::setVar($sessionVarName, $sessionVars, $sessionNamespace, true, true);
                        }
                        $userObj = false;
                        $response = new \Symfony\Component\HttpFoundation\RedirectResponse($redirectURL);
                        $response->send();
                        exit;
                    } else {
                        throw new AccessDeniedException();
                    }
                } else {
                    // The login has not been vetoed
                    // This is what really does the Zikula login
                    self::setUserByUid($userObj['uid'], $rememberMe, $authenticationMethod);
                }
            }
        }

        return $userObj;
    }

    /**
     * Sets the currently logged in active user to the user account for the given Users module uname.
     *
     * No events are fired from this function. To receive events, use {@link loginUsing()}.
     *
     * @param string  $uname      The user name of the user who should be logged into the system; required.
     * @param boolean $rememberMe If the user's login should be maintained on the computer from which the user is logging in, set this to true;
     *                                optional, defaults to false.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public static function setUserByUname($uname, $rememberMe = false)
    {
        if (!isset($uname) || !is_string($uname) || empty($uname)) {
            throw new \InvalidArgumentException(__('Attempt to set the current user with an invalid uname.'));
        }

        $uid = self::getIdFromName($uname);

        $authenticationMethod = array(
            'modname' => 'ZikulaUsersModule',
            'method'  => 'uname',
        );

        self::setUserByUid($uid, $rememberMe, $authenticationMethod);
    }

    /**
     * Sets the currently logged in active user to the user account for the given uid.
     *
     * No events are fired from this function. To receive events, use {@link loginUsing()}.
     *
     * @param integer $uid        The user id of the user who should be logged into the system; required.
     * @param boolean $rememberMe If the user's login should be maintained on the computer from which the user is logging in, set this to true;
     *                                          optional, defaults to false.
     * @param array $authenticationMethod An array containing the authentication method used to log the user in; optional,
     *                                          defaults to the 'ZikulaUsersModule' module 'uname' method.
     *
     * @return void
     *
     * @throws InvalidArgumentException|NotFoundHttpException|BadMethodCallException
     */
    public static function setUserByUid($uid, $rememberMe = false, array $authenticationMethod = null)
    {
        if (!isset($uid) || empty($uid) || ((string)((int)$uid) != $uid)) {
            throw new \InvalidArgumentException(__('Attempt to set the current user with an invalid uid.'));
        }

        $userObj = self::getVars($uid);
        if (!isset($userObj) || !is_array($userObj) || empty($userObj)) {
            throw new NotFoundHttpException(__('Attempt to set the current user with an unknown uid.'));
        }

        if (!isset($authenticationMethod)) {
            $authenticationMethod = array(
                'modname' => 'ZikulaUsersModule',
                'method'  => 'uname',
            );
        } elseif (empty($authenticationMethod) || !isset($authenticationMethod['modname']) || empty($authenticationMethod['modname'])
                || !isset($authenticationMethod['method']) || empty($authenticationMethod['method'])
                ) {
            throw new \BadMethodCallException(__('Attempt to set the current user with an invalid authentication method.'));
        }

        // Storing Last Login date -- store it in UTC! Do not use date() function!
        $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
        if (!self::setVar('lastlogin', $nowUTC->format('Y-m-d H:i:s'), $userObj['uid'])) {
            // show messages but continue
            LogUtil::registerError(__('Error! Could not save the log-in date.'));
        }

        if (!System::isInstalling()) {
            SessionUtil::requireSession();
        }

        // Set session variables -- this is what really does the Zikula login
        SessionUtil::setVar('uid', $userObj['uid']);
        SessionUtil::setVar('authentication_method', $authenticationMethod, 'Zikula_Users');

        if (!empty($rememberMe)) {
            SessionUtil::setVar('rememberme', 1);
        }

        // now that we've logged in the permissions previously calculated (if any) are invalid
        $GLOBALS['authinfogathered'][$userObj['uid']] = 0;
        // FC Core-2.0
        $container = \ServiceUtil::getManager();
        $container->get('zikula_permissions_module.api.permission')->resetPermissionsForUser($userObj['uid']);
    }

    /**
     * Log the user out.
     *
     * @return bool true if the user successfully logged out, false otherwise
     */
    public static function logout()
    {
        if (self::isLoggedIn()) {
            $userObj = self::getVars(self::getVar('uid'));
            $authenticationMethod = SessionUtil::delVar('authentication_method', array('modname' => '', 'method' => ''), 'Zikula_Users');

            session_destroy();
        }

        return true;
    }

    /**
     * Is the user logged in?
     *
     * @return bool true if the user is logged in, false if they are not
     */
    public static function isLoggedIn()
    {
        return (bool)SessionUtil::getVar('uid');
    }

    /**
     * Counts how many times a user name has been used by user accounts in the system.
     *
     * @param string $uname      The e-mail address in question (required).
     * @param int    $excludeUid The uid to exclude from the check, used when checking modifications.
     *
     * @return integer|boolean The count, or false on error.
     */
    public static function getUnameUsageCount($uname, $excludeUid = 0)
    {
        if (!is_numeric($excludeUid) || ((int)$excludeUid != $excludeUid)) {
            return false;
        }

        $uname = mb_strtolower($uname);

        // get doctrine manager
        $em = \ServiceUtil::get('doctrine.entitymanager');

        // count of uname appearances in users table
        $qb = $em->createQueryBuilder()
                 ->select('count(u.uid)')
                 ->from('ZikulaUsersModule:UserEntity', 'u')
                 ->where('u.uname = :uname')
                 ->setParameter('uname', $uname);

        if ($excludeUid > 1) {
            $qb->andWhere('u.uid <> :excludeUid')
               ->setParameter('excludeUid', $excludeUid);
        }

        $query = $qb->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Counts how many times an e-mail address has been used by user accounts in the system.
     *
     * @param string $emailAddress The e-mail address in question (required).
     * @param int    $excludeUid   The uid to exclude from the check, used when checking modifications.
     *
     * @return integer|boolean the count, or false on error.
     */
    public static function getEmailUsageCount($emailAddress, $excludeUid = 0)
    {
        if (!is_numeric($excludeUid) || ((int)$excludeUid != $excludeUid)) {
            return false;
        }

        $emailAddress = mb_strtolower($emailAddress);

        // get doctrine manager
        $em = \ServiceUtil::get('doctrine.entitymanager');

        // count of email appearances in users table
        $qb = $em->createQueryBuilder()
                 ->select('count(u.uid)')
                 ->from('ZikulaUsersModule:UserEntity', 'u')
                 ->where('u.email = :email')
                 ->setParameter('email', $emailAddress);

        if ($excludeUid > 1) {
            $qb->andWhere('u.uid <> :excludeUid')
               ->setParameter('excludeUid', $excludeUid);
        }

        $query = $qb->getQuery();

        $ucount = (int)$query->getSingleScalarResult();

        // count of email appearances in users verification table
        $qb = $em->createQueryBuilder()
                 ->select('count(v.uid)')
                 ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                 ->where('v.newemail = :email')
                 ->andWhere('v.changetype = :chgtype')
                 ->setParameter('email', $emailAddress)
                 ->setParameter('chgtype', UsersConstant::VERIFYCHGTYPE_EMAIL);

        if ($excludeUid > 1) {
            $qb->andWhere('v.uid <> :excludeUid')
               ->setParameter('excludeUid', $excludeUid);
        }

        $query = $qb->getQuery();

        $vcount = (int)$query->getSingleScalarResult();

        return $ucount + $vcount;
    }

    /**
     * When getting a registration record, this function calculates several fields needed for registration state.
     *
     * @param array &$userObj The user object array created by UserUtil::getVars(). NOTE: this parameter is passed by
     *                          reference, and therefore will be updated by the actions of this function.
     *
     * @return array The updated $userObj.
     */
    public static function postProcessGetRegistration(&$userObj)
    {
        if ($userObj['activated'] == UsersConstant::ACTIVATED_PENDING_REG) {
            // Get isverified from the attributes.
            if (isset($userObj['__ATTRIBUTES__']['_Users_isVerified'])) {
                $userObj['isverified'] = $userObj['__ATTRIBUTES__']['_Users_isVerified'];
                // todo why was this unset statement commented out in 2.0?
                unset($userObj['__ATTRIBUTES__']['_Users_isVerified']);
            } else {
                $userObj['isverified'] = false;
            }

            // Get verificationsent from the users_verifychg table
            $em = \ServiceUtil::get('doctrine.entitymanager');

            $query = $em->createQueryBuilder()
                        ->select('v')
                        ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                        ->where('v.uid = :uid')
                        ->andWhere('v.changetype = :changetype')
                        ->setParameter('uid', $userObj['uid'])
                        ->setParameter('changetype', UsersConstant::VERIFYCHGTYPE_REGEMAIL)
                        ->getQuery();

            $verifyChgList = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            if ($verifyChgList && is_array($verifyChgList) && !empty($verifyChgList) && is_array($verifyChgList[0]) && !empty($verifyChgList[0])) {
                $userObj['verificationsent'] = $verifyChgList[0]['created_dt'];
            } else {
                $userObj['verificationsent'] = false;
            }

            // Calculate isapproved from approved_by
            $userObj['isapproved'] = isset($userObj['approved_by']) && !empty($userObj['approved_by']);
        }

        return $userObj;
    }

    /**
     * Get all user variables, maps new style attributes to old style user data.
     *
     * @param integer $id              The user id of the user (required).
     * @param boolean $force           True to force loading from database and ignore the cache.
     * @param string  $idfield         Field to use as id (possible values: uid, uname or email).
     * @param bool    $getRegistration Indicates whether a "regular" user record or a pending registration
     *                                      is to be returned. False (default) for a user record and true
     *                                      for a registration. If false and the user record is a pending
     *                                      registration, then the record is not returned and false is returned
     *                                      instead; likewise, if true and the user record is not a registration,
     *                                      then false is returned; (Defaults to false).
     *
     * @return array|bool An associative array with all variables for a user (or pending registration);
     *                      false on error.
     */
    public static function getVars($id, $force = false, $idfield = '', $getRegistration = false)
    {
        if (empty($id)) {
            return false;
        }

        // assign a value for the parameter idfield if it is necessary and prevent from possible typing mistakes
        if ($idfield == '' || ($idfield != 'uid' && $idfield != 'uname' && $idfield != 'email')) {
            $idfield = 'uid';
            if (!is_numeric($id)) {
                $idfield = 'uname';
                if (strpos($id, '@')) {
                    $idfield = 'email';
                }
            }
        }

        static $cache = array(), $unames = array(), $emails = array();

        // caching
        $user = null;
        if ($force == false) {
            if ($idfield == 'uname' && isset($unames[$id])) {
                if ($unames[$id] !== false) {
                    $user = $cache[$unames[$id]];
                } else {
                    return false;
                }
            }

            if ($idfield == 'email' && isset($emails[$id])) {
                if ($emails[$id] !== false) {
                    $user = $cache[$emails[$id]];
                } else {
                    return false;
                }
            }

            if (isset($cache[$id])) {
                $user = $cache[$id];
            }
        }

        if (!isset($user) || $force) {
            $em = \ServiceUtil::get('doctrine.entitymanager');
            $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy(array($idfield => $id));

            if ($user) {
                $user = $user->toArray();

                $attributes = array();
                foreach ($user['attributes'] as $attribute) {
                    $attributes[$attribute['name']] = $attribute['value'];
                }

                $user['__ATTRIBUTES__'] = $attributes;
                unset($user['attributes']);
            }

            // If $idfield is email, make sure that we are getting a unique record.
            if ($user && ($idfield == 'email')) {
                $emailCount = self::getEmailUsageCount($id);
                if (($emailCount > 1) || ($emailCount === false)) {
                    $user = false;
                }
            }

            // update cache
            if (!$user) {
                switch ($idfield) {
                    case 'uid':
                        $cache[$id] = false;
                        break;
                    case 'uname':
                        $unames[$id] = false;
                        break;
                    case 'email':
                        $emails[$id] = false;
                        break;
                }

                return false;
            } else {
                // This check should come at the very end, here, so that if $force is true the vars get
                // reloaded into cache no matter what $getRegistration is set to. If not, and this is
                // called from setVar(), and setVar() changed the 'activated' value, then we'd have trouble.
                if (($getRegistration && ($user['activated'] != UsersConstant::ACTIVATED_PENDING_REG))
                        || (!$getRegistration && ($user['activated'] == UsersConstant::ACTIVATED_PENDING_REG))) {
                    return false;
                }

                $user = self::postProcessGetRegistration($user);

                $cache[$user['uid']] = $user;
                $unames[$user['uname']] = $user['uid'];
                $emails[$user['email']] = $user['uid'];
            }
        } elseif (($getRegistration && ($user['activated'] != UsersConstant::ACTIVATED_PENDING_REG))
                || (!$getRegistration && ($user['activated'] == UsersConstant::ACTIVATED_PENDING_REG))) {
            return false;
        }

        return $user;
    }

    /**
     * Get a user variable.
     *
     * @param string  $name            The name of the variable.
     * @param integer $uid             The user to get the variable for.
     * @param mixed   $default         The default value to return if the specified variable doesn't exist.
     * @param bool    $getRegistration Indicates whether the variable should be retrieved from a "regular"
     *                                      user record or from a pending registration. False (default) for a
     *                                      user record and true for a registration. If false and the uid refers
     *                                      to a pending registration, then the variable is not returned and
     *                                      null is returned instead; likewise, if true and the user record is
     *                                      not a registration, then null is returned. (Defaults to false).
     *
     * @return mixed The value of the user variable if successful, null otherwise.
     */
    public static function getVar($name, $uid = -1, $default = false, $getRegistration = false)
    {
        if (empty($name)) {
            return null;
        }

        // bug fix #1311 [landseer]
        if (isset($uid) && !is_numeric($uid)) {
            return null;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid');
        }
        if (empty($uid)) {
            return null;
        }

        // get this user's variables
        $vars = self::getVars($uid, false, '', $getRegistration);

        if ($vars === false) {
            return null;
        }

        // Return the variable
        if (isset($vars[$name])) {
            return $vars[$name];
        }

        // or an attribute
        if (isset($vars['__ATTRIBUTES__'][$name])) {
            return $vars['__ATTRIBUTES__'][$name];
        }

        return $default;
    }

    /**
     * Maps the old DUD names to new attribute names.
     *
     * @param string $name The name of the field.
     *
     * @return string|bool The attribute name corresponding to the DUD name, or false if the parameter is not a DUD name.
     */
    private static function convertOldDynamicUserDataAlias($name)
    {
        $attributeName = false;

        if (isset($name) && !empty($name)) {
            // Only need to build the array once
            static $mappingArray;

            if (!isset($mappingArray)) {
                // this array maps old DUDs to new attributes
                $mappingArray = array(
                    '_UREALNAME'        => 'realname',
                    '_UFAKEMAIL'        => 'publicemail',
                    '_YOURHOMEPAGE'     => 'url',
                    '_TIMEZONEOFFSET'   => 'tzoffset',
                    '_YOURAVATAR'       => 'avatar',
                    '_YLOCATION'        => 'city',
                    '_YICQ'             => 'icq',
                    '_YAIM'             => 'aim',
                    '_YYIM'             => 'yim',
                    '_YMSNM'            => 'msnm',
                    '_YOCCUPATION'      => 'occupation',
                    '_SIGNATURE'        => 'signature',
                    '_EXTRAINFO'        => 'extrainfo',
                    '_YINTERESTS'       => 'interests',
                    'name'              => 'realname',
                    'femail'            => 'publicemail',
                    'timezone_offset'   => 'tzoffset',
                    'user_avatar'       => 'avatar',
                    'user_icq'          => 'icq',
                    'user_aim'          => 'aim',
                    'user_yim'          => 'yim',
                    'user_msnm'         => 'msnm',
                    'user_from'         => 'city',
                    'user_occ'          => 'occupation',
                    'user_intrest'      => 'interests',
                    'user_sig'          => 'signature',
                    'bio'               => 'extrainfo',
                );
            }

            $attributeName = isset($mappingArray[$name]) ? $mappingArray[$name] : false;
        }

        return $attributeName;
    }

    /**
     * Set a user variable.
     *
     * This can be
     * - a field in the users table
     * - or an attribute and in this case either a new style attribute or an old style user information.
     *
     * Examples:
     * self::setVar('pass', 'mysecretpassword'); // store a password (should be hashed of course)
     * self::setVar('avatar', 'mypicture.gif');  // stores an users avatar, new style
     * (internally both the new and the old style write the same attribute)
     *
     * If the user variable does not exist it will be created automatically. This means with
     * self::setVar('somename', 'somevalue');
     * you can easily create brand new users variables onthefly.
     *
     * This function does not allow you to set uid or uname.
     *
     * @param string  $name  The name of the variable.
     * @param mixed   $value The value of the variable.
     * @param integer $uid   The user to set the variable for.
     *
     * @return bool true if the set was successful, false otherwise
     */
    public static function setVar($name, $value, $uid = -1)
    {
        if (empty($name)) {
            return false;
        }

        if (!isset($value)) {
            return false;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid');
        }

        if (empty($uid)) {
            return false;
        }

        $isRegistration = self::isRegistration($uid);
        $origUserObj = self::getVars($uid, false, 'uid', $isRegistration);
        if (!$origUserObj) {
            // No such user record!
            return false;
        }

        $varIsSet = false;

        // Cannot setVar the user's uid or uname
        if (($name != 'uid') && ($name != 'uname')) {
            // get user given a uid
            $em = \ServiceUtil::get('doctrine.entitymanager');
            $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy(array('uid' => $uid));

            // check if var to set belongs to table or it's an attribute
            if (self::fieldAlias($name)) {
                // this value comes from the users table
                $oldValue = isset($origUserObj[$name]) ? $origUserObj[$name] : null;

                $user[$name] = $value;
                $em->flush();

                $varIsSet = true;
            } else {
                // Not a table field alias, not 'uid', and not 'uname'. Treat it as an attribute.
                $dudAttributeName = self::convertOldDynamicUserDataAlias($name);
                if ($dudAttributeName) {
                    LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($dudAttributeName, $name)), E_USER_DEPRECATED);
                    // $name is a former DUD /old style user information now stored as an attribute
                    $attributeName = $dudAttributeName;
                } else {
                    // $name not in the users table and also not found in the mapping array and also not one of the
                    // forbidden names, let's make an attribute out of it
                    $attributeName = $name;
                }

                $oldValue = isset($origUserObj['__ATTRIBUTES__'][$attributeName]) ? $origUserObj['__ATTRIBUTES__'][$attributeName] : null;

                $user->setAttribute($attributeName, $value);
                $em->flush();

                $varIsSet = true;
            }

            // force loading of attributes from db
            $updatedUserObj = self::getVars($uid, true, '', $isRegistration);
            if (!$updatedUserObj) {
                // Should never get here!
                return false;
            }

            // Do not fire update event/hook unless the update happened, it was not a registration record, it was not
            // the password being updated, and the system is not currently being installed.
            if ($varIsSet && ($name != 'pass') && !System::isInstalling()) {
                // Fire the event
                $eventName = $isRegistration ? 'user.registration.update' : 'user.account.update';
                $eventArgs = array(
                    'action'    => 'setVar',
                    'field'     => isset($attributeName) ? null : $name,
                    'attribute' => isset($attributeName) ? $attributeName : null,
                );
                $eventData = array(
                    'old_value' => $oldValue,
                    'new_value' => $value,
                );

                $updateEvent = new GenericEvent($updatedUserObj, $eventArgs, $eventData);
                EventUtil::dispatch($eventName, $updateEvent);
            }
        }

        return $varIsSet;
    }

    /**
     * Get an array of hash algorithms valid for hashing user passwords.
     *
     * Either as an array of
     * algorithm names index by internal integer code, or as an array of internal integer algorithm
     * codes indexed by algorithm name.
     *
     * @param bool $reverse If false, then return an array of codes indexed by name (e.g. given $name, then $code = $methods[$name]);
     *                          if true, return an array of names indexed by code (e.g. given $code, then $name = $methods[$code]);
     *                          optional, default = false.
     *
     * @return array Depending on the value of $reverse, an array of codes indexed by name or an
     *                  array of names indexed by code.
     */
    public static function getPasswordHashMethods($reverse = false)
    {
        // NOTICE: Be extremely cautious about removing entries from this array! If a hash method is no longer
        // to be used, then it probably should be removed from the available options at display time. If an entry is
        // removed from this array but a user's password has been hashed with that method, then that user will no
        // longer be able to log in!! Only remove an entry if you are absolutely positive no user record has a
        // password hashed with that method!
        // NOTICE: DO NOT change the numbers assigned to each hash method. The number is the identifier for the
        // method stored in the database. If a number is changed to a different method, then any user whose password
        // was hashed with the method previously identified by that number will no longer be able to log in!

        $reverse = is_bool($reverse) ? $reverse : false;

        if ($reverse) {
            // Ensure this is in sync with the array below!
            return array(
                    1 => 'md5',
                    5 => 'sha1',
                    8 => 'sha256'
            );
        } else {
            // Ensure this is in sync with the array above!
            return array(
                    'md5' => 1,
                    'sha1' => 5,
                    'sha256' => 8
            );
        }
    }

    /**
     * For a given password hash algorithm name, return its internal integer code.
     *
     * @param string $hashAlgorithmName The name of a hash algorithm suitable for hashing user passwords.
     *
     * @return integer|bool The internal integer code corresponding to the given algorithm name; false if the name is not valid.
     */
    public static function getPasswordHashMethodCode($hashAlgorithmName)
    {
        static $hashMethodCodesByName;

        if (!isset($hashMethodCodesByName)) {
            $hashMethodCodesByName = self::getPasswordHashMethods(false);
        }

        if (!isset($hashAlgorithmName) || !is_string($hashAlgorithmName) || empty($hashAlgorithmName) || !isset($hashMethodCodesByName[$hashAlgorithmName])
                || empty($hashMethodCodesByName[$hashAlgorithmName]) || !is_numeric($hashMethodCodesByName[$hashAlgorithmName])) {
            throw new \InvalidArgumentException(__f('Invalid argument %s', 'hashAlgorithmName'));
        }

        return $hashMethodCodesByName[$hashAlgorithmName];
    }

    /**
     * For a given internal password hash algorithm code, return its name suitable for use with the hash() function.
     *
     * @param int $hashAlgorithmCode The internal code representing a hashing algorithm suitable for hashing user passwords.
     *
     * @return string|bool The hashing algorithm name corresponding to that code, suitable for use with hash(); false if the code is invalid.
     */
    public static function getPasswordHashMethodName($hashAlgorithmCode)
    {
        static $hashMethodNamesByCode;

        if (!isset($hashMethodNamesByCode)) {
            $hashMethodNamesByCode = self::getPasswordHashMethods(true);
        }

        if (!isset($hashAlgorithmCode) || !is_numeric($hashAlgorithmCode) || !isset($hashMethodNamesByCode[$hashAlgorithmCode])
                || !is_string($hashMethodNamesByCode[$hashAlgorithmCode]) || empty($hashMethodNamesByCode[$hashAlgorithmCode])) {
            throw new \InvalidArgumentException(__f('Invalid argument %s', 'hashAlgorithmCode'));
        }

        return $hashMethodNamesByCode[$hashAlgorithmCode];
    }

    /**
     * Determines if a given unhashed password meets the minimum criteria for use as a user password.
     *
     * The given password must be set, a string, not the empty string, and must have a length greater than
     * the minimum length defined by the Users module variable 'minpass' (or 5 if that variable is not set or
     * is misconfigured).
     *
     * @param string $unhashedPassword The proposed password.
     *
     * @return bool True if the proposed password meets the minimum criteria; otherwise false;
     */
    public static function validatePassword($unhashedPassword)
    {
        $minLength = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH);

        return isset($unhashedPassword)
                && is_string($unhashedPassword)
                && (strlen($unhashedPassword) >= $minLength);
    }

    /**
     * Given a string return it's hash and the internal integer hashing algorithm code used to hash that string.
     *
     * Note that this can be used for more than just user login passwords. If a user-readale password-like code is needed,
     * then this method may be suitable.
     *
     * @param string $unhashedPassword An unhashed password, as might be entered by a user or generated by the system, that meets
     *                                  all of the constraints of a valid password for a user account.
     * @param int $hashMethodCode An internal code identifying one of the valid user password hashing methods; optional, leave this
     *                                  unset (null) when creating a new password for a user to get the currently configured system
     *                                  hashing method, otherwise to hash a password for comparison, specify the method used to hash
     *                                  the original password.
     *
     * @return array|bool An array containing two elements: 'hash' containing the hashed password, and 'hashMethodCode' containing the
     *                      internal integer hashing algorithm code used to hash the password; false if the password does not meet the
     *                      constraints of a valid password, or if the hashing method (stored in the Users module 'hash_method' var) is
     *                      not valid.
     */
    public static function getHashedPassword($unhashedPassword, $hashMethodCode = null)
    {
        if (isset($hashMethodCode)) {
            if (!is_numeric($hashMethodCode) || ((int)$hashMethodCode != $hashMethodCode)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
            $hashAlgorithmName = self::getPasswordHashMethodName($hashMethodCode);
            if (!$hashAlgorithmName) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
        } else {
            $hashAlgorithmName = ModUtil::getVar('ZikulaUsersModule', 'hash_method', '');
            $hashMethodCode = self::getPasswordHashMethodCode($hashAlgorithmName);
            if (!$hashMethodCode) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
        }

        return SecurityUtil::getSaltedHash($unhashedPassword, $hashAlgorithmName, self::getPasswordHashMethods(false), 5, UsersConstant::SALT_DELIM);

        // FIXME this return is not reached
        return array(
                'hashMethodCode' => $hashMethodCode,
                'hash' => hash($hashAlgorithmName, $unhashedPassword),
        );
    }

    /**
     * Create a system-generated password or password-like code, meeting the configured constraints for a password.
     *
     * @return string The generated (unhashed) password-like string.
     */
    public static function generatePassword()
    {
        $minLength = ModUtil::getVar('ZikulaUsersModule', 'minpass', 5);
        if (!is_numeric($minLength) || ((int)$minLength != $minLength) || ($minLength < 5)) {
            $minLength = 5;
        }
        $minLength = min($minLength, 25);
        $maxLength = min($minLength + 3, 25);

        return RandomUtil::getStringForPassword($minLength, $maxLength);
    }

    /**
     * Change the specified user's password to the one provided, defaulting to the current user if a uid is not specified.
     *
     * @param string $unhashedPassword The new password for the current user.
     * @param int    $uid              The user ID of the user for whom the password should be set; optional; defaults to current user.
     *
     * @return bool True if the password was successfully saved; otherwise false if the password is empty,
     *                  invalid (too short), or if the password was not successfully saved.
     */
    public static function setPassword($unhashedPassword, $uid = -1)
    {
        $passwordChanged = false;

        // If uid is not -1 (specifies someone other than the current user) then make sure the current user
        // is allowed to do that.

        if (self::validatePassword($unhashedPassword)) {
            $hashedPassword = self::getHashedPassword($unhashedPassword);

            if ($hashedPassword) {
                // TODO - Important! This needs to be an atomic change to the database. If pass is changed without hash_method, then the user will not be able to log in!
                $passwordChanged = self::setVar('pass', $hashedPassword, $uid);
            }
            // TODO - Should we force the change of passreminder here too? If the password is changing, certainly the existing reminder is no longer valid.
        }

        return $passwordChanged;
    }

    /**
     * Compare a password-like code to a hashed value, to determine if they match.
     *
     * Note that this is not limited only to use for user login passwords, but can be used where ever a human-readable
     * password-like code is needed.
     *
     * @param string $unhashedPassword The password-like code entered by the user.
     * @param string $hashedPassword   The hashed password-like code that the entered password-like code is to be compared to.
     *
     * @return bool True if the $unhashedPassword matches the $hashedPassword with the given hashing method; false if they do not
     *                  match, or if there was an error (such as an empty password or invalid code).
     */
    public static function passwordsMatch($unhashedPassword, $hashedPassword)
    {
        $passwordsMatch = false;

        if (!isset($unhashedPassword) || !is_string($unhashedPassword) || empty($unhashedPassword)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($hashedPassword) || !is_string($hashedPassword) || empty($hashedPassword) || (strpos($hashedPassword, UsersConstant::SALT_DELIM) === false)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $passwordsMatch = SecurityUtil::checkSaltedHash($unhashedPassword, $hashedPassword, self::getPasswordHashMethods(true), UsersConstant::SALT_DELIM);

        return $passwordsMatch;
    }

    /**
     * Delete the contents of a user variable.
     *
     * This can either be
     * - a variable stored in the users table or
     * - an attribute to the users table, either a new style sttribute or the old style user information
     *
     * Examples:
     * UserUtil::delVar('ublock');  // clears the recent users table entry for 'ublock'
     * UserUtil::delVar('_YOURAVATAR', 123), // removes a users avatar, old style (uid = 123)
     * UserUtil::delVar('avatar', 123);  // removes a users avatar, new style (uid=123)
     * (internally both the new style and the old style clear the same attribute)
     *
     * It does not allow the deletion of uid, email, uname, pass (password), as these are mandatory
     * fields in the users table.
     *
     * @param string  $name The name of the variable.
     * @param integer $uid  The user to delete the variable for.
     *
     * @return boolen true on success, false on failure
     */
    public static function delVar($name, $uid = -1)
    {
        // Prevent deletion of core fields (duh)
        if (empty($name) || ($name == 'uid') || ($name == 'email') || ($name == 'pass') || ($name == 'uname')
                || ($name == 'activated')) {
            return false;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid');
        }

        if (empty($uid)) {
            return false;
        }

        // Special delete value for approved_by
        if ($name == 'approved_by') {
            return (bool)self::setVar($name, -1, $uid);
        }

        $isRegistration = self::isRegistration($uid);
        $origUserObj = self::getVars($uid, false, 'uid', $isRegistration);
        if (!$origUserObj) {
            // No such user record!
            return false;
        }

        $varIsDeleted = false;

        // Cannot delVar the user's uid or uname
        if (($name != 'uid') && ($name != 'uname')) {
            // get user given a uid
            $em = \ServiceUtil::get('doctrine.entitymanager');
            $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy(array('uid' => $uid));

            if (self::fieldAlias($name)) {
                // this value comes from the users table
                $oldValue = isset($origUserObj[$name]) ? $origUserObj[$name] : null;

                $user[$name] = '';
                $em->flush();

                $varIsDeleted = true;
            } else {
                // Not a table field alias, not 'uid', and not 'uname'. Treat it as an attribute.
                $dudAttributeName = self::convertOldDynamicUserDataAlias($name);
                if ($dudAttributeName) {
                    LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($dudAttributeName, $name)), E_USER_DEPRECATED);
                    // $name is a former DUD /old style user information now stored as an attribute
                    $attributeName = $dudAttributeName;
                } else {
                    // $name not in the users table and also not found in the mapping array and also not one of the
                    // forbidden names, let's make an attribute out of it
                    $attributeName = $name;
                }

                $oldValue = isset($origUserObj['__ATTRIBUTES__'][$attributeName]) ? $origUserObj['__ATTRIBUTES__'][$attributeName] : null;

                $user->delAttribute($attributeName);

                $varIsDeleted = true;
            }

            // force loading of attributes from db
            $updatedUserObj = self::getVars($uid, true, '', $isRegistration);
            if (!$updatedUserObj) {
                // Should never get here!
                return false;
            }

            // Do not fire update event/hook unless the update happened, it was not a registration record, it was not
            // the password being updated, and the system is not currently being installed.
            if ($varIsDeleted && ($name != 'pass') && !System::isInstalling()) {
                // Fire the event
                $eventArgs = array(
                    'action'    => 'delVar',
                    'field'     => isset($attributeName) ? null : $name,
                    'attribute' => isset($attributeName) ? $attributeName : null,
                );
                $eventData = array(
                    'old_value' => $oldValue,
                );
                if ($isRegistration) {
                    $updateEvent = new GenericEvent($updatedUserObj, $eventArgs, $eventData);
                    EventUtil::dispatch('user.registration.update', $updateEvent);
                } else {
                    $updateEvent = new GenericEvent($updatedUserObj, $eventArgs, $eventData);
                    EventUtil::dispatch('user.account.update', $updateEvent);
                }
            }
        }

        return $varIsDeleted;
    }

    /**
     * Get the user's theme.
     *
     * This function will return the current theme for the user.
     * Order of theme priority:
     *  - page-specific
     *  - category
     *  - user
     *  - system
     *
     * @param boolean $force True to ignore the cache.
     *
     * @return string           the name of the user's theme
     * @throws RuntimeException If this function was unable to calculate theme name.
     */
    public static function getTheme($force = false)
    {
        // if this method is called from the command line scope, always return a default core theme (ZikulaAndreas08Theme)
        // this prevents calls for the Request object or other unwanted behaviors.
        if (php_sapi_name() == 'cli') {
            return 'ZikulaAndreas08Theme';
        }

        static $pagetheme;

        if (isset($pagetheme) && !$force) {
            return $pagetheme;
        }

        /** @var $request Request */
        $request = \ServiceUtil::get('request');

        $theme = FormUtil::getPassedValue('theme', null, 'GETPOST');
        if (!empty($theme) && SecurityUtil::checkPermission('ZikulaThemeModule::ThemeChange', '::', ACCESS_COMMENT)) {
            // theme passed as parameter takes priority, can be RSS, Atom, Printer or other
            $pagetheme = $theme;
        } else {
            // check for specified alternative site view domain and theme
            $themedomain = ModUtil::getVar('ZikulaThemeModule', 'alt_theme_domain', '');
            if ($themedomain && $_SERVER['SERVER_NAME'] == $themedomain && ModUtil::getVar('ZikulaThemeModule', 'alt_theme_name', '')) {
                $pagetheme = ModUtil::getVar('ZikulaThemeModule', 'alt_theme_name');
            }
        }

        // Retrieve required parameters
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        $legacyType = FormUtil::getPassedValue('lct', null, 'GETPOST');
        if ($type != $legacyType) {
            // BC support (see #2051 for example)
            $type = $legacyType;
        }
        if (null === $type) {
            // routing preventing type from being set, get from request attributes
            $type = $request->get('_zkType');
        }

        // Page-specific theme
        $qstring = System::serverGetVar('QUERY_STRING');
        if (!empty($pagetheme)) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($pagetheme));
            if ($themeinfo['state'] == ThemeUtil::STATE_ACTIVE
                && ($themeinfo['user']
                    || $themeinfo['system']
                    || ($themeinfo['admin'] && ($type == 'admin')))
                && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $pagetheme = $themeinfo['name'];

                $themeName = self::_getThemeFilterEvent($themeinfo['name'], 'page-specific');
                $request->attributes->set('_theme', $themeName);

                return $themeName;
            }
        }

        // check for an admin theme
        // first setting the theme from the method annotation (Core-2.0 FC)
        $router = ServiceUtil::get('router');
        try {
            $parameters = $router->matchRequest($request);
            if (strpos($parameters['_controller'], '::')) {
                list($controllerName, $controllerMethod) = explode('::', $parameters['_controller'], 2);
                $newAdminTheme = ServiceUtil::get('zikula_core.common.theme_engine')->changeThemeByAnnotation($controllerName, $controllerMethod);
                if (false !== $newAdminTheme) {
                    $pagetheme = $newAdminTheme;

                    return $newAdminTheme;
                }
            }
        } catch (\Exception $e) {
            // was a homepage or something that doesn't matter. the request must be an admin route request to be changed.
        }
        $adminSections = array('admin', 'adminplugin');
        if (in_array($type, $adminSections) && SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            $admintheme = ModUtil::getVar('ZikulaAdminModule', 'admintheme');
            if (!empty($admintheme)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($admintheme));
                if ($themeinfo
                    && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE
                    && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                    $pagetheme = $themeinfo['name'];

                    $themeName = self::_getThemeFilterEvent($themeinfo['name'], 'admin-theme');
                    $request->attributes->set('_theme', $themeName);

                    return $themeName;
                }
            }
        }

        // set a new theme for the user
        // @todo @deprecated this code block (allowing a user to change themes) will not be continued into Core-2.0
        $newtheme = FormUtil::getPassedValue('newtheme', null, 'GETPOST');
        if (!empty($newtheme) && System::getVar('theme_change')) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($newtheme));
            if ($themeinfo
                && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE
                && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                if (self::isLoggedIn()) {
                    self::setVar('theme', $newtheme);
                } else {
                    SessionUtil::setVar('theme', $newtheme);
                }

                $pagetheme = $themeinfo['name'];
                $themeName = self::_getThemeFilterEvent($themeinfo['name'], 'new-theme');
                $request->attributes->set('_theme', $themeName);

                return $themeName;
            }
        }

        // User theme
        if (System::getVar('theme_change') || SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
            if ((self::isLoggedIn())) {
                $usertheme = self::getVar('theme');
            } else {
                $usertheme = SessionUtil::getVar('theme');
            }
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($usertheme));
            if ($themeinfo
                && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE
                && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $pagetheme = $themeinfo['name'];

                $themeName = self::_getThemeFilterEvent($themeinfo['name'], 'user-theme');
                $request->attributes->set('_theme', $themeName);

                return $themeName;
            }
        }

        // default site theme
        $defaulttheme = System::getVar('Default_Theme');
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($defaulttheme));
        if ($themeinfo
            && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE
            && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            $pagetheme = $themeinfo['name'];

            $themeName = self::_getThemeFilterEvent($themeinfo['name'], 'default-theme');
            $request->attributes->set('_theme', $themeName);

            return $themeName;
        }

        if (!System::isInstalling()) {
            throw new RuntimeException(__('UserUtil::getTheme() is unable to calculate theme name.'));
        }
    }

    /**
     * Filter results for a given getTheme() type.
     *
     * @param string $themeName Theme name.
     * @param string $type      Event type.
     *
     * @return string Theme name
     */
    private static function _getThemeFilterEvent($themeName, $type)
    {
        $event = new GenericEvent(null, array('type' => $type), $themeName);

        return EventUtil::dispatch('user.gettheme', $event)->getData();
    }

    /**
     * Get a list of user information.
     *
     * @param string  $sortbyfield   Sort by field.
     * @param string  $sortorder     Sort by order.
     * @param integer $limit         Select limit.
     * @param integer $startnum      Select offset.
     * @param string  $activated     Activated value.
     * @param string  $regexpfield   Field for regexfilter.
     * @param string  $regexpression Regex expression.
     * @param string  $where         Where clause.
     *
     * @return array Array of users.
     */
    public static function getAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = null, $offset = null, $activated = '', $field = '', $expression = '', $where = '')
    {
        $user = new \Zikula\UsersModule\Entity\UserEntity();

        if (empty($where)) {
            $whereFragments = array();

            if (!empty($field) && isset($user[$field]) && !empty($expression)) {
                $whereFragments[] = 'u.' . $field . ' LIKE \'' . DataUtil::formatForStore($expression) . '\'';
            }

            if (!empty($activated) && is_numeric($activated) && isset($user['activated'])) {
                $whereFragments[] = 'u.activated <> "' . DataUtil::formatForStore($activated) . '"';
            }

            if (!empty($whereFragments)) {
                $where = 'WHERE ' . implode(' AND ', $whereFragments);
            }
        }

        if (!empty($sortbyfield)) {
            $sortFragments = array();

            $sortFragments[] = 'u.'. $sortbyfield . ' ' . DataUtil::formatForStore($sortorder);

            if ($sortbyfield != 'uname') {
                $sortFragments[] = 'u.uname ASC';
            }

            if (!empty($sortFragments)) {
                $orderby = 'ORDER BY ' . implode(', ', $sortFragments);
            }
        }

        $em = \ServiceUtil::get('doctrine.entitymanager');
        $dql = "SELECT u FROM Zikula\UsersModule\Entity\UserEntity u $where $orderby";
        $query = $em->createQuery($dql);

        if (isset($limit) && is_numeric($limit) && $limit > 0) {
            $query->setMaxResults($limit);

            if (isset($offset) && is_numeric($offset) && $offset > 0) {
                $query->setFirstResult($offset);
            }
        }

        $users = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $usersObj = array();
        foreach ($users as $user) {
            $usersObj[$user['uid']] = $user;
        }

        return $usersObj;
    }

    /**
     * Get the uid of a user from the username.
     *
     * @param string $uname           The username.
     * @param bool   $forRegistration Get the id for a pending registration (default = false).
     *
     * @return integer|boolean The uid if found, false if not.
     */
    public static function getIdFromName($uname, $forRegistration = false)
    {
        $result = self::getVars($uname, false, 'uname', $forRegistration);

        return $result && isset($result['uid']) ? $result['uid'] : false;
    }

    /**
     * Get the uid of a user from the email (case for unique emails).
     *
     * @param string $email           The user email.
     * @param bool   $forRegistration Get the id for a pending registration (default = false).
     *
     * @return integer|boolean The uid if found, false if not.
     */
    public static function getIdFromEmail($email, $forRegistration = false)
    {
        $result = self::getVars($email, false, 'email', $forRegistration);

        return $result && isset($result['uid']) ? $result['uid'] : false;
    }

    /**
     * Checks the alias and returns if we save the data in the Profile module's user_data table or the users table.
     *
     * This should be removed if we ever go fully dynamic
     *
     * @param string $label The alias of the field to check.
     *
     * @return true if found, false if not, void upon error
     */
    public static function fieldAlias($label)
    {
        $isFieldAlias = false;

        // no change in uid or uname allowed, empty label is not an alias
        if (($label != 'uid') && ($label != 'uname') && !empty($label)) {
            $userObj = new \Zikula\UsersModule\Entity\UserEntity();
            $isFieldAlias = property_exists($userObj, $label);
        }

        return $isFieldAlias;
    }

    /**
     * Determine if the current session is that of an anonymous user.
     *
     * @return boolean
     */
    public static function isGuestUser()
    {
        return !SessionUtil::getVar('uid', 0);
    }

    /**
     * Determine if the record represented by the $uid is a registration or not.
     *
     * @param numeric $uid The uid of the record in question.
     *
     * @throws InvalidArgumentException If the uid is not valid.
     *
     * @return boolean True if it is a registration record, otherwise false;
     */
    public static function isRegistration($uid)
    {
        if (!isset($uid) || !is_numeric($uid)
                || (!is_int($uid) && ((string)((int)$uid) != $uid))
                ) {
            throw new InvalidArgumentException(__('An invalid uid was provided.'));
        }

        $isRegistration = false;

        $user = self::getVars($uid);
        if (!$user) {
            $user = self::getVars($uid, false, 'uid', true);
            if ($user) {
                $isRegistration = true;
            }
        }

        return $isRegistration;
    }
}
