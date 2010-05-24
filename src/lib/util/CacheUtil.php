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
 * CacheUtil
 *
 * @package Zikula_Core
 * @subpackage CacheUtil
 */
class CacheUtil
{
    /**
     * get the location of the local cache directory
     *
     * @return string location of the cache directory
     */
    public static function getLocalDir()
    {
        return DataUtil::formatForOS(System::getVar('temp'), true);
    }

    /**
     * Create a directory below zikula's local cache directory
     *
     * @param dir The name of the directory to create
     * @param mode The (UNIX) mode we wish to create the files with
     *
     * @return bool true if successful, false otherwise
     */
    public static function createLocalDir($dir, $mode = null)
    {
        $path = DataUtil::formatForOS(System::getVar('temp'), true) . '/' . $dir;
        if (!FileUtil::mkdirs($path, $mode)) {
            return false;
        }
        touch("{$path}/index.html");
        return true;
    }

    /**
     * Remove a directory from zikula's local cache directory
     *
     * @param dir The name of the directory to remove
     *
     * @return bool true if successful, false otherwise
     */
    public static function removeLocalDir($dir)
    {
        $path = DataUtil::formatForOS(System::getVar('temp'), true) . '/' . $dir;
        return FileUtil::deldir($path);
    }

    /**
     * Clear the contents of a directory from zikula's local cache directory
     *
     * THIS DOES WORK ONLY ONCE ON SOME CONFIGURATIONS, A SECOND CLEARING OF COMPILED TEMPLATES
     * FAILS. SO BETTER DO NOT USE THIS ATM.
     * ToDo: Check why and fix this.
     *
     * @param dir The name of the directory to remove
     */
    public static function clearLocalDir($dir)
    {
        self::removeLocalDir($dir);
        self::createLocalDir($dir);
    }
}
