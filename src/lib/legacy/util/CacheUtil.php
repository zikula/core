<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CacheUtil
 * @deprecated remove at Core-2.0
 */
class CacheUtil
{
    /**
     * Get the location of the local cache directory.
     *
     * @param string $dir The name of the directory to get
     *
     * @return string Location of the cache directory
     */
    public static function getLocalDir($dir = null)
    {
        $array = [];
        $tmpDir = ServiceUtil::get('service_container')->getParameter('temp_dir');
        $array[] = DataUtil::formatForOS($tmpDir, true);

        if (!is_null($dir)) {
            $array[] = DataUtil::formatForOS($dir);
        }

        $path = implode('/', $array);

        return $path;
    }

    /**
     * Create a directory below zikula's local cache directory.
     *
     * @param string $dir      The name of the directory to create
     * @param mixed  $mode     The (UNIX) mode we wish to create the files with
     * @param bool   $absolute Whether to process the passed dir as an absolute path or not
     *
     * @return boolean true if successful, false otherwise
     */
    public static function createLocalDir($dir, $mode = 0777, $absolute = true)
    {
        $path = DataUtil::formatForOS(System::getVar('temp'), true) . '/' . $dir;

        $mode = isset($mode) ? $mode : ServiceUtil::getManager()->getParameter('system.chmod_dir');

        if (!FileUtil::mkdirs($path, $mode, $absolute)) {
            return false;
        }

        return true;
    }

    /**
     * Remove a directory from zikula's local cache directory.
     *
     * @param string $dir      The name of the directory to remove
     * @param bool   $absolute Whether to process the passed dir as an absolute path or not
     *
     * @return boolean true if successful, false otherwise
     */
    public static function removeLocalDir($dir, $absolute = false)
    {
        $path = DataUtil::formatForOS(System::getVar('temp'), true) . '/' . $dir;

        return FileUtil::deldir($path, $absolute);
    }

    /**
     * Clear the contents of a directory from zikula's local cache directory.
     *
     * @param string $dir The name of the directory to remove
     *
     * @return void
     */
    public static function clearLocalDir($dir)
    {
        self::removeLocalDir($dir, true);
        self::createLocalDir($dir, null, true);
    }
}
