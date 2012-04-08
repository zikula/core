<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Legacy
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Loader class
 */
class Loader
{
    /**
     * Load a file from the specified location in the file tree
     *
     * @param fileName    The name of the file to load
     * @param path        The path prefix to use (optional) (default=null)
     * @param exitOnError whether or not exit upon error (optional) (default=true)
     * @param returnVar   The variable to return from the sourced file (optional) (default=null)
     *
     * @return string The file which was loaded
     */
    public static function loadFile($fileName, $path = null, $exitOnError = true, $returnVar = null)
    {
        if (!$fileName) {
            return z_exit(__f("Error! Invalid file specification '%s'.", $fileName));
        }

        $file = null;
        if ($path) {
            $file = "$path/$fileName";
        } else {
            $file = $fileName;
        }

        $file = DataUtil::formatForOS($file);

        if (is_file($file) && is_readable($file)) {
            if (include_once ($file)) {
                if ($returnVar) {
                    return $$returnVar;
                } else {
                    return $file;
                }
            }
        }

        if ($exitOnError) {
            return z_exit(__f("Error! Could not load the file '%s'.", $fileName));
        }

        return false;
    }

    /**
     * Load all files from the specified location in the pn file tree
     *
     * @param files        An array of filenames to load
     * @param path         The path prefix to use (optional) (default='null')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return boolean true
     */
    public static function loadAllFiles($files, $path = null, $exitOnError = false)
    {
        return self::loadFiles($files, $path, true, $exitOnError);
    }

    /**
     * Return after the first successful file load. This corresponds to the
     * default behaviour of loadFiles().
     *
     * @param files        An array of filenames to load
     * @param path         The path prefix to use (optional) (default='null')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return boolean true
     */
    public static function loadOneFile($files, $path = null, $exitOnError = false)
    {
        return self::loadFiles($files, $path, false, $exitOnError);
    }

    /**
     * Load multiple files from the specified location in the pn file tree
     * Note that in it's default invokation, this method exits after the
     * first successful file load.
     *
     * @param files       Array of filenames to load
     * @param path        The path prefix to use (optional) (default='null')
     * @param all         whether or not to load all files or exit upon 1st successful load (optional) (default=false)
     * @param exitOnError whether or not exit upon error (optional) (default=true)
     * @param returnVar   The variable to return if $all==false (optional) (default=null)
     *
     * @return boolean true
     */
    public static function loadFiles($files, $path = null, $all = false, $exitOnError = false, $returnVar = '')
    {
        if (!is_array($files) || !$files) {
            return z_exit(__('Error! Invalid file array specification.'));
        }

        $files = array_unique($files);

        $loaded = false;
        foreach ($files as $file) {
            $rc = self::loadFile($file, $path, $exitOnError, $returnVar);

            if ($rc) {
                $loaded = true;
            }

            if ($loaded && !$all) {
                break;
            }
        }

        if ($returnVar && !$all) {
            return $rc;
        }

        return $loaded;
    }
}