<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Notes on security system
 *
 * Special UID and GIDS:
 *  UID -1 corresponds to 'all users', includes unregistered users
 *  GID -1 corresponds to 'all groups', includes unregistered users
 *  UID 0 corresponds to unregistered users
 *  GID 0 corresponds to unregistered users
 */

/**
 * SecurityUtil
 *
 * @package Zikula_Core
 * @subpackage SecurityUtil
 * @author Drak
 */
class SecurityUtil
{
    /**
     * Check permissions
     *
     * @param string $component
     * @param string $instance
     * @param string $level
     * @param string $user
     * @return bool
     */
    public static function checkPermission($component = null, $instance = null, $level = null, $user = null)
    {
        static $groupperms = array();

        if (!is_numeric($level)) {
            return pn_exit(__f('Invalid security level [%1$s] received in %2$s', array($level, 'SecurityUtil::checkPermission')));
        }

        if (!$user) {
            $user = pnUserGetVar('uid');
        }

        if (!isset($GLOBALS['authinfogathered'][$user]) || (int) $GLOBALS['authinfogathered'][$user] == 0) {
            $groupperms[$user] = self::getAuthInfo($user); // First time here - get auth info
            if (count($groupperms[$user]) == 0) {
                return false; // No permissions
            }
        }

        $res = self::getSecurityLevel($groupperms[$user], $component, $instance) >= $level;

        // if the check failed, we save the info so that LogUtil has it readily available
        if (!$res) {
            global $ZRuntime;
            $ZRuntime['security']['last_failed_check']['component'] = $component;
            $ZRuntime['security']['last_failed_check']['instance'] = $instance;
            $ZRuntime['security']['last_failed_check']['level'] = $level;
            $ZRuntime['security']['last_failed_check']['user'] = $user;
        }

        return $res;
    }

    /**
     * register a permission schema
     *
     * @param string $component
     * @param string $schema
     * @return bool
     */
    public static function registerPermissionSchema($component, $schema)
    {
        if (!empty($GLOBALS['schemas'][$component])) {
            return false;
        }

        $GLOBALS['schemas'][$component] = $schema;
        return true;
    }

    /**
     * confirm auth key
     *
     * @param string $modname
     * @param string $varname
     *
     * @return bool
     */
    public static function confirmAuthKey($modname = '', $varname = 'authid')
    {
        if (!$varname) {
            $varname = 'authid';
        }

        $authid = FormUtil::getPassedValue($varname);

        if (empty($modname)) {
            $modname = ModUtil::getName();
        }

        // get the module info
        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($modname));
        $modname = strtolower($modinfo['name']);

        // get the array of randomed values per module and check if exists
        $rand_arr = SessionUtil::getVar('rand');
        if (!isset($rand_arr[$modname])) {
            return false;
        } else {
            $rand = $rand_arr[$modname];
        }

        // Regenerate static part of key
        $key = $rand . $modname;

        // validate useragent
        if (pnConfigGetVar('sessionauthkeyua')) {
            $useragent = sha1(pnServerGetVar('HTTP_USER_AGENT'));
            if (SessionUtil::getVar('useragent') != $useragent) {
                return false;
            }
        }

        // Test works because timestamp is embedded in authkey and appended
        // at the end of the authkey, so we can test validity of authid as
        // well as the number of seconds elapsed since generation.
        $keyexpiry = (int) pnConfigGetVar('keyexpiry');
        $timestamp = ($keyexpiry > 0 ? substr($authid, 40, strlen($authid)) : '');
        $key .= $timestamp;
        // check build key against authid
        if (sha1($key) == substr($authid, 0, 40)) {
            // now test if time expired
            $elapsedTime = (int) ((int) $timestamp > 0 ? time() - $timestamp : $keyexpiry - 1);
            if ($elapsedTime < $keyexpiry) {
                $rand_arr[$modname] = RandomUtil::getString(32, 40, false, true, true, false, true, true, false);
                SessionUtil::setVar('rand', $rand_arr);
                return true;
            }
        }

