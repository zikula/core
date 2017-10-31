<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * UserUtil
 * @deprecated
 */
class UserUtil
{
    /**
     * Cache for groups.
     *
     * @var array
     */
    protected static $groupCache = [];

    /**
     * Clear group cache.
     *
     * @return void
     */
    public function clearGroupCache()
    {
        self::$groupCache = [];
    }

    /**
     * Return a hash structure mapping uid to username.
     *
     * @param array   $where        Array of field values to filter by (optional, default=[])
     * @param array   $orderBy      Array fields to sort by (optional, default=[])
     * @param integer $limitOffset  The select-limit offset (optional, default=null)
     * @param integer $limitNumRows The number of rows to fetch (optional, default=null)
     * @param string  $assocKey     The associative key to apply (optional) (default='uid')
     *
     * @deprecated since 1.3.0
     *
     * @return array An array mapping uid to username
     */
    public static function getUsers($where = [], $orderBy = [], $limitOffset = null, $limitNumRows = null, $assocKey = 'uid')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        // first check for string based parameters and use dbutil if found
        if (System::isLegacyMode() && (is_string($where) || is_string($orderBy))) {
            if ($where == []) {
                $where = '';
            }
            if ($orderBy == []) {
                $orderBy = '';
            }

            return DBUtil::selectObjectArray('users', $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
        }

        // we've now ruled out BC parameters
        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
        $users = $em->getRepository('ZikulaUsersModule:UserEntity')->findBy($where, $orderBy, $limitNumRows, $limitOffset);

        $items = [];
        foreach ($users as $user) {
            $items[$user[$assocKey]] = $user->toArray();
        }

        return $items;
    }

