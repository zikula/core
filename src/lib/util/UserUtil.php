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
    // DEVELOPERS: New values added to this
    // 0 = Inactive (Not able to log in.)
    const ACTIVATED_INACTIVE = 0;
    // 1 = Active (Able to log in.)
    const ACTIVATED_ACTIVE = 1;
    // 2 = Inactive until Terms of Use and/or Privacy Policy accepted (Able to start log-in, but must accept TOU/PP to complete.)
    const ACTIVATED_INACTIVE_TOUPP = 2;
    // 4 = Inactive until password changed (Able to start log-in, but must change password to complete.)
    const ACTIVATED_INACTIVE_PWD = 4;
    // 6 = Inactive until Terms of Use and/or Privacy Policy accepted and password changed
    //     (Able to start log-in, but must accept TOU/PP and also must change password to complete.)
    const ACTIVATED_INACTIVE_PWD_TOUPP = 6;

    // Registration verification and pasword generation options
    // 0 = User chooses password, no verification by e-mail.
    const VERIFY_NO = 0;
    // 1 = System-generated password is sent directly to e-mail address
    // NOTE: Use of system-generated passwords is deprecated due to security concerns when sending passwords via e-mail
    const VERIFY_SYSTEMPWD = 1;
    // 2 = User chooses password, then activates account via e-mail
    const VERIFY_USERPWD = 2;

    // Determines the allowed order of approval (moderation) and e-mail verification (activation)
    // 0 = A moderator must approve the registration application, then the user can verify his e-mail.
    const APPROVAL_BEFORE = 0;
    // 1 = The user must verify his e-mail address first, then the moderator can approve the account (but the admin can override this)
    const APPROVAL_AFTER = 1;
    // 2 = Verification and approval can happen in any order
    const APPROVAL_ANY = 2;

    // 1 = Change of password request
    const VERIFYCHGTYPE_PWD = 1;
    // 2 = change of e-mail address request, pending e-mail address verification
    const VERIFYCHGTYPE_EMAIL = 2;

    // Default salt delimeter character
    const SALT_DELIM = '$';

    // Date-time format for use with DateTime#format(), date() and gmdate()
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const EXPIRED = '1901-12-13 20:45:52';

    /**
     * Return a user object.
     *
     * @param integer $uid     The userID of the user to retrieve.
     * @param boolean $getVars Obsolete, we also return the attributes.
     *
     * @deprecated to be removed in 2.0.0
     * @see    self::getVars()
     *
     * @return The resulting user object
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
     * @deprecated to be removed in 2.0.0?
     * @see    self::getVars()
     *
     * @return The requested field
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
     * @return An array mapping uid to username
     */
    public static function getPNUsers($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'uid')
    {
        return DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }

    /**
     * Return a group object.
     *
     * @param integer $gid The groupID to retrieve.
     *
     * @return The resulting group object
     */
    public static function getPNGroup($gid)
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
     * @return An array mapping gid to groupname
     */
    public static function getPNGroups($where = '', $orderBy = '', $limitOffset = -1, $limitNumRows = -1, $assocKey = 'gid')
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
     * Return a (string) list of group-ids which can then be used in a SQL 'IN (...)' clause.
     *
     * @param string $where     The where clause to use (optional).
     * @param string $orderBy   The order by clause to use (optional).
     * @param string $separator The field separator to use (default=",") (optional).
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
     * Return an array group-ids for the specified user.
     *
     * @param integer $uid The user ID for which we want the groups.
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
     * Return a string list of group-ids for the specified user.
     *
     * @param integer $uid       The user ID for which we want the groups.
     * @param string  $separator The field separator to use (default=",") (optional).
     *
     * @return A string list of group ids
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
     * @return an array of user IDs
     */
    public static function getUsersForGroup($gid)
    {
        if (!$gid) {
            return array();
        }

        $where = "WHERE pn_gid = '" . DataUtil::formatForStore($gid) . "'";
        $users = DBUtil::selectFieldArray('group_membership', 'uid', $where);
        return $users;
    }

    /**
     * Return the defined dynamic user data fields.
     *
     * @return an array of dynamic data field definitions
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
     * @deprecated ??
     *
     * @return an array of user IDs
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
     * Return a PN array strcuture for the PN user dropdown box.
     *
     * @param miexed $defaultValue The default value of the selector (optional) (default=0).
     * @param string $defaultText  The text of the default value (optional) (default='').
     * @param array  $ignore       An array of keys to ignore (optional) (default=array()).
     * @param miexed $includeAll   Whether to include an "All" choice (optional) (default=0).
     * @param string $allText      The text to display for the "All" choice (optional) (default='').
     * @param string $exclude      An SQL IN-LIST string to exclude specified uids.
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
        LogUtil::log(__CLASS__ . '::' . __FUNCTION__ . '[' . __LINE__ . '] ' . __f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'UserUtil::loginUsing()')), 'STRICT');
        $authinfo = array(
            'loginid'   => $loginID,
            'pass'      => $userEnteredPassword,
        );
        return self::loginUsing('Users', $authinfo, $rememberme, $checkPassword);
    }

    /**
     * Authenticate a user (check the user's authinfo--user name and password probably) against an authmodule without actually logging the user in.
     *
     * @param string $authModuleName Auth module name.
     * @param array  $authinfo       Auth info array.
     *
     * @return bool True if authinfo authenticates; otherwise false.
     */
    public static function checkPasswordUsing($authModuleName, array $authinfo)
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

        // Authenticate the loginID and userEnteredPassword against the specified authModule.
        // This should return the uid of the user logging in. Note that there are two routes here, both get a uid.
        return self::authApi($authModuleName, 'checkPassword', array('authinfo' => $authinfo));
    }

    /**
     * Check user password without logging in (or logging in again).
     *
     * @param string $authModuleName The name of the authmodule to use for authentication.
     * @param array  $authinfo       The information needed by the authmodule for authentication, typically a loginid and pass.
     *
     * @return mixed Zikula uid if the authinfo authenticates with the authmodule; otherwise false.
     */
    public static function authenticateUserUsing($authModuleName, array $authinfo)
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

        return self::authApi($authModuleName, 'authenticateUser', array('authinfo' => $authinfo));
    }

    /**
     * Login using a specific auth module.
     *
     * @param string  $authModuleName Auth module name.
     * @param array   $authinfo       Auth info array.
     * @param boolean $rememberMe     Whether or not to remember login.
     * @param boolean $checkPassword  Whether or not to check the password.
     *
     * @return boolean
     */
    public function loginUsing($authModuleName, array $authinfo, $rememberMe = false, $checkPassword = true)
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

        // Authenticate the loginID and userEnteredPassword against the specified authModule.
        // This should return the uid of the user logging in. Note that there are two routes here, both get a uid.
        if ($checkPassword) {
            $authenticatedUid = self::authApi($authModuleName, 'authenticateUser', array('authinfo' => $authinfo));
        } else {
            $authenticatedUid = self::authApi($authModuleName, 'getUidForAuthinfo', array('authinfo' => $authinfo));
        }

        if (!$authenticatedUid || !is_numeric($authenticatedUid) || ((int)$authenticatedUid != $authenticatedUid)) {
            // Note that we have not actually logged into anything yet, just authenticated, so no need to
            // call logout on the authmodule.
            $event = new Zikula_Event('user.login.failed', null, array(
                'authmodule'    => $authModuleName,
                'loginid'       => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
            ));
            EventUtil::notify($event);
            return LogUtil::registerError(__('Sorry! Either there is no active user in our system with that information, or the information you provided does not match the information for your account. Please correct your entry and try again.'));
        }

        // At this point we are authenticated, but not logged in. Check for things that need to be done
        // prior to login.

        // Need to make sure the Users module stuff is loaded and available, especially if we are logging in during
        // an upgrade or install.
        ModUtil::dbInfoLoad('Users', 'Users');
        ModUtil::loadApi('Users', 'user', true);

        $userObj = self::getVars($authenticatedUid);
        if (!$userObj || !is_array($userObj)) {
            // Oops! The authmodule gave us a bad uid! Really should not happen unless that module's uid mapping is out of sync.
            // Note that we have not actually logged into anything yet, just authenticated, so no need to
            // call logout on the authmodule.
            $event = new Zikula_Event('user.login.failed', null, array(
                'authmodule'    => $authModuleName,
                'loginid'       => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
            ));
            EventUtil::notify($event);
            return LoginUtil::registerError(__('Sorry! Either there is no active user in our system with that information, or the information you provided does not match the information for your account. Please correct your entry and try again.'));
        }

        if (!isset($userObj['activated'])) {
            // Provide a sane value.
            $userObj['activated'] = self::ACTIVATED_INACTIVE;
        }

        // Check if the account is active
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
                if (ModUtil::available('legal')
                    && (ModUtil::getVar('legal', 'termsofuse', true) || ModUtil::getVar('legal', 'privacypolicy', true))) {

                    if ($mustConfirmTOUPP && $mustChangePassword) {
                        $errorMsg = __('Your log-in request was not completed because you must agree to our terms, and must also change your account\'s password first.');
                    } elseif ($mustConfirmTOUPP) {
                        $errorMsg = __('Your log-in request was not completed because you must agree to our terms first.');
                    }
                    $callbackURL = ModUtil::url('Users','user','loginScreen', array(
                        'authinfo'      => $authinfo,
                        'authmodule'    => $authModuleName,
                        'confirmtou'    => $mustConfirmTOUPP,
                        'changepassword'=> $mustChangePasswordRightNow,
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
                $callbackURL = ModUtil::url('Users','user','loginScreen', array(
                    'authinfo'      => $authinfo,
                    'authmodule'    => $authModuleName,
                    'confirmtou'    => $mustConfirmTOUPP,
                    'changepassword'=> $mustChangePasswordRightNow,
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
                // Note that we have not actually logged into anything yet, just authenticated, so no need to
                // call logout on the authmodule.
                $event = new Zikula_Event('user.login.failed', null, array(
                    'authmodule'    => $authModuleName,
                    'loginid'       => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                ));
                EventUtil::notify($event);

                $loginDisplayInactive = ModUtil::getVar('Users', 'login_displayinactive', false);
                $loginDisplayVerify = ModUtil::getVar('Users', 'login_displayverify', false);
                if ($loginDisplayVerify && (!isset($userObj['lastlogin']) || empty($userObj['lastlogin']) || ($userObj['lastlogin'] == '1970-01-01 00:00:00'))) {
                    return  LoginUtil::registerError(__('Sorry! Your account pending activation. Please check your e-mail for an activation message or contact an administrator.'));
                } elseif ($loginDisplayInactive) {
                    return  LoginUtil::registerError(__('Sorry! Your account is not active. Please contact an administrator.'));
                } else {
                    return LoginUtil::registerError(__('Sorry! Either there is no active user in our system with that information, or the information you provided does not match the information for your account. Please correct your entry and try again.'));
                }
            }
        }

        // BEGIN ACTUAL LOGIN
        // Made it through all the checks. We can actually log in now.
        $loggedInUid = self::authApi($authModuleName, 'login', array('authinfo' => $authinfo));
        if ($loggedInUid == $authenticatedUid) {
            // Storing Last Login date -- store it in UTC! Do not use date() function!
            $nowUTC = new DateTime(null, new DateTimeZone('UTC'));
            if (!self::setVar('lastlogin', $nowUTC->format('Y-m-d H:i:s'), $loggedInUid)) {
                // show messages but continue
                LogUtil::registerError(__('Error! Could not save the log-in date.'));
            }

            if (!System::isInstalling()) {
                SessionUtil::requireSession();
            }

            // Set session variables -- this is what really does the Zikula login
            SessionUtil::setVar('uid', (int)$loggedInUid);

            // Remember the authenticating authmodule for logout
            SessionUtil::setVar('authmodule', $authModuleName);

            if (!empty($rememberMe)) {
                SessionUtil::setVar('rememberme', 1);
            }

            // now that we've logged in the permissions previously calculated (if any) are invalid
            $GLOBALS['authinfogathered'][$loggedInUid] = 0;

            $event = new Zikula_Event('user.login', null, array(
                'authmodule'    => $authModuleName,
                'loginid'       => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
                'user'          => $loggedInUid,
            ));
            EventUtil::notify($event);

            return true;
        } else {
            // Really should never get here. We authenticated earlier with the same set of authinfo, but
            // if we got here then the uid returned by login was different than the one returned by
            // authenticateUser. Strange situation, so deny login.
            // Note that we have not actually logged into anything, so no need to call logout on the authmodule.
            $event = new Zikula_Event('user.login.failed', null, array(
                'authmodule'    => $authModuleName,
                'loginid'       => isset($authinfo['loginid']) ? $authinfo['loginid'] : '',
            ));
            EventUtil::notify($event);
            return false;
        }
    }

    /**
     * Log the user in via the REMOTE_USER SERVER property.
     *
     * This routine simply
     * checks if the REMOTE_USER exists in the PN environment: if he does a
     * session is created for him, regardless of the password being used.
     *
     * @return bool true if the user successfully logged in, false otherwise
     */
    public static function loginHttp()
    {
        $uname = System::serverGetVar('REMOTE_USER');
        $hSec  = System::getVar('session_http_login_high_security', true);
        $rc    = self::loginUsing('Users', array('loginid' => $uname, 'pass' => null), false, false);
        if ($rc && $hSec) {
            System::setVar('seclevel', 'High');
        }

        return $rc;
    }

    /**
     * Log the user out.
     *
     * @return bool true if the user successfully logged out, false otherwise
     */
    public static function logout()
    {
        if (self::isLoggedIn()) {
            $authModuleName = SessionUtil::getVar('authmodule', '');

            if (!empty($authModuleName) && ModUtil::available($authModuleName) && ModUtil::loadApi($authModuleName, 'auth')) {
                $authModuleLoggedOut = self::authApi($authModuleName, 'logout');
                if (!$authModuleLoggedOut) {
                    // TODO -- Really? We want to prevent the user from logging out of Zikula if the authmodule logout fails?  Really?
                    $event = new Zikula_Event('user.logout.failed', null, array(
                        'authmodule'    => $authModuleName,
                        'user'          => self::getVar('uid'),
                    ));
                    EventUtil::notify($event);
                    return false;
                }
            }

            $event = new Zikula_Event('user.logout', null, array(
                'authmodule'    => $authModuleName,
                'user'          => self::getVar('uid'),
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
     * Get all user variables, maps new style attributes to old style user data.
     *
     * @param integer $id      The user id of the user.
     * @param boolean $force   True to force loading from database and ignore the cache.
     * @param string  $idfield Field to use as id (possible values: uid, uname or email).
     *
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
        // NOTE: Do not use a permission filter, or you will enter an infinite nesting loop where getVars calls checkPermission (from within
        // DBUtil), which will call getVars to find out who you are, which will call checkPermission, etc., etc.
        // Do your permission check in the API that is using UserUtil.
        $user = DBUtil::selectObjectByID('users', $id, $idfield, null, null, null, false);

        // If $idfield is email, make sure that we are getting a unique record.
        if ($user && ($idfield == 'email')) {
            $dbTables = System::dbGetTables();
            $usersColumn = $dbTables['users_column'];
            $where = "WHERE {$usersColumn['email']} = '{$id}'";
            $ucount = DBUtil::selectObjectCount('users', $where);

            if ($ucount > 1) {
                $user = false;
            }
        }

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
     * Get a user variable.
     *
     * @param string  $name    The name of the variable.
     * @param integer $uid     The user to get the variable for.
     * @param mixed   $default The default value to return if the specified variable doesn't exist.
     *
     * @return mixed the value of the user variable if successful, null otherwise
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
        $vars = self::getVars($uid);

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
        if (self::fieldAlias($name)) {
            // this value comes from the users table
            $obj = array('uid' => $uid, $name => $value);
            $res = (bool)DBUtil::updateObject($obj, 'users', '', 'uid');
        } else if (array_key_exists($name, $mappingarray)) {
            LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), 'STRICT');
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
        self::getVars($uid, true);
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
                'md5'    => 1,
                'sha1'   => 5,
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

        if (!isset($hashAlgorithmName) || !is_string($hashAlgorithmName) || empty($hashAlgorithmName)
            || !isset($hashMethodCodesByName[$hashAlgorithmName]) || empty($hashMethodCodesByName[$hashAlgorithmName])
            || !is_numeric($hashMethodCodesByName[$hashAlgorithmName])) {

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
            'hashMethodCode'    => $hashMethodCode,
            'hash'              => hash($hashAlgorithmName, $unhashedPassword),
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

        if (self::fieldAlias($name)) {
            // this value comes from the users table
            $obj = array('uid' => $uid, $name => '');
            return (bool)DBUtil::updateObject($obj, 'users', '', 'uid');
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
        self::getVars($uid, true);
        return (bool)$res;
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
            if ($themeinfo['state'] == ThemeUtil::STATE_ACTIVE && ($themeinfo['user'] || $themeinfo['system'] || ($themeinfo['admin'] && ($type == 'admin' || stristr($qstring, 'admin.php')))) && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $theme = self::_themeEvent($themeinfo['name']);
                return $theme;
            }
        }

        // check for an admin theme
        if (($type == 'admin' || stristr($qstring, 'admin.php')) && SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            $admintheme = ModUtil::getVar('Admin', 'admintheme');
            if (!empty($admintheme)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($admintheme));
                if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                    $theme = self::_themeEvent($themeinfo['name']);
                    return $theme;
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
                $theme = self::_themeEvent($themeinfo['name']);
                return $theme;
            }
        }

        // User theme
        if (System::getVar('theme_change')) {
            if ((self::isLoggedIn())) {
                $usertheme = self::getVar('theme');
            } else {
                $usertheme = SessionUtil::getVar('theme');
            }
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($usertheme));
            if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $theme = self::_themeEvent($themeinfo['name']);
                return $theme;
            }
        }

        // default site theme
        $defaulttheme = System::getVar('Default_Theme');
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($defaulttheme));
        if ($themeinfo && $themeinfo['state'] == ThemeUtil::STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            $theme = self::_themeEvent($themeinfo['name']);
            return $theme;
        }

        if (!System::isInstalling()) {
            throw new RuntimeException(__('UserUtil::getTheme: unable to calculate theme name.'));
        }
    }

    /**
     * Call theme event.
     *
     * @param string $themeName Theme name.
     *
     * @return string Event name.
     */
    private static function _themeEvent($themeName)
    {
        $event = new Zikula_Event('user.gettheme', null, array('name' => $themeName));
        EventUtil::notifyUntil($event);
        return $event['name'];
    }

    /**
     * Get the user's language.
     *
     * @deprecated
     * @see    ZLanaguage::getLanguageCode()
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
                $sqlFragments[] = DataUtil::formatForStore($sortbyfield) . ' ' . DataUtil::formatForStore($sortorder); //sorty by dynamic.....
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
     * @param string $uname The username.
     *
     * @return mixed userid if found, false if not
     */
    public static function getIdFromName($uname)
    {
        $result = self::getVars($uname, false, 'uname');
        return ($result && isset($result['uid']) ? $result['uid'] : false);
    }

    /**
     * Get the uid of a user from the email (case for unique emails).
     *
     * @param string $email The user email.
     *
     * @return mixed userid if found, false if not
     */
    public static function getIdFromEmail($email)
    {
        $result = self::getVars($email);
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
     * Call authmodle's auth api.
     *
     * @param string $authModuleName The name of the module.
     * @param string $method         The specific function to run.
     * @param array  $args           The arguments to pass to the function.
     *
     * @return mixed
     * @throws Exception If the $authModuleName's api does not subclass Zikula_AuthApi.
     */
    public static function authApi($authModuleName, $method, $args)
    {
        $object = ModUtil::getObject(ModUtil::getClass($authModuleName, 'auth', true));
        if (!$object->getReflection()->isSubClassOf('Zikula_AuthApi')) {
            throw new Exception(__f('%s must be a subclass of Zikula_AuthApi', $className));
        }

        return ModUtil::apiFunc($authModuleName, 'auth', $method, $args);
    }

}
