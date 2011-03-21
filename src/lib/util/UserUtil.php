<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
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
    // Activated states

    /**
     * Pending registration (not able to log in).
     *
     * Moderation and/or e-mail verification are in use in the registration process, and one or more of the required steps has not yet
     * been completed.
     */
    const ACTIVATED_PENDING_REG = -32768;

    /**
     * Inactive (Not able to log in).
     *
     * This state may be set by the site administrator to prevent any attempt to log in with this account.
     */
    const ACTIVATED_INACTIVE = 0;

    /**
     * Active (Able to log in).
     */
    const ACTIVATED_ACTIVE = 1;

    /**
     * Inactive until Terms of Use and/or Privacy Policy accepted (able to start log-in, but must accept TOU/PP to complete).
     */
    const ACTIVATED_INACTIVE_TOUPP = 2;

    /**
     * Inactive until password changed (able to start log-in, but must change web site account password to complete).
     *
     * Note, if the user attempts to log in using an alternate means of authentication (e.g., LDAP, OpenID, etc.) then the login
     * attempt will proceed without asking the user for a new password.
     */
    const ACTIVATED_INACTIVE_PWD = 4;

    /**
     * Inactive until Terms of Use and/or Privacy Policy accepted and password changed (aee above).
     */
    const ACTIVATED_INACTIVE_PWD_TOUPP = 6;

    /**
     * Marked for deletion (not able to log on)--FUTURE USE.
     *
     * Similar to inactive, but with the expectation that the account could be removed at any time. This state can also be used to
     * simulate deletion without actually deleting the account.
     */
    const ACTIVATED_PENDING_DELETE = 16384;

    // Registration verification and pasword generation options

    /**
     * User chooses password, no verification by e-mail.
     */
    const VERIFY_NO = 0;

    /**
     * System-generated password is sent directly to e-mail address.
     *
     * NOTE: Use of system-generated passwords is deprecated due to security concerns when sending passwords via e-mail.
     *
     * @deprecated since 1.3.0
     */
    const VERIFY_SYSTEMPWD = 1;

    /**
     * User chooses password, then activates account via e-mail.
     */
    const VERIFY_USERPWD = 2;

    // Determines the allowed order of approval (moderation) and e-mail verification (activation)

    /**
     * A moderator must approve the registration application, then the user can verify his e-mail.
     */
    const APPROVAL_BEFORE = 0;

    /**
     * The user must verify his e-mail address first, then the moderator can approve the account (but the admin can override this).
     */
    const APPROVAL_AFTER = 1;

    /**
     * Verification and approval can happen in any order.
     */
    const APPROVAL_ANY = 2;

    // Change verification type for the users_verifychg table.

    /**
     * Change of password request.
     */
    const VERIFYCHGTYPE_PWD = 1;

    /**
     * Change of e-mail address request, pending e-mail address verification.
     */
    const VERIFYCHGTYPE_EMAIL = 2;

    /**
     * Registration e-mail verification.
     */
    const VERIFYCHGTYPE_REGEMAIL = 3;

    /**
     * Default salt delimeter character.
     */
    const SALT_DELIM = '$';

    /**
     * Date-time format for use with DateTime#format(), date() and gmdate() for database storage.
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * A date/time indicating that a change request verification has expired.
     */
    const EXPIRED = '1901-12-21 20:45:52';

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
        LogUtil::log(__f('Warning! %1$s::%2$s is deprecated. Please use %1$s::%3$s instead.', array(__CLASS__, 'getPNUsers', 'getUsers')), E_USER_DEPRECATED);
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
        LogUtil::log(__f('Warning! %1$s::%2$s is deprecated. Please use %1$s::%3$s instead.', array(__CLASS__, 'getPNGroup', 'getGroup')), E_USER_DEPRECATED);
        return self::getGroup($gid);
    }

    /**
     * Return a group object.
     *
     * @param integer $gid The groupID to retrieve.
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
        LogUtil::log(__f('Warning! %1$s::%2$s is deprecated. Please use %1$s::%3$s instead.', array(__CLASS__, 'getPNGroups', 'getGroups')), E_USER_DEPRECATED);
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
        LogUtil::log(__f('Warning! %1$s::%2$s is deprecated. Please use %1$s::%3$s instead.', array(__CLASS__, 'getPNUserIdList', 'getUserIdList')), E_USER_DEPRECATED);
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

        $keys = array_keys($userdata);
        $size = sizeof($keys);
        $list = '';

        if ($size == 0) {
            return '-1';
        }

        for ($i = 0; $i < $size; $i++) {
            $list .= $keys[$i] . $separator;
        }

        if (($length = strlen($list)) > 0) {
            $list = substr($list, 0, $length - 1);
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
        LogUtil::log(__f('Warning! %1$s::%2$s is deprecated. Please use %1$s::%3$s instead.', array(__CLASS__, 'getPNGroupIdList', 'getGroupIdList')), E_USER_DEPRECATED);
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

        $keys = array_keys($groupdata);
        $numkeys = sizeof($keys);
        $list = '';

        for ($i = 0; $i < $numkeys; $i++) {
            $list .= $keys[$i] . $separator;
        }

        if (($length = strlen($list)) > 0) {
            $list = substr($list, 0, $length - 1);
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
            $where = "WHERE z_uid = '" . DataUtil::formatForStore($uid) . "'";
        }

        $groups = DBUtil::selectFieldArray('group_membership', 'gid', $where);
        return $groups;
    }

    /**
     * Return a string list of group-ids for the specified user.
     *
     * @param integer $uid       The user ID for which we want the groups.
     * @param string  $separator The field separator to use (default=",") (optional).
     *
     * @return string A string list of group ids.
     */
    public static function getGroupListForUser($uid = 0, $separator = ",")
    {
        if (!$uid) {
            $uid = self::getVar('uid');
        }

        $gidArray = self::getGroupsForUser($uid);
        $size = count($gidArray);
        $gidlist = '';

        if ($size == 0) {
            return "-1";
        }

        for ($i = 0; $i < $size; $i++) {
            $gidlist .= $gidArray[$i] . $separator;
        }

        if (($length = strlen($gidlist)) > 0) {
            $gidlist = substr($gidlist, 0, $length - 1);
        }

        return $gidlist;
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

        $where = "WHERE z_gid = '" . DataUtil::formatForStore($gid) . "'";
        $users = DBUtil::selectFieldArray('group_membership', 'uid', $where);
        return $users;
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
        $dudfields = DBUtil::selectObjectArray('user_property');
        return $dudfields;
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
        LogUtil::log(__f('Warning! %1$s::%2$s is deprecated. Please use %1$s::%3$s instead.', array(__CLASS__, 'getSelectorData_PNGroup', 'getSelectorData_Group')), E_USER_DEPRECATED);
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

        $groupdata = self::getGroups('', 'ORDER BY z_name');

        if (sizeof($groupdata) == 0) {
            return $dropdown;
        }

        $keys = array_keys($groupdata);
        $numkeys = sizeof($keys);

        if ($includeAll) {
            $dropdown[] = array('id' => $includeAll, 'name' => $allText);
        }

        for ($i = 0; $i < $numkeys; $i++) {
            if (!isset($ignore[$keys[$i]])) {
                $dropdown[] = array('id' => $keys[$i], 'name' => $groupdata[$keys[$i]]['name']);
            }
        }

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
            $where = "WHERE z_uid NOT IN (" . DataUtil::formatForStore($exclude) . ")";
        }

        $userdata = self::getUsers($where, 'ORDER BY z_uname');

        if (sizeof($userdata) == 0) {
            return $dropdown;
        }

        $keys = array_keys($userdata);
        $numkeys = sizeof($keys);

        if ($includeAll) {
            $dropdown[] = array('id' => $includeAll, 'name' => $allText);
        }

        for ($i = 0; $i < $numkeys; $i++) {
            if (!isset($ignore[$keys[$i]])) {
                $dropdown[] = array('id' => $keys[$i], 'name' => $userdata[$keys[$i]]['name']);
            }
        }

        return $dropdown;
    }

    /**
     * Login.
     *
     * @param string  $loginID             Login Id.
     * @param string  $userEnteredPassword The Password.
     * @param boolean $rememberme          Whether or not to remember login.
     * @param boolean $checkPassword       Whether or not to check the password.
     *
     * @return boolean
     */
    public static function login($loginID, $userEnteredPassword, $rememberme = false, $checkPassword = true)
    {
        LogUtil::log(__CLASS__ . '::' . __FUNCTION__ . '[' . __LINE__ . '] ' . __f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'UserUtil::loginUsing()')), E_USER_DEPRECATED);
        $authinfo = array(
                'loginid' => $loginID,
                'pass' => $userEnteredPassword,
        );
        return self::loginUsing('Users', $authinfo, $rememberme, null, $checkPassword);
    }

    /**
     * Authenticate a user (check the user's authinfo--user name and password probably) against an authmodule.
     *
     * ATTENTION: The authmodule function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling checkPasswordUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param string $authModuleName Auth module name.
     * @param array  $authinfo       Auth info array.
     * @param string $reentrantURL   If the authmodule needs to redirect to an external authentication server (e.g., OpenID), then
     *                                  this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                  state must have already been saved by the function calling checkPasswordUsing(), and the URL must
     *                                  point to a Zikula_Controller function that is equipped to detect reentry, restore the
     *                                  saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                  is only optional if the authmodule identified by $authModuleName reports that it is not
     *                                  reentrant (e.g., Users is guaranteed to not be reentrant).
     *
     * @return bool True if authinfo authenticates; otherwise false.
     */
    public static function checkPasswordUsing($authModuleName, array $authinfo, $reentrantURL = null)
    {
        if (!isset($authModuleName) || !is_string($authModuleName) || empty($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::getInfoFromName($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::available($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::loadApi($authModuleName, 'auth')) {
            return LogUtil::registerArgsError();
        }

        if (!ModUtil::available($authModuleName)) {
            return LogUtil::registerArgsError();
        }
        if (ModUtil::apiFunc($authModuleName, 'auth', 'isReentrant', null, 'Zikula_Api_AbstractAuthentication') && (!isset($reentrantURL) || empty($reentrantURL))) {
            return LogUtil::registerArgsError();
        }

        // Authenticate the loginID and userEnteredPassword against the specified authModule.
        // This should return the uid of the user logging in. Note that there are two routes here, both get a uid.
        $checkPasswordArgs = array(
                'authinfo' => $authinfo,
                'reentrant_url' => $reentrantURL,
        );
        return ModUtil::apiFunc($authModuleName, 'auth', 'checkPassword', $checkPasswordArgs, 'Zikula_Api_AbstractAuthentication');
    }

    /**
     * Check user password without logging in (or logging in again).
     *
     * ATTENTION: The authmodule function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling authenticateUserUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param string $authModuleName The name of the authmodule to use for authentication.
     * @param array  $authinfo       The information needed by the authmodule for authentication, typically a loginid and pass.
     * @param string $reentrantURL   If the authmodule needs to redirect to an external authentication server (e.g., OpenID), then
     *                                  this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                  state must have already been saved by the function calling authenticateUserUsing(), and the URL must
     *                                  point to a Zikula_Controller function that is equipped to detect reentry, restore the
     *                                  saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                  is only optional if the authmodule identified by $authModuleName reports that it is not
     *                                  reentrant (e.g., Users is guaranteed to not be reentrant).
     *
     * @return mixed Zikula uid if the authinfo authenticates with the authmodule; otherwise false.
     */
    public static function authenticateUserUsing($authModuleName, array $authinfo, $reentrantURL = null)
    {
        if (!isset($authModuleName) || !is_string($authModuleName) || empty($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::getInfoFromName($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::available($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::loadApi($authModuleName, 'auth')) {
            return LogUtil::registerArgsError();
        }

        if (!ModUtil::available($authModuleName)) {
            return LogUtil::registerArgsError();
        }
        if (ModUtil::apiFunc($authModuleName, 'auth', 'isReentrant', null, 'Zikula_Api_AbstractAuthentication') && (!isset($reentrantURL) || empty($reentrantURL))) {
            return LogUtil::registerArgsError();
        }

        $authenticateUserArgs = array(
                'authinfo' => $authinfo,
                'reentrant_url' => $reentrantURL,
        );
        return ModUtil::apiFunc($authModuleName, 'auth', 'authenticateUser', $authenticateUserArgs, 'Zikula_Api_AbstractAuthentication');
    }

    /**
     * Login using a specific auth module.
     *
     * ATTENTION: The authmodule function(s) called during this process may redirect the user to an external server
     * to perform authorization and/or authentication. The function calling loginUsing must already have anticipated
     * the reentrant nature of this process, must already have saved pertinent user state, must have supplied a
     * reentrant URL pointing to a function that will handle reentry into the login process silently, and must clear
     * any save user state immediately following the return of this function.
     *
     * @param string  $authModuleName     Auth module name.
     * @param array   $authinfo           Auth info array.
     * @param boolean $rememberMe         Whether or not to remember login.
     * @param string  $reentrantURL       If the authmodule needs to redirect to an external authentication server (e.g., OpenID), then
     *                                      this is the URL to return to in order to re-enter the log-in process. The pertinent user
     *                                      state must have already been saved by the function calling loginUsing(), and the URL must
     *                                      point to a Zikula_Controller function that is equipped to detect reentry, restore the
     *                                      saved user state, and get the user back to the point where loginUsing is re-executed. This
     *                                      is only optional if the authmodule identified by $authModuleName reports that it is not
     *                                      reentrant (e.g., Users is guaranteed to not be reentrant), or if $checkPassword is false.
     * @param boolean $checkPassword      Whether or not to check the password.
     * @param numeric $preauthenicatedUid If $checkPassword is false because the user has already been authenticated and a uid has
     *                                      already been obtained, then the uid can be passed into loginUsing() here. If the
     *                                      preauthenticated uid is supplied, then $authinfo is not used. This parameter is
     *                                      ignored if $checkPassword is true.
     *
     * @return boolean True if the user is logged in; false if the user is not logged in or an error occurs.
     */
    public static function loginUsing($authModuleName, array $authinfo, $rememberMe = false, $reentrantURL = null, $checkPassword = true, $preauthenicatedUid = false)
    {
        // For the following, register any errors in the UI function that called this.
        if (!isset($authModuleName) || !is_string($authModuleName) || empty($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::getInfoFromName($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::available($authModuleName)) {
            return LogUtil::registerArgsError();
        } elseif (!ModUtil::loadApi($authModuleName, 'auth')) {
            return LogUtil::registerArgsError();
        }

        if (!ModUtil::available($authModuleName)) {
            return LogUtil::registerArgsError();
        }
        if ($checkPassword && ModUtil::apiFunc($authModuleName, 'auth', 'isReentrant', null, 'Zikula_Api_AbstractAuthentication') && (!isset($reentrantURL) || empty($reentrantURL))) {
            return LogUtil::registerArgsError();
        }

        // Authenticate the loginID and userEnteredPassword against the specified authModule.
        // This should return the uid of the user logging in. Note that there are two routes here, both get a uid.
        if ($checkPassword) {
            $authArgs = array(
                    'authinfo' => $authinfo,
                    'reentrant_url' => isset($args['reentrant_url']) ? $args['reentrant_url'] : null,
            );
            $authenticatedUid = ModUtil::apiFunc($authModuleName, 'auth', 'authenticateUser', $authArgs, 'Zikula_Api_AbstractAuthentication');
        } elseif (!$preauthenicatedUid) {
            $authenticatedUid = ModUtil::apiFunc($authModuleName, 'auth', 'getUidForAuthinfo', array('authinfo' => $authinfo), 'Zikula_Api_AbstractAuthentication');
        } else {
            $authenticatedUid = $preauthenicatedUid;
        }

        if (!$authenticatedUid || !is_numeric($authenticatedUid) || ((int)$authenticatedUid != $authenticatedUid)) {
            // Note that we have not actually logged into anything yet, just authenticated.
            $event = new Zikula_Event('user.login.failed', null, array(
                            'authmodule' => $authModuleName,
                            'loginid' => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                    ));
            EventUtil::notify($event);

            if (!LogUtil::hasErrors()) {
                return LogUtil::registerError(__('Sorry! Either there is no active user in our system with that information, or the information you provided does not match the information for your account. Please correct your entry and try again.'));
            } else {
                return false;
            }
        }

        // At this point we are authenticated, but not logged in. Check for things that need to be done
        // prior to login.
        // Need to make sure the Users module stuff is loaded and available, especially if we are logging in during
        // an upgrade or install.
        ModUtil::dbInfoLoad('Users', 'Users');
        ModUtil::loadApi('Users', 'user', true);

        $userObj = self::getVars($authenticatedUid);
        if (!$userObj) {
            // Might be a registration.
            $userObj = self::getVars($authenticatedUid, false, '', true);
        }

        if (!$userObj || !is_array($userObj)) {
            // Oops! The authmodule gave us a bad uid! Really should not happen unless that module's uid mapping is out of sync.
            // Note that we have not actually logged into anything yet, just authenticated.
            $event = new Zikula_Event('user.login.failed', null, array(
                            'authmodule' => $authModuleName,
                            'loginid' => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                    ));
            EventUtil::notify($event);
            return LogUtil::registerError(__('Sorry! Either there is no active user in our system with that information, or the information you provided does not match the information for your account. Please correct your entry and try again.'));
        }

        if (!isset($userObj['activated'])) {
            // Provide a sane value.
            $userObj['activated'] = self::ACTIVATED_INACTIVE;
        }

        // Check for a few statuses that will mean that we don't have a chance of logging in.
        $errorMsg = '';
        $loginDisplayMarkedForDelete = ModUtil::getVar('Users', 'login_displaymarkeddel', false);
        $loginDisplayInactive = ModUtil::getVar('Users', 'login_displayinactive', false);
        $loginDisplayApproval = ModUtil::getVar('Users', 'login_displayapproval', false);
        $loginDisplayVerify = ModUtil::getVar('Users', 'login_displayverify', false);
        if ((($userObj['activated']) == self::ACTIVATED_INACTIVE) && $loginDisplayInactive) {
            $errorMsg = __("Sorry! Your account is not active. Please contact a site administrator.");
        } elseif ((($userObj['activated']) == self::ACTIVATED_PENDING_DELETE) && $loginDisplayMarkedForDelete) {
            $errorMsg = __("Sorry! Your account is marked to be permanently closed. Please contact a site administrator.");
        } elseif (($userObj['activated']) == self::ACTIVATED_PENDING_REG) {
            if (empty($userObj['approved_by']) && $loginDisplayApproval) {
                $errorMsg = __("Sorry! Your account is still awaiting administrator approval. An e-mail message will be sent to you once an administrator has reviewed your registration request.");
            } elseif (!$userObj['isverified'] && $loginDisplayVerify) {
                $errorMsg = __("Sorry! Your e-mail address must be verified before you can log in. Check for an e-mail message containing verification instructions. If you need another verification e-mail sent, contact an administrator.");
            } elseif ($loginDisplayApproval || $loginDisplayVerify) {
                $errorMsg = __("Sorry! Your registration status is still pending.");
            }
        }
        if (!empty($errorMsg)) {
            $event = new Zikula_Event('user.login.failed', null, array(
                            'authmodule' => $authModuleName,
                            'loginid' => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                    ));
            EventUtil::notify($event);
            return LogUtil::registerError($errorMsg);
        }

        // Check if the account is active -- we still have a few inactive possibilities (accept terms, change password)
        if ($userObj['activated'] != self::ACTIVATED_ACTIVE) {
            // If we came here through the normal Users module loginScreen/login Users module UI functions, then we shouldn't
            // have to deal with terms or password status issues. If we came here from some place else, then we need to
            // ensure they get handled.
            // The status to accept terms and/or privacy policy happens no matter what authmodule is used for this login.
            $mustConfirmTOUPP = ($userObj['activated'] == self::ACTIVATED_INACTIVE_TOUPP) || ($userObj['activated'] == self::ACTIVATED_INACTIVE_PWD_TOUPP);

            // The status to force a password change only happens if the current authmodule is 'Users', but we need to know the
            // status separately from whether it has to happen right now.
            $mustChangePassword = ($userObj['activated'] == self::ACTIVATED_INACTIVE_PWD) || ($userObj['activated'] == self::ACTIVATED_INACTIVE_PWD_TOUPP);
            $mustChangePasswordRightNow = ($authModuleName == 'Users') && $mustChangePassword;

            // First, check to see if the user needs to accept the terms, privacy policy, or both. This is done no matter
            // what authmodule is in use, as it is an account status thing, not an authentication status thing.
            if ($mustConfirmTOUPP) {
                // The user needs to confirm acceptance of the terms and/or privacy policy.
                // First, let's see if the administrator is still using that stuff.
                if (ModUtil::available('legal') && (ModUtil::getVar('legal', 'termsofuse', true) || ModUtil::getVar('legal', 'privacypolicy', true))) {

                    if ($mustConfirmTOUPP && $mustChangePassword) {
                        $errorMsg = __('Your log-in request was not completed because you must agree to our terms, and must also change your account\'s password first.');
                    } elseif ($mustConfirmTOUPP) {
                        $errorMsg = __('Your log-in request was not completed because you must agree to our terms first.');
                    }
                    $callbackURL = ModUtil::url('Users', 'user', 'loginScreen', array(
                                    'authinfo' => $authinfo,
                                    'authmodule' => $authModuleName,
                                    'confirmtou' => $mustConfirmTOUPP,
                                    'changepassword' => $mustChangePasswordRightNow,
                            ));

                    // Haven't failed login yet, really. We're retrying.
                    return LogUtil::registerError($errorMsg, 403, $callbackURL);
                } else {
                    // No, the admin must have changed something with respect to the legal module since the
                    // last time we saw this user log in. Set the new activated status appropriately, depending on whether
                    // the user's password must change or not. Note that we don't care, here, whether it needs to change right now,
                    // just that it needs to change at some point.
                    if ($mustChangePassword) {
                        $userObj['activated'] = self::ACTIVATED_INACTIVE_PWD;
                    } else {
                        $userObj['activated'] = self::ACTIVATED_ACTIVE;
                    }
                    self::setVar('activated', $userObj['activated'], $userObj['uid']);
                    $mustConfirmTOUPP = false;
                }
            }

            // We only force a password change if the authmodule for this login is 'Users', hence the difference between
            // checking $mustChangePasswordRightNow and $mustChangePassword
            if ($mustChangePasswordRightNow) {
                $errorMsg = __('Your log-in request was not completed because you must change your web site account\'s password first.');
                $callbackURL = ModUtil::url('Users', 'user', 'loginScreen', array(
                                'authinfo' => $authinfo,
                                'authmodule' => $authModuleName,
                                'confirmtou' => $mustConfirmTOUPP,
                                'changepassword' => $mustChangePasswordRightNow,
                        ));

                // Haven't failed login yet, really. We're retrying.
                return LogUtil::registerError($errorMsg, 403, $callbackURL);
            }

            // If we get here, then either the account is inactive, or the account was set to confirm the terms,
            // but that is no longer needed (no more legal module, or the settings changed), or the account was set
            // to force a password change, but we don't want to do it right now because the authmodule is not 'Users'.
            // Check the status one more time.
            if (($userObj['activated'] != self::ACTIVATED_ACTIVE) && ($userObj['activated'] != self::ACTIVATED_INACTIVE_PWD)) {
                // account inactive or we have a problem understanding what status the user has, deny login
                // Note that we have not actually logged into anything yet, just authenticated.
                $event = new Zikula_Event('user.login.failed', null, array(
                                'authmodule' => $authModuleName,
                                'loginid' => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                        ));
                EventUtil::notify($event);
                return LogUtil::registerError(__('Sorry! Either there is no active user in our system with that information, or the information you provided '
                                . 'does not match the information for your account. Please correct your entry and try again.'));
            }
        }

        // BEGIN ACTUAL LOGIN
        // Made it through all the checks. We can actually log in now.
        if ($authenticatedUid) {
            // Storing Last Login date -- store it in UTC! Do not use date() function!
            $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
            if (!self::setVar('lastlogin', $nowUTC->format('Y-m-d H:i:s'), $authenticatedUid)) {
                // show messages but continue
                LogUtil::registerError(__('Error! Could not save the log-in date.'));
            }

            if (!System::isInstalling()) {
                SessionUtil::requireSession();
            }

            // Set session variables -- this is what really does the Zikula login
            SessionUtil::setVar('uid', (int)$authenticatedUid);

            if (!empty($rememberMe)) {
                SessionUtil::setVar('rememberme', 1);
            }

            // now that we've logged in the permissions previously calculated (if any) are invalid
            $GLOBALS['authinfogathered'][$authenticatedUid] = 0;

            $event = new Zikula_Event('user.login', null, array(
                            'authmodule' => $authModuleName,
                            'loginid' => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                            'user' => $authenticatedUid,
                    ));
            EventUtil::notify($event);

            return true;
        } else {
            // Really should never get here. We authenticated earlier with the same set of authinfo, but
            // if we got here then the uid returned by login was different than the one returned by
            // authenticateUser. Strange situation, so deny login.
            // Note that we have not actually logged into anything yet.
            $event = new Zikula_Event('user.login.failed', null, array(
                            'authmodule' => $authModuleName,
                            'loginid' => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                    ));
            EventUtil::notify($event);
            return false;
        }
    }

//    /**
//     * Log the user in via the REMOTE_USER SERVER property.
//     *
//     * This routine simply
//     * checks if the REMOTE_USER exists in the environment: if he does a
//     * session is created for him, regardless of the password being used.
//     *
//     * @return bool true if the user successfully logged in, false otherwise
//     */
//    public static function loginHttp()
//    {
//        $uname = System::serverGetVar('REMOTE_USER');
//        $hSec = System::getVar('session_http_login_high_security', true);
//        $rc = self::loginUsing('Users', array('loginid' => $uname, 'pass' => null), false, false);
//        if ($rc && $hSec) {
//            System::setVar('seclevel', 'High');
//        }
//
//        return $rc;
//    }

    /**
     * Log the user out.
     *
     * @return bool true if the user successfully logged out, false otherwise
     */
    public static function logout()
    {
        if (self::isLoggedIn()) {
            $event = new Zikula_Event('user.logout', null, array(
                            'authmodule' => $authModuleName,
                            'user' => self::getVar('uid'),
                    ));
            EventUtil::notify($event);

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

        if ($ucount === false) {
            return false;
        } else {
            return $ucount;
        }
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
                . self::VERIFYCHGTYPE_EMAIL . ")";
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
        if ($userObj['activated'] == self::ACTIVATED_PENDING_REG) {
            // Get isverified from the attributes.
            if (isset($userObj['__ATTRIBUTES__']['isverified'])) {
                $userObj['isverified'] = $userObj['__ATTRIBUTES__']['isverified'];
                unset($userObj['__ATTRIBUTES__']['isverified']);
            } else {
                $userObj['isverified'] = false;
            }

            // Get verificationsent from the users_verifychg table
            $dbinfo = DBUtil::getTables();
            $verifyChgColumn = $dbinfo['users_verifychg_column'];
            $where = "WHERE ({$verifyChgColumn['uid']} = {$userObj['uid']}) AND ({$verifyChgColumn['changetype']} = " . self::VERIFYCHGTYPE_REGEMAIL . ")";
            $verifyChgList = DBUtil::selectObjectArray('users_verifychg', $where, '', -1, 1);
            if ($verifyChgList && is_array($verifyChgList) && !empty($verifyChgList) && is_array($verifyChgList[0]) && !empty($verifyChgList[0])) {
                $userObj['verificationsent'] = $verifyChgList[0]['created_dt'];
            } else {
                $userObj['verificationsent'] = false;
            }

            // Calculate isapproved from approved_by
            $userObj['isapproved'] = isset($userObj['approved_by']) && !empty($userObj['approved_by']);

            // Get agreetoterms from the attributes
            if (isset($userObj['__ATTRIBUTES__']['agreetoterms'])) {
                $userObj['agreetoterms'] = $userObj['__ATTRIBUTES__']['agreetoterms'];
                unset($userObj['__ATTRIBUTES__']['agreetoterms']);
            } else {
                $userObj['agreetoterms'] = false;
            }

            // unserialize dynadata
            if (isset($userObj['__ATTRIBUTES__']['dynadata'])) {
                $userObj['dynadata'] = unserialize($userObj['__ATTRIBUTES__']['dynadata']);
                unset($userObj['__ATTRIBUTES__']['dynadata']);
            } else {
                $userObj['dynadata'] = array();
            }
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
                if (($getRegistration && ($user['activated'] != self::ACTIVATED_PENDING_REG))
                        || (!$getRegistration && ($user['activated'] == self::ACTIVATED_PENDING_REG))) {
                    return false;
                }

                $user = self::postProcessGetRegistration($user);

                $cache[$user['uid']] = $user;
                $unames[$user['uname']] = $user['uid'];
                $emails[$user['email']] = $user['uid'];
            }
        } elseif (($getRegistration && ($user['activated'] != self::ACTIVATED_PENDING_REG))
                || (!$getRegistration && ($user['activated'] == self::ACTIVATED_PENDING_REG))) {

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

        $isRegistration = false;
        $origUserObj = self::getVars($uid);
        if (!$origUserObj) {
            // Might be a registration getting updated
            $origUserObj = self::getVars($uid, false, '', true);
            if ($origUserObj) {
                $isRegistration = true;
            } else {
                // No such user record!
                return false;
            }
        }

        // this array maps old DUDs to new attributes
        $mappingarray = array(
                '_UREALNAME' => 'realname',
                '_UFAKEMAIL' => 'publicemail',
                '_YOURHOMEPAGE' => 'url',
                '_TIMEZONEOFFSET' => 'tzoffset',
                '_YOURAVATAR' => 'avatar',
                '_YLOCATION' => 'city',
                '_YICQ' => 'icq',
                '_YAIM' => 'aim',
                '_YYIM' => 'yim',
                '_YMSNM' => 'msnm',
                '_YOCCUPATION' => 'occupation',
                '_SIGNATURE' => 'signature',
                '_EXTRAINFO' => 'extrainfo',
                '_YINTERESTS' => 'interests',
                'name' => 'realname',
                'femail' => 'publicemail',
                'timezone_offset' => 'tzoffset',
                'user_avatar' => 'avatar',
                'user_icq' => 'icq',
                'user_aim' => 'aim',
                'user_yim' => 'yim',
                'user_msnm' => 'msnm',
                'user_from' => 'city',
                'user_occ' => 'occupation',
                'user_intrest' => 'interests',
                'user_sig' => 'signature',
                'bio' => 'extrainfo',
        );

        $res = false;
        if (self::fieldAlias($name)) {
            // this value comes from the users table
            $obj = array('uid' => $uid, $name => $value);
            $res = (bool)DBUtil::updateObject($obj, 'users', '', 'uid');
        } else if (array_key_exists($name, $mappingarray)) {
            LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), E_USER_DEPRECATED);
            // $name is a former DUD /old style user information now stored as an attribute
            $obj = array('uid' => $uid, '__ATTRIBUTES__' => array($mappingarray[$name] => $value));
            $res = (bool)ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);
        } else if (!in_array($name, array('uid', 'uname'))) {
            // $name not in the users table and also not found in the mapping array and also not one of the
            // forbidden names, let's make an attribute out of it
            $obj = array('uid' => $uid, '__ATTRIBUTES__' => array($name => $value));
            $res = (bool)ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);
        }

        // force loading of attributes from db
        $updatedUserObj = self::getVars($uid, true, '', $isRegistration);
        if (!$updatedUserObj) {
            // Should never get here!
            return false;
        }

        // Do not fire update event/hook unless the update happened, it was not a registration record, it was not
        // the password being updated, and the system is not currently being installed.
        if ($res && !$isRegistration && ($name != 'pass') && !System::isInstalling()) {
            // Fire the event
            $updateEvent = new Zikula_Event('user.update', $updatedUserObj);
            EventUtil::notify($updateEvent);
        }

        return $res;
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
        $minLength = ModUtil::getVar('Users', 'minpass', 5);
        if (!is_numeric($minLength) || ((int)$minLength != $minLength) || ($minLength < 1)) {
            $minLength = 5;
        }

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
     * @param int    $hashMethodCode   An internal code identifying one of the valid user password hashing methods; optional, leave this
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

        return SecurityUtil::getSaltedHash($unhashedPassword, $hashAlgorithmName, self::getPasswordHashMethods(false), 5, self::SALT_DELIM);

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

        if (!isset($hashedPassword) || !is_string($hashedPassword) || empty($hashedPassword) || (strpos($hashedPassword, self::SALT_DELIM) === false)) {
            return LogUtil::registerArgsError();
        }

        $passwordsMatch = SecurityUtil::checkSaltedHash($unhashedPassword, $hashedPassword, self::getPasswordHashMethods(true), self::SALT_DELIM);

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

        $isRegistration = false;
        $origUserObj = self::getVars($uid);
        if (!$origUserObj) {
            // Might be a registration getting updated
            $origUserObj = self::getVars($uid, false, '', true);
            if ($origUserObj) {
                $isRegistration = true;
            } else {
                // No such user record!
                return false;
            }
        }

        // this array maps old DUDs to new attributes
        $mappingarray = array(
                '_UREALNAME' => 'realname',
                '_UFAKEMAIL' => 'publicemail',
                '_YOURHOMEPAGE' => 'url',
                '_TIMEZONEOFFSET' => 'tzoffset',
                '_YOURAVATAR' => 'avatar',
                '_YLOCATION' => 'city',
                '_YICQ' => 'icq',
                '_YAIM' => 'aim',
                '_YYIM' => 'yim',
                '_YMSNM' => 'msnm',
                '_YOCCUPATION' => 'occupation',
                '_SIGNATURE' => 'signature',
                '_EXTRAINFO' => 'extrainfo',
                '_YINTERESTS' => 'interests',
                'name' => 'realname',
                'femail' => 'publicemail',
                'timezone_offset' => 'tzoffset',
                'user_avatar' => 'avatar',
                'user_icq' => 'icq',
                'user_aim' => 'aim',
                'user_yim' => 'yim',
                'user_msnm' => 'msnm',
                'user_from' => 'city',
                'user_occ' => 'occupation',
                'user_intrest' => 'interests',
                'user_sig' => 'signature',
                'bio' => 'extrainfo',
        );

        if (self::fieldAlias($name)) {
            // this value comes from the users table
            $obj = array('uid' => $uid, $name => '');
            $res = (bool)DBUtil::updateObject($obj, 'users', '', 'uid');
        } else if (array_key_exists($name, $mappingarray)) {
            LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), E_USER_DEPRECATED);
            // $name is a former DUD /old style user information now stored as an attribute
            $res = (bool)ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $mappingarray[$name]);
        } else {
            // $name not in the users table and also not found in the mapping array,
            // let's make an attribute out of it
            $res = (bool)ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $name);
        }

        // force loading of attributes from db
        $updatedUserObj = self::getVars($uid, true, '', $isRegistration);
        if (!$updatedUserObj) {
            // Should never get here!
            return false;
        }

        // Do not fire update event/hook unless the update happened, it was not a registration record, it was not
        // the password being updated, and the system is not currently being installed.
        if ($res && !$isRegistration && ($name != 'pass') && !System::isInstalling()) {
            // Fire the event
            $updateEvent = new Zikula_Event('user.update', $updatedUserObj);
            EventUtil::notify($updateEvent);
        }

        return $res;
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
     * @return string the name of the user's theme
     * @throws RuntimeException If this function was unable to calculate theme name.
     */
    public static function getTheme($force = false)
    {
        static $theme;
        if (isset($theme) && !$force) {
            return $theme;
        }

        // Page-specific theme
        $pagetheme = FormUtil::getPassedValue('theme', null, 'GETPOST');
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
        return EventUtil::notifyUntil($event)->getData();
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
        if (empty($label)) {
            return false;
        }

        // no change in uid or uname allowed
        if ($label == 'uid' || $label == 'uname') {
            return false;
        }

        $dbtables = DBUtil::getTables();
        return array_key_exists($label, $dbtables['users_column']);
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


}
