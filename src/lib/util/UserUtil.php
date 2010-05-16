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
        if (empty($profileModule) || $profileModule != 'Profile' || !pnModAvailable($profileModule)) {
            return array();
        }

        pnModDBInfoLoad($profileModule);
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
}
