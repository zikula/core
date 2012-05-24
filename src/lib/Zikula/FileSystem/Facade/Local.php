<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 * @subpackage Zikula_FileSystem_Facade
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_FileSystem_Facade_Local is a facade interface for Local connections.
 *
 * Created especially to allow easy unit testing.
 */
class Zikula_FileSystem_Facade_Local
{
    /**
     * Facade for the copy function.
     *
     * @param string   $source  Source file to copy.
     * @param string   $dest    Target path.
     * @param resource $context Local filesystem context.
     *
     * @return boolean True on success.
     */
    public function copy($source, $dest, $context)
    {
        //@codeCoverageIgnoreStart
        return copy($source, $dest, $context);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the file_put_contents function.
     *
     * @param string                $filename Source file to copy.
     * @param string|array|resource $data     Data to write.
     * @param intiger               $flags    Flags.
     * @param resource              $context  Local filesystem context.
     *
     * @return intiger|boolean Number of bytes written on success, false on fail.
     */
    public function putContents($filename, $data, $flags = 0, $context)
    {
        //@codeCoverageIgnoreStart
        return file_put_contents($filename, $data, $flags, $context);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the fopen function.
     *
     * @param string   $filename         Source file to open.
     * @param string   $mode             Fopen mode.
     * @param boolean  $use_include_path True to use include path.
     * @param resource $context          Local filesystem context.
     *
     * @return resource|boolean Resource handle on success, false on fail.
     */
    public function fileOpen($filename, $mode, $use_include_path = false, $context)
    {
        //@codeCoverageIgnoreStart
        return fopen($filename, $mode, $use_include_path, $context);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the chmod function.
     *
     * @param string  $filename Source file to chmod.
     * @param intiger $mode     Perm to chmod to.
     *
     * @return boolean True on success.
     */
    public function chmod($filename, $mode)
    {
        //@codeCoverageIgnoreStart
        return chmod($filename, $mode);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the scandir function.
     *
     * @param string   $directory     Directory to scan.
     * @param intiger  $sorting_order Sort the contents?.
     * @param resource $context       Local filesystem context.
     *
     * @return array|boolean Array of contents on success, false on failure.
     */
    public function scandir($directory, $sorting_order = 0, $context)
    {
        //@codeCoverageIgnoreStart
        return scandir($directory, $sorting_order, $context);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the chdir function.
     *
     * @param string $dir Directory to change into.
     *
     * @return boolean True on success.
     */
    public function chdir($dir)
    {
        //@codeCoverageIgnoreStart
        return chdir($dir);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the rename function.
     *
     * @param string   $oldname Old path to rename.
     * @param string   $newname New path.
     * @param resource $context Local filesystem context.
     *
     * @return boolean True on success.
     */
    public function rename($oldname, $newname, $context)
    {
        //@codeCoverageIgnoreStart
        return rename($oldname, $newname, $context);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for the unlink function.
     *
     * @param string   $filename Path to delete.
     * @param resource $context  Local filesystem context.
     *
     * @return boolean True on success.
     */
    public function delete($filename, $context)
    {
        //@codeCoverageIgnoreStart
        return unlink($filename, $context);
        //@codeCoverageIgnoreEnd
    }

}
