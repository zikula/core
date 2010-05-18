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
 * UserUtil
 *
 * @package Zikula_Core
 * @subpackage UserUtil
 */
class UserUtil
{
    /**
     * Return a user object
     *
     * @deprecated        to be removed in 2.0.0
     * @see               pnUserGetVars()
     * @param uid         The userID of the user to retrieve
     * @param getVars     obsolete, we also return the attributes
     *
     * @return The resulting user object
     */
    public static function getPNUser($uid, $getVars = false)
    {
        return pnUserGetVars($uid);
    }

    /**
     * Return a field from a user object
     *
     * @deprecated       to be removed in 2.0.0?
     * @see               pnUserGetVars()
     * @param id         The userID of the user to retrieve
     * @param field      The field from the user object to get
     *
     * @return The requested field
     */
    public static function getPNUserField($id, $field)
    {
        return pnUserGetVar($field, $id);
    }

    /**
     * Return a hash structure mapping uid to username
     *
     * @param where         The where clause to use (optional)
     * @param orderBy       The order by clause to use (optional)
     * @param limitOffset   The select-limit offset (optional) (default=-1)
     * @param limitNumRows  The number of rows to fetch (optional) (default=-1)
     * @param assocKey      The associative key to apply (optional) (default='gid')
     *
     * @return An array mapping uid to username
     */
    public static function getPNUsers($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'uid')
    {
        return DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Return a group object
     *
     * @param gid         The groupID to retrieve
     *
     * @return The resulting group object
     */
    public static function getPNGroup($gid)
    {
        return DBUtil::selectObjectByID('groups', $gid, 'gid');
    }

    /**
     * Return a hash structure mapping gid to groupname
     *
     * @param where          The where clause to use (optional) (default='')
     * @param orderBy        The order by clause to use (optional) (default='')
     * @param limitOffset    The select-limit offset (optional) (default=-1)
     * @param limitNumRows   The number of rows to fetch (optional) (default=-1)
     * @param assocKey       The associative key to apply (optional) (default='gid')
     *
     * @return An array mapping gid to groupname
     */
    public static function getPNGroups($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'gid')
    {
        return DBUtil::selectObjectArray('groups', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Return a (string) list of user-ids which can then be used in a SQL 'IN (...)' clause
     *
     * @param where       The where clause to use (optional)
     * @param orderBy     The order by clause to use (optional)
     * @param separator   The field separator to use (default=",") (optional)
     *
     * @return A string list of user ids
     */
    public static function getPNUserIdList($where = '', $orderBy = '', $separator = ',')
    {
        $userdata = self::getPNUsers($where, $orderBy);

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
     * Return a (string) list of group-ids which can then be used in a SQL 'IN (...)' clause
     *
     * @param where       The where clause to use (optional)
     * @param orderBy     The order by clause to use (optional)
     * @param separator   The field separator to use (default=",") (optional)
     *
     * @return A string list of group ids
     */
    public static function getPNGroupIdList($where = '', $orderBy = '', $separator = ',')
    {
        $groupdata = self::getPNGroups($where, $orderBy);

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
     * Return an array group-ids for the specified user
     *
     * @param uid         The user ID for which we want the groups
     *
     * @return An array of group IDs
     */
    public static function getGroupsForUser($uid)
    {
        if (empty($uid)) {
            return array();
        }

        $where = '';
        if ($uid != -1) {
            $where = "WHERE pn_uid = '" . DataUtil::formatForStore($uid) . "'";
        }

        $groups = DBUtil::selectFieldArray('group_membership', 'gid', $where);
        return $groups;
    }

    /**
     * Return a string list of group-ids for the specified user
     *
     * @param uid         The user ID for which we want the groups
     * @param separator   The field separator to use (default=",") (optional)
     *
     * @return A string list of group ids
     */
    public static function getGroupListForUser($uid = 0, $separator = ",")
    {
        if (!$uid) {
            $uid = pnUserGetVar('uid');
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
     * Return a string list of user-ids for the specified group
     *
     * @param gid         The group ID for which we want the users
     * @param separator   The field separator to use (default=",") (optional)
     *
     * @return an array of user IDs
     */
    public static function getUsersForGroup($gid, $separator = ",")
    {
        if (!$gid) {
            return array();
        }

        $where = "WHERE pn_gid = '" . DataUtil::formatForStore($gid) . "'";
        $users = DBUtil::selectFieldArray('group_membership', 'uid', $where);
        return $users;
    }

    /**
     * Return the defined dynamic user data fields
     *
     * @return an array of dynamic data field definitions
     */
    public static function getDynamicDataFields()
    {
        // decide if we have to use the (obsolete) DUDs from the Profile module
        $profileModule = pnConfigGetVar('profilemodule', '');
        if (empty($profileModule) || $profileModule != 'Profile' || !ModUtil::available($profileModule)) {
            return array();
        }

        ModUtil::dbInfoLoad($profileModule);
        $dudfields = DBUtil::selectObjectArray('user_property');
        return $dudfields;
    }

    /**
     * Return a string list of user-ids for the specified group
     *
     * -> no this is not what this functions does, but what does it do?
     *    It is not used within the core
     * @deprecated            ??
     *
     * @param uid             The user ID for which we want the users
     * @param assocKey        The associate Key to use
     * @param standardFields  Whether or not to also marshall the standard user properties into the DUD array
     *
     * @return an array of user IDs
     */
    public static function getUserDynamicDataFields($uid, $assocKey = 'uda_propid', $standardFields = false)
    {
        if (!$uid) {
            return array();
        }

        return pnUserGetVars($uid, '__ATTRIBUTES__');
    }

    /**
     * Return a PN array structure for the PN user group selector
     *
     * @param defaultValue    The default value of the selector (default=0) (optional)
     * @param defaultText     The text of the default value (optional)
     * @param ignore          An array of keys to ignore (optional)
     * @param includeAll      whether to include an "All" choice (optional)
     * @param allText         The text to display for the "All" choice (optional)
     *
     * @return The PN array structure for the user group selector
     */
    public static function getSelectorData_PNGroup($defaultValue = 0, $defaultText = '', $ignore = array(), $includeAll = 0, $allText = '')
    {
        $dropdown = array();

        if ($defaultText) {
            $dropdown[] = array('id' => $defaultValue, 'name' => $defaultText);
        }

        $groupdata = self::getPNGroups('', 'ORDER BY pn_name');

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
     * Return a PN array strcuture for the PN user dropdown box
     *
     * @param defaultValue    The default value of the selector (optional) (default=0)
     * @param defaultText     The text of the default value (optional) (default='')
     * @param ignore          An array of keys to ignore (optional) (default=array())
     * @param includeAll      whether to include an "All" choice (optional) (default=0)
     * @param allText         The text to display for the "All" choice (optional) (default='')
     * @param exclude         An SQL IN-LIST string to exclude specified uids
     *
     * @return The PN array structure for the user group selector
     */
    public static function getSelectorData_ZUser($defaultValue = 0, $defaultText = '', $ignore = array(), $includeAll = 0, $allText = '', $exclude = '')
    {
        $dropdown = array();

        if ($defaultText) {
            $dropdown[] = array('id' => $defaultValue, 'name' => $defaultText);
        }

        $where = '';
        if ($exclude) {
            $where = "WHERE pn_uid NOT IN (" . DataUtil::formatForStore($exclude) . ")";
        }

        $userdata = self::getPNUsers($where, 'ORDER BY pn_uname');

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

    public static function login($uname, $pass, $rememberme = false, $checkPassword = true)
    {
        if (pnUserLoggedIn()) {
            return true;
        }

        $uservars = ModUtil::getVar('Users');

        if (!pnVarValidate($uname, (($uservars['loginviaoption'] == 1) ? 'email' : 'uname'))) {
            return false;
        }

        // get the database connection
        ModUtil::dbInfoLoad('Users', 'Users');
        ModUtil::loadApi('Users', 'user', true);

        $uname = strtolower($uname);
        if (!ModUtil::available('AuthPN')) {
            if (!isset($uservars['loginviaoption']) || $uservars['loginviaoption'] == 0) {
                $user = DBUtil::selectObjectByID('users', $uname, 'uname', null, null, null, false, 'lower');
            } else {
                $user = DBUtil::selectObjectByID('users', $uname, 'email', null, null, null, false, 'lower');
            }

            if (!$user) {
                return false;
            }

            // check if the account is active
            if (isset($user['activated']) && $user['activated'] == '0') {
                // account inactive, deny login
                return false;
            } else if ($user['activated'] == '2') {
                // we need a session var here that can have 3 states
                // 0: account needs to be activated, this is the value after
                //    we detected this
                // 1: account needs to activated, user check the accept checkbox
                // 2: everything is ok
                // have we been here before?
                $confirmtou = SessionUtil::getVar('confirmtou', 0);
                switch ($confirmtou)
                {
                    case 0 :
                    // continue if legal module is active and and configured to
                    // use the terms of use
                        if (ModUtil::available('legal')) {
                            $tou = ModUtil::getVar('legal', 'termsofuse');
                            if ($tou == 1) {
                                // users must confirm terms of use before before he can continue
                                // we redirect him to the login screen
                                // to ensure that he reads this reminder
                                SessionUtil::setVar('confirmtou', 0);
                                return false;
                            }
                        }
                        break;
                    case 1 : // user has accepted the terms of use - continue
                    case 2 :
                    default :
                }
            }

            $uid = $user['uid'];

            // password check doesn't apply to HTTP(S) based login
            if ($checkPassword) {
                $upass = $user['pass'];
                $pnuser_hash_number = $user['hash_method'];
                $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods', array('reverse' => true));

                $hpass = hash($hashmethodsarray[$pnuser_hash_number], $pass);
                if ($hpass != $upass) {
                    $event = new Event('user.login.failed', null, array('username' => $uname));
                    EventManagerUtil::notify($event);
                    return false;
                }

                // Check stored hash matches the current system type, if not convert it.
                $system_hash_method = $uservars['hash_method'];
                if ($system_hash_method != $hashmethodsarray[$pnuser_hash_number]) {
                    $newhash = hash($system_hash_method, $pass);
                    $hashtonumberarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods');

                    $obj = array('uid' => $uid, 'pass' => $newhash, 'hash_method' => $hashtonumberarray[$system_hash_method]);
                    $result = DBUtil::updateObject($obj, 'users', '', 'uid');

                    if (!$result) {
                        return false;
                    }
                }
            }

            // Storing Last Login date
            if (!pnUserSetVar('lastlogin', date("Y-m-d H:i:s", time()), $uid)) {
                // show messages but continue
                LogUtil::registerError(__('Error! Could not save the log-in date.'));
            }
        } else {
            $authmodules = explode(',', ModUtil::getVar('AuthPN', 'authmodules'));
            foreach ($authmodules as $authmodule) {
                $authmodule = trim($authmodule);
                if (ModUtil::available($authmodule) && ModUtil::loadApi($authmodule, 'user')) {
                    $uid = ModUtil::apiFunc($authmodule, 'user', 'login', array('uname' => $uname, 'pass' => $pass, 'rememberme' => $rememberme, 'checkPassword' => $checkPassword));
                    if ($uid) {
                        break;
                    }
                }
            }
            if (!$uid) {
                $event = new Event('user.login.failed', null, array('user' => pnUserGetVar('uid')));
                EventManagerUtil::notify($event);
                return false;
            }
        }

        if (!defined('_ZINSTALLVER')) {
            SessionUtil::requireSession();
        }

        // Set session variables
        SessionUtil::setVar('uid', (int) $uid);
        if (!empty($rememberme)) {
            SessionUtil::setVar('rememberme', 1);
        }

        if (isset($confirmtou) && $confirmtou == 1) {
            // if we get here, the user did accept the terms of use
            // now update the status
            pnUserSetVar('activated', 1, (int) $uid);
            SessionUtil::delVar('confirmtou');
        }

        // now we've logged in the permissions previously calculated are invalid
        $GLOBALS['authinfogathered'][$uid] = 0;

        $event = new Event('user.login', null, array('user' => pnUserGetVar('uid')));
        EventManagerUtil::notify($event);

        return true;
    }

    /**
     * Log the user in via the REMOTE_USER SERVER property. This routine simply
     * checks if the REMOTE_USER exists in the PN environment: if he does a
     * session is created for him, regardless of the password being used.
     *
     * @return bool true if the user successfully logged in, false otherwise
     */
    public static function loginHttp()
    {
        $uname = pnServerGetVar('REMOTE_USER');
        $hSec  = pnConfigGetVar('session_http_login_high_security', true);
        $rc    = pnUserLogIn($uname, null, false, false);
        if ($rc && $hSec) {
            pnConfigSetVar('seclevel', 'High');
        }

        return $rc;
    }

    /**
     * Log the user out
     *
     * @public
     * @return bool true if the user successfully logged out, false otherwise
     */
    public static function logout()
    {
        if (pnUserLoggedIn()) {
            $event = new Event('user.logout', null, array('user' => pnUserGetVar('uid')));
            EventManagerUtil::notify($event);
            if (ModUtil::available('AuthPN')) {
                $authmodules = explode(',', ModUtil::getVar('AuthPN', 'authmodules'));
                foreach ($authmodules as $authmodule)
                {
                    $authmodule = trim($authmodule);
                    if (ModUtil::available($authmodule) && ModUtil::loadApi($authmodule, 'user')) {
                        if (!$result = ModUtil::apiFunc($authmodule, 'user', 'logout')) {
                            return false;
                        }
                    }
                }
            }

            // delete logged on user the session
            // SessionUtil::delVar('rememberme');
            // SessionUtil::delVar('uid');
            session_destroy();
        }

        return true;
    }

    /**
     * is the user logged in?
     *
     * @public
     * @returns bool true if the user is logged in, false if they are not
     */
    public static function isLoggedIn()
    {
        return (bool) SessionUtil::getVar('uid');
    }

    /**
     * Get all user variables, maps new style attributes to old style user data.
     *
     * @param uid $ the user id of the user
     * @return array an associative array with all variables for a user
     */
    public static function getVars($id, $force = false, $idfield = '')
    {
        if (empty($id)) {
            return false;
        }

        // assign a value for the parameter idfield if it is necessary and prevent from possible typing mistakes
        if ($idfield  == '' || ($idfield != 'uid' && $idfield != 'uname' && $idfield != 'email')) {
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
        if ($idfield == 'uname' && isset($unames[$id]) && $force == false) {
            if ($unames[$id] !== false) {
                return $cache[$unames[$id]];
            }
            return false;
        }

        if ($idfield == 'email' && isset($emails[$id]) && $force == false) {
            if ($emails[$id] !== false) {
                return $cache[$emails[$id]];
            }
            return false;
        }

        if (isset($cache[$id]) && $force == false) {
            return $cache[$id];
        }

        // load the Users database information
        ModUtil::dbInfoLoad('Users', 'Users');

        // get user info, don't cache as this information must be up-to-date
        $user = DBUtil::selectObjectByID('users', $id, $idfield, null, null, null, false);
        // user can be false (error) or empty array (no such user)
        if ($user === false || empty($user)) {
            switch ($idfield)
            {
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
        }

        $cache[$user['uid']] = $user;
        $unames[$user['uname']] = $user['uid'];
        $emails[$user['email']] = $user['uid'];

        return ($user);
    }

    /**
     * get a user variable
     *
     * @param name $ the name of the variable
     * @param uid $ the user to get the variable for
     * @param default $ the default value to return if the specified variable doesn't exist
     * @return string the value of the user variable if successful, null otherwise
     */
    public static function getVar($name, $uid = -1, $default = false)
    {
        if (empty($name)) {
            return;
        }

        // bug fix #1311 [landseer]
        if (isset($uid) && !is_numeric($uid)) {
            return;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid');
        }
        if (empty($uid)) {
            return;
        }

        // get this user's variables
        $vars = pnUserGetVars($uid);

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
     * Set a user variable. This can be
     * - a field in the users table
     * - or an attribute and in this case either a new style attribute or an old style user information.
     *
     * Examples:
     * pnUserSetVar('pass', 'mysecretpassword'); // store a password (should be hashed of course)
     * pnUserSetVar('avatar', 'mypicture.gif');  // stores an users avatar, new style
     * (internally both the new and the old style write the same attribute)
     *
     * If the user variable does not exist it will be created automatically. This means with
     * pnUserSetVar('somename', 'somevalue');
     * you can easily create brand new users variables onthefly.
     *
     * This function does not allow you to set uid or uname.
     *
     * @param name $ the name of the variable
     * @param value $ the value of the variable
     * @param uid $ the user to set the variable for
     * @return bool true if the set was successful, false otherwise
     */
    public static function setVar($name, $value, $uid = -1)
    {
        $pntable = pnDBGetTables();

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
                'bio' => 'extrainfo');

        $res = false;
        if (pnUserFieldAlias($name)) {
            // this value comes from the users table
            $obj = array('uid' => $uid, $name => $value);
            $res = (bool) DBUtil::updateObject($obj, 'users', '', 'uid');
        } else if (array_key_exists($name, $mappingarray)) {
            LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), 'STRICT');
            // $name is a former DUD /old style user information now stored as an attribute
            $obj = array('uid' => $uid, '__ATTRIBUTES__' => array($mappingarray[$name] => $value));
            $res = (bool) ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);

        } else if (!in_array($name, array('uid', 'uname'))) {
            // $name not in the users table and also not found in the mapping array and also not one of the
            // forbidden names, let's make an attribute out of it
            $obj = array('uid' => $uid, '__ATTRIBUTES__' => array($name => $value));
            $res = (bool) ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);
        }

        // force loading of attributes from db
        pnUserGetVars($uid, true);
        return $res;
    }

    public static function setPassword($pass)
    {
        $method = ModUtil::getVar('Users', 'hash_method');
        $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods');
        pnUserSetVar('pass', hash($method, $pass));
        pnUserSetVar('hash_method', $hashmethodsarray[$method]);
    }

    /**
     * Delete the contents of a user variable. This can either be
     * - a variable stored in the users table or
     * - an attribute to the users table, either a new style sttribute or the old style user information
     *
     * Examples:
     * pnUserDelVar('ublock');  // clears the recent users table entry for 'ublock'
     * pnUserDelVar('_YOURAVATAR', 123), // removes a users avatar, old style (uid = 123)
     * pnUserDelVar('avatar', 123);  // removes a users avatar, new style (uid=123)
     * (internally both the new style and the old style clear the same attribute)
     *
     * It does not allow the deletion of uid, email, uname and pass (word) as these are mandatory
     * fields in the users table.
     *
     * @param name $ the name of the variable
     * @param uid $ the user to delete the variable for
     * @return boolen true on success, false on failure
     */
    public static function delVar($name, $uid = -1)
    {
        // Prevent deletion of core fields (duh)
        if (empty($name) || ($name == 'uid') || ($name == 'email') || ($name == 'pass') || ($name == 'uname')) {
            return false;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid');
        }
        if (empty($uid)) {
            return false;
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
                'bio' => 'extrainfo');

        if (pnUserFieldAlias($name)) {
            // this value comes from the users table
            $obj = array('uid' => $uid, $name => '');
            return (bool) DBUtil::updateObject($obj, 'users', '', 'uid');
        } else if (array_key_exists($name, $mappingarray)) {
            LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), 'STRICT');
            // $name is a former DUD /old style user information now stored as an attribute
            $res = ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $mappingarray[$name]);

        } else {
            // $name not in the users table and also not found in the mapping array,
            // let's make an attribute out of it
            $res = ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $name);
        }

        // force loading of attributes from db
        pnUserGetVars($uid, true);
        return (bool) $res;
    }

    /**
     * get the user's theme
     * This function will return the current theme for the user.
     * Order of theme priority:
     *  - page-specific
     *  - category
     *  - user
     *  - system
     *
     * @public
     * @return string the name of the user's theme
     **/
    public static function getTheme($force = false)
    {
        static $theme;
        if (isset($theme) && !$force) {
            return $theme;
        }

        // Page-specific theme
        $pagetheme = FormUtil::getPassedValue('theme', null, 'GETPOST');
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');
        $qstring = pnServerGetVar('QUERY_STRING');
        if (!empty($pagetheme)) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($pagetheme));
            if ($themeinfo['state'] == PNTHEME_STATE_ACTIVE && ($themeinfo['user'] || $themeinfo['system'] || ($themeinfo['admin'] && ($type == 'admin' || stristr($qstring, 'admin.php')))) && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $theme = _pnUserGetThemeEvent($themeinfo['name']);
                return $theme;
            }
        }

        // check for an admin theme
        if (($type == 'admin' || stristr($qstring, 'admin.php')) && SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            $admintheme = ModUtil::getVar('Admin', 'admintheme');
            if (!empty($admintheme)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($admintheme));
                if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                    $theme = _pnUserGetThemeEvent($themeinfo['name']);
                    return $theme;
                }
            }
        }