    /**
     * Return a group object.
     *
     * @param integer $gid The groupID to retrieve
     *
     * @todo   Decouple UserUtil and Groups?
     *
     * @return array The resulting group object
     */
    public static function getGroup($gid)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        return ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
    }

    /**
     * Return a hash structure mapping gid to groupname.
     *
     * @param string  $where        The where clause to use (optional) (default=[])
     * @param string  $orderBy      The order by clause to use (optional) (default=[])
     * @param integer $limitOffset  The select-limit offset (optional) (default=null)
     * @param integer $limitNumRows The number of rows to fetch (optional) (default=null)
     * @param string  $assocKey     The associative key to apply (optional) (default='gid')
     *
     * @return array An array mapping gid to groupname
     */
    public static function getGroups($where = [], array $orderBy = [], $limitOffset = null, $limitNumRows = null, $assocKey = 'gid')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
        $groups = $em->getRepository('ZikulaGroupsModule:GroupEntity')->findBy($where, $orderBy, $limitNumRows, $limitOffset);

        $items = [];
        foreach ($groups as $group) {
            $items[$group[$assocKey]] = $group->toArray();
        }

        return $items;
    }

    /**
     * Return a (string) list of user-ids which can then be used in a SQL 'IN (...)' clause.
     *
     * @param string $where     The where clause to use (optional)
     * @param string $orderBy   The order by clause to use (optional)
     * @param string $separator The field separator to use (default=",") (optional)
     *
     * @return string A string list of user ids
     */
    public static function getUserIdList($where = '', $orderBy = '', $separator = ',')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

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
     * @param string $where     The where clause to use (optional) (default=[])
     * @param string $orderBy   The order by clause to use (optional) (default=[])
     * @param string $separator The field separator to use (default=",") (optional)
     *
     * @return string A string list of group ids
     */
    public static function getGroupIdList($where = [], array $orderBy = [], $separator = ',')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

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
     * Return an array of group-ids for the specified user.
     *
     * @param integer $uid The user ID for which we want the groups
     *
     * @return array An array of group IDs
     */
    public static function getGroupsForUser($uid)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        if (empty($uid)) {
            return [];
        }

        return ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getusergroups', ['uid' => $uid, 'clean' => true]);
    }

    /**
     * Return a string list of group-ids for the specified user.
     *
     * @param integer $uid       The user ID for which we want the groups
     * @param string  $separator The field separator to use (default=",") (optional)
     *
     * @return string A string list of group ids
     */
    public static function getGroupListForUser($uid = null, $separator = ',')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

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
     * @param integer $gid The group ID for which we want the users
     *
     * @return array An array of user IDs
     */
    public static function getUsersForGroup($gid)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        if (!$gid) {
            return [];
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);

        $members = $group['members'];

        $uids = [];

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
     * @param integer $uid User ID to get the group memberships from. Default: current user
     *
     * @return string Cache GIDs string to use on Zikula_View
     */
    public static function getGidCacheString($uid = null)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $str = self::getGroupListForUser($uid, '_');

        return '-1' == $str ? 'guest' : 'groups_'.$str;
    }

    /**
     * Get a unique string for a user, based on the uid.
     *
     * String ready to be used as part of the CacheID of the output views.
     * Useful for user-based access privilegies.
     *
     * @param integer $uid User ID to get string from. Default: current user
     *
     * @return string Cache UID string to use on Zikula_View
     */
    public static function getUidCacheString($uid = null)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $uid = $uid ? (int)$uid : self::getVar('uid');

        return !$uid ? 'guest' : 'uid_'.$uid;
    }

    /**
     * Return the defined dynamic user data fields.
     *
     * @return array An array of dynamic data field definitions
     */
    public static function getDynamicDataFields()
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        // decide if we have to use the (obsolete) DUDs from the Profile module
        $profileModule = System::getVar('profilemodule', '');

        if (empty($profileModule) || 'Profile' != $profileModule || !ModUtil::available($profileModule)) {
            return [];
        }

        return DBUtil::selectObjectArray('user_property');
    }

    /**
     * Return a array structure for the user group selector.
     *
     * @param mixed  $defaultValue The default value of the selector (default=0) (optional)
     * @param string $defaultText  The text of the default value (optional)
     * @param array  $ignore       An array of keys to ignore (optional)
     * @param mixed  $includeAll   Whether to include an "All" choice (optional)
     * @param string $allText      The text to display for the "All" choice (optional)
     *
     * @return array The array structure for the user group selector
     */
    public static function getSelectorData_Group($defaultValue = 0, $defaultText = '', $ignore = [], $includeAll = 0, $allText = '')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $dropdown = [];

        if ($defaultText) {
            $dropdown[] = [
                'id' => $defaultValue,
                'name' => $defaultText
            ];
        }

        $groupdata = self::getGroups([], ['name' => 'ASC']);

        if (!$groupdata || !count($groupdata)) {
            return $dropdown;
        }

        if ($includeAll) {
            $dropdown[] = [
                'id' => $includeAll,
                'name' => $allText
            ];
        }

        foreach (array_keys($groupdata) as $gid) {
            if (!isset($ignore[$gid])) {
                $gname = $groupdata[$gid]['name'];
                $dropdown[$gname] = [
                    'id' => $gid,
                    'name' => $gname
                ];
            }
        }

        ksort($dropdown);

        return $dropdown;
    }

    /**
     * Return a array strcuture for the user dropdown box.
     *
     * @param mixed  $defaultValue The default value of the selector (optional) (default=0)
     * @param string $defaultText  The text of the default value (optional) (default='')
     * @param array  $ignore       An array of keys to ignore (optional) (default=[])
     * @param mixed  $includeAll   Whether to include an "All" choice (optional) (default=0)
     * @param string $allText      The text to display for the "All" choice (optional) (default='')
     * @param string $exclude      An SQL IN-LIST string to exclude specified uids
     *
     * @return array The array structure for the user group selector
     */
    public static function getSelectorData_User($defaultValue = 0, $defaultText = '', array $ignore = [], $includeAll = 0, $allText = '', $exclude = '')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $dropdown = [];

        if ($defaultText) {
            $dropdown[] = [
                'id' => $defaultValue,
                'name' => $defaultText
            ];
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
            $dropdown[] = [
                'id' => $includeAll,
                'name' => $allText
            ];
        }

        foreach (array_keys($userdata) as $uid) {
            if (!isset($ignore[$uid])) {
                $uname = $userdata[$uid]['uname'];
                $dropdown[$uname] = [
                    'id' => $uid,
                    'name' => $uname
                ];
            }
        }

        ksort($uname);

        return $dropdown;
    }

    /**
     * Retrieve the account recovery information for a user from the various authentication modules.
     * @deprecated no longer functional
     */
    public static function getUserAccountRecoveryInfo($uid = -1)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);
    }

    /**
     * Log the user out.
     *
     * @return bool true if the user successfully logged out, false otherwise
     */
    public static function logout()
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        if (self::isLoggedIn()) {
            $userObj = self::getVars(self::getVar('uid'));
            $authenticationMethod = SessionUtil::delVar('authentication_method', ['modname' => '', 'method' => ''], 'Zikula_Users');

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
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('session')->isStarted() && (bool)SessionUtil::getVar('uid');
    }

    /**
     * Counts how many times a user name has been used by user accounts in the system.
     *
     * @param string $uname      The e-mail address in question (required)
     * @param int    $excludeUid The uid to exclude from the check, used when checking modifications
     *
     * @return integer|boolean The count, or false on error
     */
    public static function getUnameUsageCount($uname, $excludeUid = 0)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        if (!is_numeric($excludeUid) || ((int)$excludeUid != $excludeUid)) {
            return false;
        }

        $uname = mb_strtolower($uname);

        // get doctrine manager
        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        // count of uname appearances in users table
        $qb = $em->createQueryBuilder()
                 ->select('COUNT(u.uid)')
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
     * @param string $emailAddress The e-mail address in question (required)
     * @param int    $excludeUid   The uid to exclude from the check, used when checking modifications
     *
     * @return integer|boolean the count, or false on error
     */
    public static function getEmailUsageCount($emailAddress, $excludeUid = 0)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        if (!is_numeric($excludeUid) || ((int)$excludeUid != $excludeUid)) {
            return false;
        }

        $emailAddress = mb_strtolower($emailAddress);

        // get doctrine manager
        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        // count of email appearances in users table
        $qb = $em->createQueryBuilder()
                 ->select('COUNT(u.uid)')
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
                 ->select('COUNT(v.uid)')
                 ->from('ZikulaZAuthModule:UserVerificationEntity', 'v')
                 ->where('v.newemail = :email')
                 ->andWhere('v.changetype = :chgtype')
                 ->setParameter('email', $emailAddress)
                 ->setParameter('chgtype', ZAuthConstant::VERIFYCHGTYPE_EMAIL);

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
     *                          reference, and therefore will be updated by the actions of this function
     *
     * @return array The updated $userObj
     */
    public static function postProcessGetRegistration(&$userObj)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        if (UsersConstant::ACTIVATED_PENDING_REG == $userObj['activated']) {
            // Get isverified from the attributes.
            if (isset($userObj['__ATTRIBUTES__']['_Users_isVerified'])) {
                $userObj['isverified'] = $userObj['__ATTRIBUTES__']['_Users_isVerified'];
                // todo why was this unset statement commented out in 2.0?
                unset($userObj['__ATTRIBUTES__']['_Users_isVerified']);
            } else {
                $userObj['isverified'] = false;
            }

            // Get verificationsent from the users_verifychg table
            $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');

            $query = $em->createQueryBuilder()
                        ->select('v')
                        ->from('ZikulaZAuthModule:UserVerificationEntity', 'v')
                        ->where('v.uid = :uid')
                        ->andWhere('v.changetype = :changetype')
                        ->setParameter('uid', $userObj['uid'])
                        ->setParameter('changetype', ZAuthConstant::VERIFYCHGTYPE_REGEMAIL)
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
     * @param integer $id              The user id of the user (required)
     * @param boolean $force           True to force loading from database and ignore the cache
     * @param string  $idfield         Field to use as id (possible values: uid, uname or email)
     * @param bool    $getRegistration Indicates whether a "regular" user record or a pending registration
     *                                      is to be returned. False (default) for a user record and true
     *                                      for a registration. If false and the user record is a pending
     *                                      registration, then the record is not returned and false is returned
     *                                      instead; likewise, if true and the user record is not a registration,
     *                                      then false is returned; (Defaults to false)
     *
     * @return array|bool An associative array with all variables for a user (or pending registration);
     *                      false on error
     */
    public static function getVars($id, $force = false, $idfield = '', $getRegistration = false)
    {
        @trigger_error('UserUtil is deprecated, please use CurrentUserApi instead.', E_USER_DEPRECATED);

        if (empty($id)) {
            return false;
        }

        // assign a value for the parameter idfield if it is necessary and prevent from possible typing mistakes
        if ('' == $idfield || ('uid' != $idfield && 'uname' != $idfield && 'email' != $idfield)) {
            $idfield = 'uid';
            if (!is_numeric($id)) {
                $idfield = 'uname';
                if (strpos($id, '@')) {
                    $idfield = 'email';
                }
            }
        }

        static $cache = [], $unames = [], $emails = [];

        // caching
        $user = null;
        if (false == $force) {
            if ('uname' == $idfield && isset($unames[$id])) {
                if (false !== $unames[$id]) {
                    $user = $cache[$unames[$id]];
                } else {
                    return false;
                }
            }

            if ('email' == $idfield && isset($emails[$id])) {
                if (false !== $emails[$id]) {
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
            $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
            $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy([$idfield => $id]);

            if ($user) {
                $user = $user->toArray();

                $attributes = [];
                foreach ($user['attributes'] as $attribute) {
                    $attributes[$attribute['name']] = $attribute['value'];
                }

                $user['__ATTRIBUTES__'] = $attributes;
                unset($user['attributes']);
            }

            // If $idfield is email, make sure that we are getting a unique record.
            if ($user && ('email' == $idfield)) {
                $emailCount = self::getEmailUsageCount($id);
                if (($emailCount > 1) || (false === $emailCount)) {
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
                if (($getRegistration && (UsersConstant::ACTIVATED_PENDING_REG != $user['activated']))
                        || (!$getRegistration && (UsersConstant::ACTIVATED_PENDING_REG == $user['activated']))) {
                    return false;
                }

                $user = self::postProcessGetRegistration($user);

                $cache[$user['uid']] = $user;
                $unames[$user['uname']] = $user['uid'];
                $emails[$user['email']] = $user['uid'];
            }
        } elseif (($getRegistration && (UsersConstant::ACTIVATED_PENDING_REG != $user['activated']))
                || (!$getRegistration && (UsersConstant::ACTIVATED_PENDING_REG == $user['activated']))) {
            return false;
        }

        return $user;
    }

    /**
     * Get a user variable.
     *
     * @param string  $name            The name of the variable
     * @param integer $uid             The user to get the variable for
     * @param mixed   $default         The default value to return if the specified variable doesn't exist
     * @param bool    $getRegistration Indicates whether the variable should be retrieved from a "regular"
     *                                      user record or from a pending registration. False (default) for a
     *                                      user record and true for a registration. If false and the uid refers
     *                                      to a pending registration, then the variable is not returned and
     *                                      null is returned instead; likewise, if true and the user record is
     *                                      not a registration, then null is returned. (Defaults to false)
     *
     * @return mixed The value of the user variable if successful, null otherwise
     */
    public static function getVar($name, $uid = -1, $default = false, $getRegistration = false)
    {
        @trigger_error('UserUtil is deprecated, please use CurrentUserApi instead.', E_USER_DEPRECATED);

        if (empty($name)) {
            return null;
        }

        // bug fix #1311 [landseer]
        if (isset($uid) && !is_numeric($uid)) {
            return null;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid', null);
        }
        if (empty($uid)) {
            return null;
        }

        // get this user's variables
        $vars = self::getVars($uid, false, '', $getRegistration);

        if (false === $vars) {
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
     * @param string $name The name of the field
     *
     * @return string|bool The attribute name corresponding to the DUD name, or false if the parameter is not a DUD name
     */
    private static function convertOldDynamicUserDataAlias($name)
    {
        @trigger_error('UserUtil is deprecated.', E_USER_DEPRECATED);

        $attributeName = false;

        if (isset($name) && !empty($name)) {
            // Only need to build the array once
            static $mappingArray;

            if (!isset($mappingArray)) {
                // this array maps old DUDs to new attributes
                $mappingArray = [
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
                ];
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
     * @param string  $name  The name of the variable
     * @param mixed   $value The value of the variable
     * @param integer $uid   The user to set the variable for
     *
     * @return bool true if the set was successful, false otherwise
     */
    public static function setVar($name, $value, $uid = -1)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

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
        if (('uid' != $name) && ('uname' != $name)) {
            // get user given a uid
            $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
            $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy(['uid' => $uid]);

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
                    LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', [$dudAttributeName, $name]), E_USER_DEPRECATED);
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
            if ($varIsSet && ('pass' != $name) && \ServiceUtil::getManager()->getParameter('installed')) {
                // Fire the event
                $eventName = $isRegistration ? 'user.registration.update' : 'user.account.update';
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => isset($attributeName) ? null : $name,
                    'attribute' => isset($attributeName) ? $attributeName : null,
                ];
                $eventData = [
                    'old_value' => $oldValue,
                    'new_value' => $value,
                ];

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
     *                          optional, default = false
     *
     * @return array Depending on the value of $reverse, an array of codes indexed by name or an
     *                  array of names indexed by code
     */
    public static function getPasswordHashMethods($reverse = false)
    {
        @trigger_error('UserUtil is deprecated, please use PasswordApi instead.', E_USER_DEPRECATED);

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
            return [
                1 => 'md5',
                5 => 'sha1',
                8 => 'sha256'
            ];
        }

        // Ensure this is in sync with the array above!
        return [
            'md5' => 1,
            'sha1' => 5,
            'sha256' => 8
        ];
    }

    /**
     * For a given password hash algorithm name, return its internal integer code.
     *
     * @param string $hashAlgorithmName The name of a hash algorithm suitable for hashing user passwords
     *
     * @return integer|bool The internal integer code corresponding to the given algorithm name; false if the name is not valid
     */
    public static function getPasswordHashMethodCode($hashAlgorithmName)
    {
        @trigger_error('UserUtil is deprecated, please use PasswordApi instead.', E_USER_DEPRECATED);

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
     * @param int $hashAlgorithmCode The internal code representing a hashing algorithm suitable for hashing user passwords
     *
     * @return string|bool The hashing algorithm name corresponding to that code, suitable for use with hash(); false if the code is invalid
     */
    public static function getPasswordHashMethodName($hashAlgorithmCode)
    {
        @trigger_error('UserUtil is deprecated, please use PasswordApi instead.', E_USER_DEPRECATED);

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
     * Given a string return it's hash and the internal integer hashing algorithm code used to hash that string.
     *
     * Note that this can be used for more than just user login passwords. If a user-readale password-like code is needed,
     * then this method may be suitable.
     *
     * @param string $unhashedPassword An unhashed password, as might be entered by a user or generated by the system, that meets
     *                                  all of the constraints of a valid password for a user account
     * @param int $hashMethodCode An internal code identifying one of the valid user password hashing methods; optional, leave this
     *                                  unset (null) when creating a new password for a user to get the currently configured system
     *                                  hashing method, otherwise to hash a password for comparison, specify the method used to hash
     *                                  the original password
     *
     * @return array|bool An array containing two elements: 'hash' containing the hashed password, and 'hashMethodCode' containing the
     *                      internal integer hashing algorithm code used to hash the password; false if the password does not meet the
     *                      constraints of a valid password, or if the hashing method (stored in the Users module 'hash_method' var) is
     *                      not valid
     */
    public static function getHashedPassword($unhashedPassword, $hashMethodCode = null)
    {
        @trigger_error('UserUtil is deprecated, please use PasswordApi instead.', E_USER_DEPRECATED);

        if (isset($hashMethodCode)) {
            if (!is_numeric($hashMethodCode) || ((int)$hashMethodCode != $hashMethodCode)) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
            $hashAlgorithmName = self::getPasswordHashMethodName($hashMethodCode);
            if (!$hashAlgorithmName) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
        } else {
            $hashAlgorithmName = ModUtil::getVar('ZikulaZAuthModule', ZAuthConstant::MODVAR_HASH_METHOD, ZAuthConstant::DEFAULT_HASH_METHOD);
            $hashMethodCode = self::getPasswordHashMethodCode($hashAlgorithmName);
            if (!$hashMethodCode) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            }
        }

        return SecurityUtil::getSaltedHash($unhashedPassword, $hashAlgorithmName, self::getPasswordHashMethods(false), 5, UsersConstant::SALT_DELIM);

        // FIXME this return is not reached
        return [
            'hashMethodCode' => $hashMethodCode,
            'hash' => hash($hashAlgorithmName, $unhashedPassword),
        ];
    }

    /**
     * Create a system-generated password or password-like code, meeting the configured constraints for a password.
     *
     * @return string The generated (unhashed) password-like string
     */
    public static function generatePassword()
    {
        @trigger_error('UserUtil is deprecated, please use PasswordApi instead.', E_USER_DEPRECATED);

        $minLength = ModUtil::getVar('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH);
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
     * @param string $unhashedPassword The new password for the current user
     * @param int    $uid              The user ID of the user for whom the password should be set; optional; defaults to current user
     *
     * @return bool True if the password was successfully saved; otherwise false if the password is empty,
     *                  invalid (too short), or if the password was not successfully saved
     */
    public static function setPassword($unhashedPassword, $uid = -1)
    {
        @trigger_error('UserUtil is deprecated, please update the password via the entity instead.', E_USER_DEPRECATED);
    }

    /**
     * Compare a password-like code to a hashed value, to determine if they match.
     *
     * Note that this is not limited only to use for user login passwords, but can be used where ever a human-readable
     * password-like code is needed.
     *
     * @param string $unhashedPassword The password-like code entered by the user
     * @param string $hashedPassword   The hashed password-like code that the entered password-like code is to be compared to
     *
     * @return bool True if the $unhashedPassword matches the $hashedPassword with the given hashing method; false if they do not
     *                  match, or if there was an error (such as an empty password or invalid code)
     */
    public static function passwordsMatch($unhashedPassword, $hashedPassword)
    {
        @trigger_error('UserUtil is deprecated, please use PasswordApi instead.', E_USER_DEPRECATED);

        $passwordsMatch = false;

        if (!isset($unhashedPassword) || !is_string($unhashedPassword) || empty($unhashedPassword)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($hashedPassword) || !is_string($hashedPassword) || empty($hashedPassword) || (false === strpos($hashedPassword, UsersConstant::SALT_DELIM))) {
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
     * @param string  $name The name of the variable
     * @param integer $uid  The user to delete the variable for
     *
     * @return boolean true on success, false on failure
     */
    public static function delVar($name, $uid = -1)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        // Prevent deletion of core fields (duh)
        if (empty($name) || ('uid' == $name) || ('email' == $name) || ('pass' == $name) || ('uname' == $name)
                || ('activated' == $name)) {
            return false;
        }

        if ($uid == -1) {
            $uid = SessionUtil::getVar('uid');
        }

        if (empty($uid)) {
            return false;
        }

        // Special delete value for approved_by
        if ('approved_by' == $name) {
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
        if (('uid' != $name) && ('uname' != $name)) {
            // get user given a uid
            $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
            $user = $em->getRepository('ZikulaUsersModule:UserEntity')->findOneBy(['uid' => $uid]);

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
                    LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', [$dudAttributeName, $name]), E_USER_DEPRECATED);
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
            if ($varIsDeleted && ('pass' != $name) && \ServiceUtil::getManager()->getParameter('installed')) {
                // Fire the event
                $eventArgs = [
                    'action'    => 'delVar',
                    'field'     => isset($attributeName) ? null : $name,
                    'attribute' => isset($attributeName) ? $attributeName : null,
                ];
                $eventData = [
                    'old_value' => $oldValue,
                ];
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
     * @param boolean $force True to ignore the cache
     *
     * @return string           the name of the user's theme
     * @throws RuntimeException If this function was unable to calculate theme name
     */
    public static function getTheme($force = false)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        // if this method is called from the command line scope, always return a default core theme (ZikulaAndreas08Theme)
        // this prevents calls for the Request object or other unwanted behaviors.
        if ('cli' == php_sapi_name()) {
            return 'ZikulaAndreas08Theme';
        }

        static $pagetheme;

        if (isset($pagetheme) && !$force) {
            return $pagetheme;
        }

        /** @var $request Request */
        $request = \ServiceUtil::get('request');

        $theme = FormUtil::getPassedValue('theme', null, 'GETPOST');
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($theme));
        if (!empty($theme) && (
                SecurityUtil::checkPermission('ZikulaThemeModule::ThemeChange', ':' . $themeinfo['name'] . ':', ACCESS_COMMENT) ||
                SecurityUtil::checkPermission('ZikulaThemeModule::ThemeChange', ':' . $themeinfo['displayname'] . ':', ACCESS_COMMENT))
        ) {
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
        if (!empty($pagetheme)) {
            if (ThemeUtil::STATE_ACTIVE == $themeinfo['state']
                && ($themeinfo['user']
                    || $themeinfo['system']
                    || ($themeinfo['admin'] && ('admin' == $type)))
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
        $adminSections = ['admin', 'adminplugin'];
        if (in_array($type, $adminSections) && SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            $admintheme = ModUtil::getVar('ZikulaAdminModule', 'admintheme');
            if (!empty($admintheme)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($admintheme));
                if ($themeinfo
                    && ThemeUtil::STATE_ACTIVE == $themeinfo['state']
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
                && ThemeUtil::STATE_ACTIVE == $themeinfo['state']
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
                && ThemeUtil::STATE_ACTIVE == $themeinfo['state']
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
            && ThemeUtil::STATE_ACTIVE == $themeinfo['state']
            && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            $pagetheme = $themeinfo['name'];

            $themeName = self::_getThemeFilterEvent($themeinfo['name'], 'default-theme');
            $request->attributes->set('_theme', $themeName);

            return $themeName;
        } else {
            // on upgrade, if a user doesn't add their custom theme back to the /theme dir, it should be reset to a core
            // theme, if available.
            if (ServiceUtil::get('kernel')->isBundle('ZikulaBootstrapTheme')) {
                System::setVar('Default_Theme', 'ZikulaBootstrapTheme');

                return 'ZikulaBootstrapTheme';
            }
        }

        if (\ServiceUtil::getManager()->getParameter('installed')) {
            throw new RuntimeException(__('UserUtil::getTheme() is unable to calculate theme name.'));
        }
    }

    /**
     * Filter results for a given getTheme() type.
     *
     * @param string $themeName Theme name
     * @param string $type      Event type
     *
     * @return string Theme name
     */
    private static function _getThemeFilterEvent($themeName, $type)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $event = new GenericEvent(null, ['type' => $type], $themeName);

        return EventUtil::dispatch('user.gettheme', $event)->getData();
    }

    /**
     * Get a list of user information.
     *
     * @param string  $sortbyfield   Sort by field
     * @param string  $sortorder     Sort by order
     * @param integer $limit         Select limit
     * @param integer $startnum      Select offset
     * @param string  $activated     Activated value
     * @param string  $regexpfield   Field for regexfilter
     * @param string  $regexpression Regex expression
     * @param string  $where         Where clause
     *
     * @return array Array of users
     */
    public static function getAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = null, $offset = null, $activated = '', $field = '', $expression = '', $where = '')
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $user = new \Zikula\UsersModule\Entity\UserEntity();

        if (empty($where)) {
            $whereFragments = [];

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
            $sortFragments = [];

            $sortFragments[] = 'u.'. $sortbyfield . ' ' . DataUtil::formatForStore($sortorder);

            if ('uname' != $sortbyfield) {
                $sortFragments[] = 'u.uname ASC';
            }

            if (!empty($sortFragments)) {
                $orderby = 'ORDER BY ' . implode(', ', $sortFragments);
            }
        }

        $em = \ServiceUtil::get('doctrine.orm.default_entity_manager');
        $dql = "SELECT u FROM Zikula\UsersModule\Entity\UserEntity u $where $orderby";
        $query = $em->createQuery($dql);

        if (isset($limit) && is_numeric($limit) && $limit > 0) {
            $query->setMaxResults($limit);

            if (isset($offset) && is_numeric($offset) && $offset > 0) {
                $query->setFirstResult($offset);
            }
        }

        $users = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $usersObj = [];
        foreach ($users as $user) {
            $usersObj[$user['uid']] = $user;
        }

        return $usersObj;
    }

    /**
     * Get the uid of a user from the username.
     *
     * @param string $uname           The username
     * @param bool   $forRegistration Get the id for a pending registration (default = false)
     *
     * @return integer|boolean The uid if found, false if not
     */
    public static function getIdFromName($uname, $forRegistration = false)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $result = self::getVars($uname, false, 'uname', $forRegistration);

        return $result && isset($result['uid']) ? $result['uid'] : false;
    }

    /**
     * Get the uid of a user from the email (case for unique emails).
     *
     * @param string $email           The user email
     * @param bool   $forRegistration Get the id for a pending registration (default = false)
     *
     * @return integer|boolean The uid if found, false if not
     */
    public static function getIdFromEmail($email, $forRegistration = false)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $result = self::getVars($email, false, 'email', $forRegistration);

        return $result && isset($result['uid']) ? $result['uid'] : false;
    }

    /**
     * Checks the alias and returns if we save the data in the Profile module's user_data table or the users table.
     *
     * This should be removed if we ever go fully dynamic
     *
     * @param string $label The alias of the field to check
     *
     * @return true if found, false if not, void upon error
     */
    public static function fieldAlias($label)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        $isFieldAlias = false;

        // no change in uid or uname allowed, empty label is not an alias
        if (('uid' != $label) && ('uname' != $label) && !empty($label)) {
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
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

        return !SessionUtil::getVar('uid', 0);
    }

    /**
     * Determine if the record represented by the $uid is a registration or not.
     *
     * @param integer $uid The uid of the record in question
     *
     * @throws InvalidArgumentException If the uid is not valid
     *
     * @return boolean True if it is a registration record, otherwise false;
     */
    public static function isRegistration($uid)
    {
        @trigger_error('UserUtil is deprecated, please use User and Group entities instead.', E_USER_DEPRECATED);

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
