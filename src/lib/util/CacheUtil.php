<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * CacheUtil
 */
class CacheUtil
{
    /**
     * Get a unique string for a user, depending on this group memberships.
     *
     * String ready to be used as part of the CacheID of the output views.
     * Useful when there aren't another user-based access privilegies, just group permissions.
     *
     * @param integer $uid User ID to get the group memberships from. Default: current user.
     *
     * @return string Cache string to use on Zikula_View.
     */
    public static function getUserString($uid = null)
    {
        $str = UserUtil::getGroupListForUser($uid, '_');

        return $str == '-1' ? 'guest' : 'groups_'.$str;
    }

    /**
     * Get the location of the local cache directory.
     *
     * @param string $dir The name of the directory to get.
     *
     * @return string Location of the cache directory.
     */
    public static function getLocalDir($dir = null)
    {
        $array = array();
        $array[] = DataUtil::formatForOS(System::getVar('temp'), true);

        if (!is_null($dir)) {
            $array[] = DataUtil::formatForOS($dir);
        }

        $path = implode('/', $array);

        return $path;
    }

    /**
     * Create a directory below zikula's local cache directory.
     *
     * @param string $dir  The name of the directory to create.
     * @param string $mode The (UNIX) mode we wish to create the files with.
     *
     * @return boolean true if successful, false otherwise.
     */
    public static function createLocalDir($dir, $mode = null, $absolute = false)
    {
        $path = DataUtil::formatForOS(System::getVar('temp'), true) . '/' . $dir;

        if (!FileUtil::mkdirs($path, $mode, $absolute)) {
            return false;
        }

        return true;
    }

    /**
     * Remove a directory from zikula's local cache directory.
     *
     * @param string $dir The name of the directory to remove.
     *
     * @return boolean true if successful, false otherwise.
     */
    public static function removeLocalDir($dir)
    {
        $path = DataUtil::formatForOS(System::getVar('temp'), true) . '/' . $dir;

        return FileUtil::deldir($path);
    }

    /**
     * Clear the contents of a directory from zikula's local cache directory.
     *
     * THIS DOES WORK ONLY ONCE ON SOME CONFIGURATIONS, A SECOND CLEARING OF COMPILED TEMPLATES
     * FAILS. SO BETTER DO NOT USE THIS ATM.
     * ToDo: Check why and fix this.
     *
     * @param string $dir The name of the directory to remove.
     *
     * @return void
     */
    public static function clearLocalDir($dir)
    {
        self::removeLocalDir($dir);
        self::createLocalDir($dir);
    }
}