        // set a new theme for the user
        $newtheme = FormUtil::getPassedValue('newtheme', null, 'GETPOST');
        if (!empty($newtheme) && pnConfigGetVar('theme_change')) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($newtheme));
            if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                if (pnUserLoggedIn()) {
                    pnUserSetVar('theme', $newtheme);
                } else {
                    SessionUtil::setVar('theme', $newtheme);
                }
                $theme = _pnUserGetThemeEvent($themeinfo['name']);
                return $theme;
            }
        }

        // User theme
        if (pnConfigGetVar('theme_change')) {
            if ((pnUserLoggedIn())) {
                $usertheme = pnUserGetVar('theme');
            } else {
                $usertheme = SessionUtil::getVar('theme');
            }
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($usertheme));
            if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $theme = _pnUserGetThemeEvent($themeinfo['name']);
                return $theme;
            }
        }

        // default site theme
        $defaulttheme = pnConfigGetVar('Default_Theme');
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($defaulttheme));
        if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            $theme = _pnUserGetThemeEvent($themeinfo['name']);
            return $theme;
        }

        throw new RuntimeException(__('pnUserGetTheme: unable to calculate theme name.'));
    }

    public static function _getThemeEvent($themeName)
    {
        $event = new Event('user.gettheme', null, array('name' => $themeName));
        EventManagerUtil::notifyUntil($event);
        return $event['name'];
    }

    /**
     * get the user's language
     *
     * @deprecated
     * @see ZLanaguage::getLanguageCode()
     *
     * This function returns the deprecated 3 digit language codes, you need to switch APIs
     *
     * @return string the name of the user's language
     */
    public static function getLang()
    {
        return ZLanguage::getLanguageCodeLegacy();
    }

    /**
     * get a list of user information
     *
     * @public
     * @return array array of user arrays
     */
    public static function getAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = -1, $startnum = -1, $activated = '', $regexpfield = '', $regexpression = '', $where = '')
    {
        $pntable = pnDBGetTables();
        $userscolumn = $pntable['users_column'];

        if (empty($where)) {
            if (!empty($regexpfield) && (array_key_exists($regexpfield, $userscolumn)) && !empty($regexpression)) {
                $where = 'WHERE ' . $userscolumn[$regexpfield] . ' REGEXP "' . DataUtil::formatForStore($regexpression) . '"';
            }
            if (!empty($activated) && is_numeric($activated) && array_key_exists('activated', $userscolumn)) {
                if (!empty($where)) {
                    $where .= ' AND ';
                } else {
                    $where = ' WHERE ';
                }
                $where .= "$userscolumn[activated] != '" . DataUtil::formatForStore($activated) . "'";
            }
        }

        $sortby = '';
        if (!empty($sortbyfield)) {
            if (array_key_exists($sortbyfield, $userscolumn)) {
                $sortby = 'ORDER BY ' . $userscolumn[$sortbyfield] . ' ' . DataUtil::formatForStore($sortorder); //sort by .....
            } else {
                $sortby = 'ORDER BY ' . DataUtil::formatForStore($sortbyfield) . ' ' . DataUtil::formatForStore($sortorder); //sorty by dynamic.....
            }
            if ($sortbyfield != 'uname') {
                $sortby .= ', ' . $userscolumn['uname'] . ' ASC ';
            }
        }

        return DBUtil::selectObjectArray('users', $where, $sortby, $startnum, $limit, 'uid');
    }

    /**
     * Get the uid of a user from the username
     *
     * @param uname $ the username
     * @return mixed userid if found, false if not
     */
    public static function getIdFromName($uname)
    {
        $result = pnUserGetVars($uname, false, 'uname');
        return ($result && isset($result['uid']) ? $result['uid'] : false);
    }

    /**
     * Get the uid of a user from the email (case for unique emails)
     *
     * @param email $ the user email
     * @return mixed userid if found, false if not
     */
    public static function getIdFromEmail($email)
    {
        $result = pnUserGetVars($email);
        return ($result && isset($result['uid']) ? $result['uid'] : false);
    }

    /**
     * Checks the alias and returns if we save the data in the
     * Profile module's user_data table or the users table.
     * This should be removed if we ever go fully dynamic
     *
     * @param label $ the alias of the field to check
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

        $pntables = pnDBGetTables();
        return array_key_exists($label, $pntables['users_column']);
    }
}
