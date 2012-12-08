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
     * Return a user object.
     *
     * @param integer $uid     The userID of the user to retrieve.
     * @param boolean $getVars Obsolete, we also return the attributes.
     *
     * @deprecated since 1.3.0
     * @see    self::getVars()
     *
     * @return array The resulting user object.
     */
    public static function getPNUser($uid, $getVars = false)
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getVars')), E_USER_DEPRECATED);

        return self::getVars($uid);
    }

    /**
     * Return a field from a user object.
     *
     * @param integer $id    The userID of the user to retrieve.
     * @param string  $field The field from the user object to get.
     *
     * @deprecated since 1.3.0
     * @see    self::getVars()
     *
     * @return mixed The requested field.
     */
    public static function getPNUserField($id, $field)
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getVar')), E_USER_DEPRECATED);

        return self::getVar($field, $id);
    }

    /**
     * Return a hash structure mapping uid to username.
     *
     * @param string  $where        The where clause to use (optional).
     * @param string  $orderBy      The order by clause to use (optional).
     * @param integer $limitOffset  The select-limit offset (optional) (default=-1).
     * @param integer $limitNumRows The number of rows to fetch (optional) (default=-1).
     * @param string  $assocKey     The associative key to apply (optional) (default='gid').
     *
     * @deprecated since 1.3.0
     *
     * @return array An array mapping uid to username.
     */
    public static function getPNUsers($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'uid')
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getUsers')), E_USER_DEPRECATED);

        return self::getUsers($where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Return a hash structure mapping uid to username.
     *
     * @param string  $where        The where clause to use (optional).
     * @param string  $orderBy      The order by clause to use (optional).
     * @param integer $limitOffset  The select-limit offset (optional) (default=-1).
     * @param integer $limitNumRows The number of rows to fetch (optional) (default=-1).
     * @param string  $assocKey     The associative key to apply (optional) (default='gid').
     *
     * @deprecated since 1.3.0
     *
     * @return array An array mapping uid to username.
     */
    public static function getUsers($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'uid')
    {
        return DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Return a group object.
     *
     * @param integer $gid The groupID to retrieve.
     *
     * @deprecated since 1.3.0
     * @see    UserUtil::getGroup()
     *
     * @return array The resulting group object.
     */
    public static function getPNGroup($gid)
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getGroup')), E_USER_DEPRECATED);

        return self::getGroup($gid);
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
        return DBUtil::selectObjectByID('groups', $gid, 'gid');
    }

    /**
     * Return a hash structure mapping gid to groupname.
     *
     * @param string  $where        The where clause to use (optional) (default='').
     * @param string  $orderBy      The order by clause to use (optional) (default='').
     * @param integer $limitOffset  The select-limit offset (optional) (default=-1).
     * @param integer $limitNumRows The number of rows to fetch (optional) (default=-1).
     * @param string  $assocKey     The associative key to apply (optional) (default='gid').
     *
     * @deprecated since 1.3.0
     *
     * @return array An array mapping gid to groupname
     */
    public static function getPNGroups($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'gid')
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getGroups')), E_USER_DEPRECATED);

        return self::getGroups();
    }

    /**
     * Return a hash structure mapping gid to groupname.
     *
     * @param string  $where        The where clause to use (optional) (default='').
     * @param string  $orderBy      The order by clause to use (optional) (default='').
     * @param integer $limitOffset  The select-limit offset (optional) (default=-1).
     * @param integer $limitNumRows The number of rows to fetch (optional) (default=-1).
     * @param string  $assocKey     The associative key to apply (optional) (default='gid').
     *
     * @return array An array mapping gid to groupname.
     */
    public static function getGroups($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'gid')
    {
        return DBUtil::selectObjectArray('groups', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
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
     * @deprecated since 1.3.0
     * @see    UserUtil::getGroupIdList()
     *
     * @return string A string list of group ids
     */
    public static function getPNGroupIdList($where = '', $orderBy = '', $separator = ',')
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getGroupIdList')), E_USER_DEPRECATED);

        return self::getGroupIdList($where, $orderBy, $separator);
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
    public static function getGroupIdList($where = '', $orderBy = '', $separator = ',')
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
     * Return an array group-ids for the specified user.
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

        $where = '';
        if ($uid != -1) {
            $where = "WHERE uid = '" . DataUtil::formatForStore($uid) . "'";
        }

        return DBUtil::selectFieldArray('group_membership', 'gid', $where);
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

        $where = "WHERE gid = '" . DataUtil::formatForStore($gid) . "'";

        return DBUtil::selectFieldArray('group_membership', 'uid', $where);
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
        $str = UserUtil::getGroupListForUser($uid, '_');

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

        ModUtil::dbInfoLoad($profileModule);

        return DBUtil::selectObjectArray('user_property');
    }

    /**
     * Return a string list of user-ids for the specified group.
     *
     * @param integer $uid            The user ID for which we want the users.
     * @param string  $assocKey       The associate Key to use.
     * @param boolean $standardFields Whether or not to also marshall the standard user properties into the DUD array.
     *
     * @todo No this is not what this functions does, but what does it do? It is not used within the core
     * @deprecated 1.3.0
     *
     * @return array An array of user IDs.
     */
    public static function getUserDynamicDataFields($uid, $assocKey = 'uda_propid', $standardFields = false)
    {
        if (!$uid) {
            return array();
        }

        return self::getVars($uid, '__ATTRIBUTES__');
    }

    /**
     * Return a PN array structure for the PN user group selector.
     *
     * @param mixed  $defaultValue The default value of the selector (default=0) (optional).
     * @param string $defaultText  The text of the default value (optional).
     * @param array  $ignore       An array of keys to ignore (optional).
     * @param mixed  $includeAll   Whether to include an "All" choice (optional).
     * @param string $allText      The text to display for the "All" choice (optional).
     *
     * @deprecated since 1.3.0
     * @see    UserUtil::getSelectorData_Group()
     *
     * @return array The array structure for the user group selector
     */
    public static function getSelectorData_PNGroup($defaultValue = 0, $defaultText = '', $ignore = array(), $includeAll = 0, $allText = '')
    {
        LogUtil::log(__f('Warning! UserUtil::%1$s is deprecated. Please use %2$s instead.', array(__METHOD__, 'UserUtil::getSelectorData_Group')), E_USER_DEPRECATED);

        return self::getSelectorData_Group($defaultValue, $defaultText, $ignore, $includeAll, $allText);
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

        $groupdata = self::getGroups('', 'ORDER BY name');

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
     * @param miexed $defaultValue The default value of the selector (optional) (default=0).
     * @param string $defaultText  The text of the default value (optional) (default='').
     * @param array  $ignore       An array of keys to ignore (optional) (default=array()).
     * @param miexed $includeAll   Whether to include an "All" choice (optional) (default=0).
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
     * @param numeric $uid The user id of the user for which account recovery information should be retrieved; optional, defaults to the
     *                          currently logged in user (an exception occurs if the current user is not logged in).
     *
     * @return array An array of account recovery information.
     *
     * @throws Zikula_Exception_Fatal If the $uid parameter is not valid.
     */
    public static function getUserAccountRecoveryInfo($uid = -1)
    {
        if (!isset($uid) || !is_numeric($uid) || ((string)((int)$uid) != $uid) || (($uid < -1) || ($uid == 0) || ($uid == 1))) {
            throw new Zikula_Exception_Fatal('Attempt to get authentication information for an invalid user id.');
        }

        if ($uid == -1) {
            if (self::isLoggedIn()) {
                $uid = self::getVar('uid');
            } else {
                throw new Zikula_Exception_Fatal('Attempt to get authentication information for an invalid user id.');
            }
        }

        $userAuthenticationInfo = array();

        $authenticationModules = ModUtil::getModulesCapableOf(Users_Constant::CAPABILITY_AUTHENTICATION);
        if ($authenticationModules) {
            $accountRecoveryArgs = array (
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
     * @deprecated use UserUtil::loginUsing() instead
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
            'modname'   => 'Users',
        );

        if (ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_LOGIN_METHOD, Users_Constant::DEFAULT_LOGIN_METHOD) == Users_Constant::LOGIN_METHOD_EMAIL) {
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
     * @throws Zikula_Exception_Fatal
     *
     * @return true
     */
    private static function preAuthenticationValidation(array $authenticationMethod, $reentrantURL = null)
    {
        if (empty($authenticationMethod) || (count($authenticationMethod) != 2)) {
            throw new Zikula_Exception_Fatal(__f('An invalid %1$s parameter was received.', array('authenticationMethod')));
        }

        if (!isset($authenticationMethod['modname']) || !is_string($authenticationMethod['modname']) || empty($authenticationMethod['modname'])) {
            throw new Zikula_Exception_Fatal(__f('An invalid %1$s parameter was received.', array('modname')));
        } elseif (!ModUtil::getInfoFromName($authenticationMethod['modname'])) {
            throw new Zikula_Exception_Fatal(__f('The authentication module \'%1$s\' could not be found.', array($authenticationMethod['modname'])));
        } elseif (!ModUtil::available($authenticationMethod['modname'])) {
            throw new Zikula_Exception_Fatal(__f('The authentication module \'%1$s\' is not available.', array($authenticationMethod['modname'])));
        } elseif (!ModUtil::loadApi($authenticationMethod['modname'], 'Authentication')) {
            throw new Zikula_Exception_Fatal(__f('The authentication module \'%1$s\' could not be loaded.', array($authenticationMethod['modname'])));
        }

        if (!isset($authenticationMethod['method']) || !is_string($authenticationMethod['method']) || empty($authenticationMethod['method'])) {
            throw new Zikula_Exception_Fatal(__f('An invalid %1$s parameter was received.', array('method')));
        } elseif (!ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'supportsAuthenticationMethod', array('method' => $authenticationMethod['method']), 'Zikula_Api_AbstractAuthentication')) {
            throw new Zikula_Exception_Fatal(__f('The authentication method \'%1$s\' is not supported by the authentication module \'%2$s\'.', array($authenticationMethod['method'], $authenticationMethod['modname'])));
        }

        if (ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'isReentrant', null, 'Zikula_Api_AbstractAuthentication') && (!isset($reentrantURL) || empty($reentrantURL))) {
            throw new Zikula_Exception_Fatal(__f('The authentication module \'%1$s\' is reentrant. A %2$s is required.', array($authenticationMethod['modname'], 'reentrantURL')));
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
                ModUtil::dbInfoLoad('Users', 'Users');
                ModUtil::loadApi('Users', 'user', true);

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

                throw new Zikula_Exception_Fatal(__f('A %1$s (%2$s) was returned by the authenticating module, but a user account record (or registration request record) could not be found.', array('uid', $uid)));
            }

            if (!isset($userObj['activated'])) {
                // Provide a sane value.
                $userObj['activated'] = Users_Constant::ACTIVATED_INACTIVE;
            }

            if ($userObj['activated'] != Users_Constant::ACTIVATED_ACTIVE) {
                if ($reportErrors) {
                    $displayVerifyPending = ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, Users_Constant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                    $displayApprovalPending = ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, Users_Constant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
                    if (($userObj['activated'] == Users_Constant::ACTIVATED_PENDING_REG) && ($displayApprovalPending || $displayVerifyPending)) {
                        $moderationOrder = ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, Users_Constant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
                        if (!$userObj['isverified']
                                && (($moderationOrder == Users_Constant::APPROVAL_AFTER) || ($moderationOrder == Users_Constant::APPROVAL_ANY)
                                        || (!empty($userObj['approved_by'])))
                                && $displayVerifyPending
                                ) {
                            $message = __('Your request to register with this site is still waiting for verification of your e-mail address. Please check your inbox for a message from us.');
                        } elseif (empty($userObj['approved_by'])
                                && (($moderationOrder == Users_Constant::APPROVAL_BEFORE) || ($moderationOrder == Users_Constant::APPROVAL_ANY))
                                && $displayApprovalPending
                                ) {
                            $message = __('Your request to register with this site is still waiting for approval from a site administrator.');
                        }

                        if (isset($message) && !empty($message)) {
                            return LogUtil::registerError($message);
                        }
                        // It is a pending registration but the site admin elected to not display this to the user.
                        // No exception here because the answer is simply "no." This will fall through to return false.
                    } elseif (($userObj['activated'] == Users_Constant::ACTIVATED_INACTIVE) && ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, Users_Constant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS)) {
                        $message = __('Your account has been disabled. Please contact a site administrator for more information.');
                    } elseif (($userObj['activated'] == Users_Constant::ACTIVATED_PENDING_DELETE) && ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS, Users_Constant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS)) {
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
                    throw new Zikula_Exception_Fatal();
                }
            } else {
                $authArgs = array(
                    'authentication_info'   => $authenticationInfo,
                    'authentication_method' => $authenticationMethod,
                );
                $authenticatedUid = ModUtil::apiFunc($authenticationMethod['modname'], 'Authentication', 'getUidForAuthenticationInfo', $authArgs, 'Zikula_Api_AbstractAuthentication');
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
                $event = new Zikula_Event('user.login.veto', $userObj, $eventArgs);
                $event = EventUtil::notify($event);

                if ($event->isStopped()) {
                    // The login attempt has been vetoed by one or more modules.
                    $eventData = $event->getData();

                    if (isset($eventData['retry']) && $eventData['retry']) {
                        $sessionVarName = 'Users_Controller_User_login';
                        $sessionNamespace = 'Zikula_Users';
                        $redirectURL = ModUtil::url('Users', 'user', 'login', array('csrftoken' => SecurityUtil::generateCsrfToken()));
                    } elseif (isset($eventData['redirect_func'])) {
                        if (isset($eventData['redirect_func']['session'])) {
                            $sessionVarName = $eventData['redirect_func']['session']['var'];
                            $sessionNamespace = isset($eventData['redirect_func']['session']['namespace']) ? $eventData['redirect_func']['session']['namespace'] : '/';
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
                        //System::redirect($redirectURL);
                        throw new Zikula_Exception_Redirect($redirectURL);
                    } else {
                        throw new Zikula_Exception_Forbidden();
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
     */
    public static function setUserByUname($uname, $rememberMe = false)
    {
        if (!isset($uname) || !is_string($uname) || empty($uname)) {
            throw new Zikula_Exception_Fatal(__('Attempt to set the current user with an invalid uname.'));
        }

        $uid = self::getIdFromName($uname);

        $authenticationMethod = array(
            'modname' => 'Users',
            'method'  => 'uname',
        );

        self::setUserByUid($uid, $rememberMe, $authenticationMethod);
    }

    /**
     * Sets the currently logged in active user to the user account for the given uid.
     *
     * No events are fired from this function. To receive events, use {@link loginUsing()}.
     *
     * @param numeric $uid        The user id of the user who should be logged into the system; required.
     * @param boolean $rememberMe If the user's login should be maintained on the computer from which the user is logging in, set this to true;
     *                                          optional, defaults to false.
     * @param array $authenticationMethod An array containing the authentication method used to log the user in; optional,
     *                                          defaults to the 'Users' module 'uname' method.
     *
     * @return void
     */
    public static function setUserByUid($uid, $rememberMe = false, array $authenticationMethod = null)
    {
        if (!isset($uid) || empty($uid) || ((string)((int)$uid) != $uid)) {
            throw new Zikula_Exception_Fatal(__('Attempt to set the current user with an invalid uid.'));
        }

        $userObj = self::getVars($uid);
        if (!isset($userObj) || !is_array($userObj) || empty($userObj)) {
            throw new Zikula_Exception_Fatal(__('Attempt to set the current user with an unknown uid.'));
        }

        if (!isset($authenticationMethod)) {
            $authenticationMethod = array(
                'modname' => 'Users',
                'method'  => 'uname',
            );
        } elseif (empty($authenticationMethod) || !isset($authenticationMethod['modname']) || empty($authenticationMethod['modname'])
                || !isset($authenticationMethod['method']) || empty($authenticationMethod['method'])
                ) {
            throw new Zikula_Exception_Fatal(__('Attempt to set the current user with an invalid authentication method.'));
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

        $uname = DataUtil::formatForStore(mb_strtolower($uname));

        if ($excludeUid > 1) {
            $dbinfo = DBUtil::getTables();
            $usersColumn = $dbinfo['users_column'];
            $where = "({$usersColumn['uname']} = '{$uname}') AND ({$usersColumn['uid']} != {$excludeUid})";
            $ucount = DBUtil::selectObjectCount('users', $where);
        } else {
            $ucount = DBUtil::selectObjectCountByID('users', $uname, 'uname');
        }

        return $ucount;
    }

    /**
     * Counts how many times an e-mail address has been used by user accounts in the system.
     *
     * @param string $emailAddress The e-mail address in question (required).
     * @param int    $excludeUid   The uid to exclude from the check, used when checking modifications.
     *
     * @return integer|boolean The count, or false on error.
     */
    public static function getEmailUsageCount($emailAddress, $excludeUid = 0)
    {
        if (!is_numeric($excludeUid) || ((int)$excludeUid != $excludeUid)) {
            return false;
        }

        $emailAddress = DataUtil::formatForStore(mb_strtolower($emailAddress));

        $dbinfo = DBUtil::getTables();
        $usersColumn = $dbinfo['users_column'];
        $where = "({$usersColumn['email']} = '{$emailAddress}')";
        if ($excludeUid > 1) {
            $where .= " AND ({$usersColumn['uid']} != {$excludeUid})";
        }
        $ucount = DBUtil::selectObjectCount('users', $where);

        $verifyChgColumn = $dbinfo['users_verifychg_column'];
        $where = "({$verifyChgColumn['newemail']} = '{$emailAddress}') AND ({$verifyChgColumn['changetype']} = "
                . Users_Constant::VERIFYCHGTYPE_EMAIL . ")";
        if ($excludeUid > 1) {
            $where .= " AND ({$verifyChgColumn['uid']} != {$excludeUid})";
        }
        $vcount = DBUtil::selectObjectCount('users_verifychg', $where);

        if (($ucount === false) || ($vcount === false)) {
            return false;
        } else {
            return ($ucount + $vcount);
        }
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
        if ($userObj['activated'] == Users_Constant::ACTIVATED_PENDING_REG) {
            // Get isverified from the attributes.
            if (isset($userObj['__ATTRIBUTES__']['_Users_isVerified'])) {
                $userObj['isverified'] = $userObj['__ATTRIBUTES__']['_Users_isVerified'];
                unset($userObj['__ATTRIBUTES__']['_Users_isVerified']);
            } else {
                $userObj['isverified'] = false;
            }

            // Get verificationsent from the users_verifychg table
            $dbinfo = DBUtil::getTables();
            $verifyChgColumn = $dbinfo['users_verifychg_column'];
            $where = "WHERE ({$verifyChgColumn['uid']} = {$userObj['uid']}) AND ({$verifyChgColumn['changetype']} = " . Users_Constant::VERIFYCHGTYPE_REGEMAIL . ")";
            $verifyChgList = DBUtil::selectObjectArray('users_verifychg', $where, '', -1, 1);
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
            // load the Users database information
            ModUtil::dbInfoLoad('Users', 'Users');

            // get user info, don't cache as this information must be up-to-date
            // NOTE: Do not use a permission filter, or you will enter an infinite nesting loop where getVars calls checkPermission (from within
            // DBUtil), which will call getVars to find out who you are, which will call checkPermission, etc., etc.
            // Do your permission check in the API that is using UserUtil.
            $user = DBUtil::selectObjectByID('users', $id, $idfield, null, null, null, false);

            // If $idfield is email, make sure that we are getting a unique record.
            if ($user && ($idfield == 'email')) {
                $emailCount = self::getEmailUsageCount($id);

                if (($emailCount > 1) || ($emailCount === false)) {
                    $user = false;
                }
            }

            // update cache
            // user can be false (error) or empty array (no such user)
            if ($user === false || empty($user)) {
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
                if ($user === false) {
                    return LogUtil::registerError(__('Error! Could not load data.'));
                }

                return false;
            } else {
                // This check should come at the very end, here, so that if $force is true the vars get
                // reloaded into cache no matter what $getRegistration is set to. If not, and this is
                // called from setVar(), and setVar() changed the 'activated' value, then we'd have trouble.
                if (($getRegistration && ($user['activated'] != Users_Constant::ACTIVATED_PENDING_REG))
                        || (!$getRegistration && ($user['activated'] == Users_Constant::ACTIVATED_PENDING_REG))) {
                    return false;
                }

                $user = self::postProcessGetRegistration($user);

                $cache[$user['uid']] = $user;
                $unames[$user['uname']] = $user['uid'];
                $emails[$user['email']] = $user['uid'];
            }
        } elseif (($getRegistration && ($user['activated'] != Users_Constant::ACTIVATED_PENDING_REG))
                || (!$getRegistration && ($user['activated'] == Users_Constant::ACTIVATED_PENDING_REG))) {

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
        $dbtable = DBUtil::getTables();

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
            if (self::fieldAlias($name)) {
                // this value comes from the users table
                $obj = array(
                    'uid' => $uid,
                    $name => $value
                );
                $oldValue = isset($origUserObj[$name]) ? $origUserObj[$name] : null;
                $varIsSet = (bool)DBUtil::updateObject($obj, 'users', '', 'uid');
            } else {
                // Not a table field alias, not 'uid', and not 'uname'. Treat it as an attribute.
                $dudAttributeName = self::convertOldDynamicUserDataAlias($name);
                if ($dudAttributeName) {
                    LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), E_USER_DEPRECATED);
                    // $name is a former DUD /old style user information now stored as an attribute
                    $attributeName = $dudAttributeName;
                } else {
                    // $name not in the users table and also not found in the mapping array and also not one of the
                    // forbidden names, let's make an attribute out of it
                    $attributeName = $name;
                }

                $obj = array(
                    'uid' => $uid,
                    '__ATTRIBUTES__' => array(
                        $attributeName => $value
                    )
                );
                $oldValue = isset($origUserObj['__ATTRIBUTES__'][$attributeName]) ? $origUserObj['__ATTRIBUTES__'][$attributeName] : null;

                $varIsSet = (bool)ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);
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
                $updateEvent = new Zikula_Event($eventName, $updatedUserObj, $eventArgs, $eventData);
                EventUtil::notify($updateEvent);
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

            return LogUtil::registerArgsError();
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

            return LogUtil::registerArgsError();
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
        $minLength = ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH, Users_Constant::DEFAULT_PASSWORD_MINIMUM_LENGTH);

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
                return LogUtil::registerArgsError();
            }
            $hashAlgorithmName = self::getPasswordHashMethodName($hashMethodCode);
            if (!$hashAlgorithmName) {
                return LogUtil::registerArgsError();
            }

        } else {
            $hashAlgorithmName = ModUtil::getVar('Users', 'hash_method', '');
            $hashMethodCode = self::getPasswordHashMethodCode($hashAlgorithmName);
            if (!$hashMethodCode) {
                return LogUtil::registerArgsError();
            }
        }

        return SecurityUtil::getSaltedHash($unhashedPassword, $hashAlgorithmName, self::getPasswordHashMethods(false), 5, Users_Constant::SALT_DELIM);

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
        $minLength = ModUtil::getVar('Users', 'minpass', 5);
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
            return LogUtil::registerArgsError();
        }

        if (!isset($hashedPassword) || !is_string($hashedPassword) || empty($hashedPassword) || (strpos($hashedPassword, Users_Constant::SALT_DELIM) === false)) {
            return LogUtil::registerArgsError();
        }

        $passwordsMatch = SecurityUtil::checkSaltedHash($unhashedPassword, $hashedPassword, self::getPasswordHashMethods(true), Users_Constant::SALT_DELIM);

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
            if (self::fieldAlias($name)) {
                // this value comes from the users table
                $obj = array(
                    'uid' => $uid,
                    $name => '',
                );
                $oldValue = isset($origUserObj[$name]) ? $origUserObj[$name] : null;
                $varIsDeleted = (bool)DBUtil::updateObject($obj, 'users', '', 'uid');
            } else {
                // Not a table field alias, not 'uid', and not 'uname'. Treat it as an attribute.
                $dudAttributeName = self::convertOldDynamicUserDataAlias($name);
                if ($dudAttributeName) {
                    LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), E_USER_DEPRECATED);
                    // $name is a former DUD /old style user information now stored as an attribute
                    $attributeName = $dudAttributeName;
                } else {
                    // $name not in the users table and also not found in the mapping array and also not one of the
                    // forbidden names, let's make an attribute out of it
                    $attributeName = $name;
                }
                $oldValue = isset($origUserObj['__ATTRIBUTES__'][$attributeName]) ? $origUserObj['__ATTRIBUTES__'][$attributeName] : null;

                $varIsDeleted = (bool)ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $attributeName);
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
                    $updateEvent = new Zikula_Event('user.registration.update', $updatedUserObj, $eventArgs, $eventData);
                } else {
                    $updateEvent = new Zikula_Event('user.account.update', $updatedUserObj, $eventArgs, $eventData);
                }
                EventUtil::notify($updateEvent);
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
        static $theme;

        if (isset($theme) && !$force) {
            return $theme;
        }
            
        if (CookieUtil::getCookie('zikulaMobileTheme') == '1' && ModUtil::getVar('Theme', 'enable_mobile_theme', false)) {
            $pagetheme = 'Mobile';
        } else if (CookieUtil::getCookie('zikulaMobileTheme') != '2' && ModUtil::getVar('Theme', 'enable_mobile_theme', false)) {
            include_once("system/Theme/lib/vendor/Mobile_Detect.php");
            $detect = new Mobile_Detect();
            if ($detect->isMobile()) {
                $pagetheme = 'Mobile';
            }
        } else {
             $pagetheme = FormUtil::getPassedValue('theme', null, 'GETPOST');
        }

        // Page-specific theme
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        $qstring = System::serverGetVar('QUERY_STRING');
        if (!empty($pagetheme)) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($pagetheme));
            if ($themeinfo['state'] == ThemeUtil::STATE_ACTIVE && ($themeinfo['user'] || $themeinfo['system'] || ($themeinfo['admin'] && ($type == 'admin'))) && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                return self::_getThemeFilterEvent($themeinfo['name'], 'page-specific');
            }
        }

        // check for an admin theme
        if (($type == 'admin' || $type == 'adminplugin') && SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            $admintheme = ModUtil::getVar('Admin', 'admintheme');
            if (!empty($admintheme)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($admintheme));
                if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                    return self::_getThemeFilterEvent($themeinfo['name'], 'admin-theme');
                }
            }
        }

        // set a new theme for the user
        $newtheme = FormUtil::getPassedValue('newtheme', null, 'GETPOST');
        if (!empty($newtheme) && System::getVar('theme_change')) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($newtheme));
            if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                if (self::isLoggedIn()) {
                    self::setVar('theme', $newtheme);
                } else {
                    SessionUtil::setVar('theme', $newtheme);
                }

                return self::_getThemeFilterEvent($themeinfo['name'], 'new-theme');
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
            if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                return self::_getThemeFilterEvent($themeinfo['name'], 'user-theme');
            }
        }

        // default site theme
        $defaulttheme = System::getVar('Default_Theme');
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($defaulttheme));
        if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            return self::_getThemeFilterEvent($themeinfo['name'], 'default-theme');
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
        $event = new Zikula_Event('user.gettheme', null, array('type' => $type), $themeName);

        return EventUtil::notify($event)->getData();
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
    public static function getAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = -1, $startnum = -1, $activated = '', $regexpfield = '', $regexpression = '', $where = '')
    {
        $dbtable = DBUtil::getTables();
        $userscolumn = $dbtable['users_column'];

        if (empty($where)) {
            $sqlFragments = array();
            if (!empty($regexpfield) && (array_key_exists($regexpfield, $userscolumn)) && !empty($regexpression)) {
                $sqlFragments[] = '(' . $userscolumn[$regexpfield] . ' REGEXP "' . DataUtil::formatForStore($regexpression) . '")';
            }
            if (!empty($activated) && is_numeric($activated) && array_key_exists('activated', $userscolumn)) {
                $sqlFragments[] = "({$userscolumn['activated']} != '" . DataUtil::formatForStore($activated) . "')";
            }

            if (!empty($sqlFragments)) {
                $where = 'WHERE ' . implode(' AND ', $sqlFragments);
            }
        }

        $sortby = '';
        if (!empty($sortbyfield)) {
            // Do not skip the following line, it might still have $where stuff in there!
            $sqlFragments = array();
            if (array_key_exists($sortbyfield, $userscolumn)) {
                $sqlFragments[] = $userscolumn[$sortbyfield] . ' ' . DataUtil::formatForStore($sortorder);
            } else {
                $sqlFragments[] = DataUtil::formatForStore($sortbyfield) . ' ' . DataUtil::formatForStore($sortorder); //sort by dynamic.....
            }
            if ($sortbyfield != 'uname') {
                $sqlFragments[] = $userscolumn['uname'] . ' ASC ';
            }

            if (!empty($sqlFragments)) {
                $sortby = 'ORDER BY ' . implode(', ', $sqlFragments);
            }
        }

        // NOTE: DO NOT use a permission filter here to avoid potential infinite loops (DBUtil calls SecurityUtil
        // which calls back to UserUtil. Do your permission check in the API that uses UserUtil.
        return DBUtil::selectObjectArray('users', $where, $sortby, $startnum, $limit, 'uid');
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

        return ($result && isset($result['uid']) ? $result['uid'] : false);
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

        return ($result && isset($result['uid']) ? $result['uid'] : false);
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
            // Only need to retrieve users table columns once.
            static $usersColumns;

            if (!isset($usersColumns)) {
                $dbtables = DBUtil::getTables();
                $usersColumns = $dbtables['users_column'];
            }

            $isFieldAlias = array_key_exists($label, $usersColumns);
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