        return false;
    }

    /**
     * generate auth key
     *
     * @param string $modname module name
     * @return string an encrypted key for use in authorisation of operations
     */
    public static function generateAuthKey($modname = '')
    {
        // since we need sessions for authorisation keys we should check
        // if a session exists and if not create one
        SessionUtil::requireSession();

        if (empty($modname)) {
            $modname = ModUtil::getName();
        }

        // get the module info
        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($modname));
        $modname = strtolower($modinfo['name']);

        // get the array of randomed values per module
        // and generate the one of the current module if doesn't exist
        $rand_arr = SessionUtil::getVar('rand');

        if (!isset($rand_arr[$modname])) {
            $rand_arr[$modname] = RandomUtil::getString(32, 40, false, true, true, false, true, true, false);
            SessionUtil::setVar('rand', $rand_arr);
        }

        $key = $rand_arr[$modname] . $modname;
        if (pnConfigGetVar('keyexpiry') > 0) {
            $timestamp = time();
            $authid = sha1($key . $timestamp) . $timestamp;
        } else {
            $authid = sha1($key);
        }

        // Return encrypted key
        return $authid;
    }

    /**
     * get auth info
     *
     * @param unknown_type $user
     * @return array two element array of user and group permissions
     */
    public static function getAuthInfo($user = null)
    {
        // Table columns we use - ModUtil::dbInfoLoad is done in pnInit
        $pntable = pnDBGetTables();
        $groupmembershipcolumn = $pntable['group_membership_column'];
        $grouppermcolumn = $pntable['group_perms_column'];

        // Empty arrays
        $groupperms = array();

        $uids[] = -1;
        // Get user ID
        if (!isset($user)) {
            if (!UserUtil::isLoggedIn()) {
                // Unregistered UID
                $uids[] = 0;
                $vars['Active User'] = 'unregistered';
            } else {
                $uids[] = pnUserGetVar('uid');
                $vars['Active User'] = pnUserGetVar('uid');
            }
        } else {
            $uids[] = $user;
            $vars['Active User'] = $user;
        }
        $uids = implode(',', $uids);

        // Get all groups that user is in
        $where = "WHERE $groupmembershipcolumn[uid] IN (" . DataUtil::formatForStore($uids) . ')';
        $fldArray = DBUtil::selectFieldArray('group_membership', 'gid', $where);
        if ($fldArray === false) {
            return $groupperms;
        }

        static $usergroups = array();
        if (!$usergroups) {
            $usergroups[] = -1;
            if (!UserUtil::isLoggedIn()) {
                $usergroups[] = 0; // Unregistered GID
            }
        }

        $allgroups = array_merge($usergroups, $fldArray);
        $allgroups = implode(',', $allgroups);

        // Get all group permissions
        $where = "WHERE $grouppermcolumn[gid] IN (" . DataUtil::formatForStore($allgroups) . ')';
        $orderBy = "ORDER BY $grouppermcolumn[sequence]";
        $objArray = DBUtil::selectObjectArray('group_perms', $where, $orderBy);
        if (!$objArray) {
            return $groupperms;
        }

        foreach ($objArray as $obj) {
            $component = self::_fixsecuritystring($obj['component']);
            $instance = self::_fixsecuritystring($obj['instance']);
            $level = self::_fixsecuritystring($obj['level']);
            // Search/replace of special names
            preg_match_all('/<([^>]+)>/', $instance, $res);
            $size = count($res[1]);
            for ($i = 0; $i < $size; $i++) {
                $instance = preg_replace('/<([^>]+)>/', $vars[$res[1][$i]], $instance, 1);
            }
            $groupperms[] = array('component' => $component, 'instance' => $instance, 'level' => $level);
        }

        // we've now got the permissions info
        $GLOBALS['authinfogathered'][$user] = 1;
        return $groupperms;
    }

    /**
     * get security level
     *
     * @param array $perms
     * @param string $component
     * @param string $instance
     * @return int matching security level
     */
    public static function getSecurityLevel($perms, $component, $instance)
    {
        $level = ACCESS_INVALID;

        // If we get a test component or instance purely consisting of ':' signs
        // then it counts as blank
        //itevo
        if ($component == str_repeat(':', strlen($component))) {
            $component = '';
        }
        if ($instance == str_repeat(':', strlen($instance))) {
            $instance = '';
        }

        // Test for generic permission
        if ((empty($component)) && (empty($instance))) {
            // Looking for best permission
            foreach ($perms as $perm) {
                if ($perm['level'] > $level) {
                    $level = $perm['level'];
                }
            }
            return $level;
        }

        // Test if user has ANY access to given component, without determining exact instance
        if ($instance == 'ANY') {
            $levels = array($level);
            foreach ($perms as $perm) {
                // component check
                if (!preg_match("=^$perm[component]$=", $component)) {
                    continue; // component doestn't match.
                }

                // if component matches -  keep the level we found
                $levels[] = $perm['level'];

                // check that the instance matches :: or '' (nothing)
                if ((preg_match("=^$perm[instance]$=", '::') || preg_match("=^$perm[instance]$=", ''))) {
                    break; // instance matches - stop searching
                }
            }

            // select the highest level among found
            $level = max($levels);
            return $level;
        }

        // Test for generic instance
        // additional fixes by BMW [larsneo]
        // if the instance is empty, then we're looking for the per-module
        // permissions.
        if (empty($instance)) {
            // if $instance is empty, then there must be a component.
            // Looking for best permission
            foreach ($perms as $perm) {
                // component check
                if (!preg_match("=^$perm[component]$=", $component)) {
                    continue; // component doestn't match.
                }

                // check that the instance matches :: or '' (nothing)
                if (!(preg_match("=^$perm[instance]$=", '::') || preg_match("=^$perm[instance]$=", ''))) {
                    continue; // instance does not match
                }

                // We have a match - set the level and quit
                $level = $perm['level'];
                break;

            }
            return $level;
        }

        // Normal permissions check
        // there *is* a $instance at this point.
        foreach ($perms as $perm) {

            // if there is a component, check that it matches
            if (($component != '') && (!preg_match("=^$perm[component]$=", $component))) {
                // component exists, and doestn't match.
                continue;
            }

            // Confirm that instance matches
            if (!preg_match("=^$perm[instance]$=", $instance)) {
                // instance does not match
                continue;
            }

            // We have a match - set the level and quit looking
            $level = $perm['level'];
            break;
        }

        return $level;
    }

    /**
     * fix security string
     *
     * @access private
     * @param string $string
     * @return string
     */
    public static function _fixsecuritystring($string)
    {
        if (empty($string)) {
            $string = '.*';
        }
        if (strpos($string, ':') === 0) {
            $string = '.*' . $string;
        }
        $string = str_replace('::', ':.*:', $string);
        if (strrpos($string, ':') === strlen($string) - 1) {
            $string = $string . '.*';
        }
        return $string;
    }

    /**
     * sign data object leaving data clearly visible
     *
     * @param unknown_type $data
     * @return serialized string of signed data
     */
    public static function signData($data)
    {
        $key = pnConfigGetVar('signingkey');
        $unsignedData = serialize($data);
        $signature = sha1($unsignedData . $key);
        $signedData = serialize(array($unsignedData, $signature));

        return $signedData;
    }

    /**
     * verify signed data object
     *
     * @param string of serialized $data
     * @return mixed array or string of data if true or bool false if false
     */
    public static function checkSignedData($data)
    {
        $key = pnConfigGetVar('signingkey');
        $signedData = unserialize($data);
        $signature = sha1($signedData[0] . $key);
        if ($signature != $signedData[1]) {
            return false;
        }

        return unserialize($signedData[0]);
    }

    /**
     * Translation functions - avoids globals in external code
     */
    // Translate level -> name
    public static function accesslevelname($level)
    {
        $accessnames = self::accesslevelnames();
        return $accessnames[$level];
    }

    /**
     * get access level names
     *
     * @return array of access names
     */
    public static function accesslevelnames()
    {
        static $accessnames = null;
        if (!is_array($accessnames)) {
            $accessnames = array(
                0 => __('No access'),
                100 => __('Overview access'),
                200 => __('Read access'),
                300 => __('Comment access'),
                400 => __('Moderate access'),
                500 => __('Edit access'),
                600 => __('Add access'),
                700 => __('Delete access'),
                800 => __('Admin access'));
        }

        return $accessnames;
    }

}
