<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * FileSystem_Facade_Ftp is a facade interface for FTP connections.
 *
 * Created especially to allow easy unit testing.
 */
class FileSystem_Facade_Ftp
{
    /**
     * Facade for ftp_put().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param string   $remote_file The remote file to save as.
     * @param string   $local_file  The local file to put.
     * @param integer  $mode        The transfer mode.
     * @param integer  $startpos    The starting position.
     *
     * @return boolean True on success.
     */
    public function put($ftp_stream, $remote_file, $local_file, $mode, $startpos=0)
    {
        return ftp_put($ftp_stream, $remote_file, $local_file, $mode, $startpos);
    }
    
    /**
     * Facade for ftp_fput().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param string   $remote_file The remote file to put to.
     * @param resource $handle      The stream to put to $remote_file.
     * @param integer  $mode        The transfer mode.
     *
     * @return boolean True on success.
     */
    public function fput($ftp_stream, $remote_file, $handle, $mode = FTP_BINARY)
    {
        return fput($ftp_stream, $remote_file, $handle, $mode);
    }
    
    /**
     * Facade for ftp_get().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param string   $local_file  The local file to save to.
     * @param string   $remote_file The remote file to get.
     * @param integer  $mode        The transfer mode.
     * @param integer  $resumepos   The resume position.
     *
     * @return boolean True on success.
     */
    public function get($ftp_stream, $local_file, $remote_file, $mode, $resumepos = 0)
    {
        return fget($ftp_stream, $local_file, $remote_file, $mode, $resumepos);
    }
    
    /**
     * Facade for ftp_fget().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param resource $handle      The resource to get the data into.
     * @param string   $remote_file The remote file to fget.
     * @param integer  $mode        Transfer mode.
     * @param integer  $resumepos   The resume position.
     *
     * @return boolean True on success.
     */
    public function fget($ftp_stream, $handle, $remote_file, $mode, $resumepos = 0)
    {
        return ftp_fget($ftp_stream, $handle, $remote_file, $mode, $resumepos = 0);
    }
    
    /**
     * Facade for ftp_chmod().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param integer  $mode       The permission.
     * @param string   $filename   The filename to chmod.
     *
     * @return boolean True on success.
     */
    public function chmod($ftp_stream, $mode, $filename)
    {
        return ftp_chmod($ftp_stream, $mode, $filename);
    }
    
    /**
     * Facade for ftp_put().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $directory  The directory to list.
     *
     * @return boolean|array Array of contents on success, false on failure.
     */
    public function nlist($ftp_stream, $directory)
    {
        return ftp_nlist($ftp_stream, $directory);
    }
    
    /**
     * Facade for ftp_chdir().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $directory  The directory to change into.
     *
     * @return boolean True on success.
     */
    public function chdir($ftp_stream, $directory)
    {
        return ftp_chdir($ftp_stream, $directory);
    }
    
    /**
     * Facade for ftp_rename().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $oldname    The old filename.
     * @param string   $newname    The new filename.
     *
     * @return boolean True on success.
     */
    public function rename($ftp_stream, $oldname, $newname)
    {
        return ftp_rename($ftp_stream, $oldname, $newname);
    }
    
    /**
     * Facade for ftp_delete().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $path       The path or file to be removed.
     *
     * @return boolean True on success.
     */
    public function delete($ftp_stream, $path)
    {
        return delete($ftp_stream, $path);
    }
    
    /**
     * Facade for ftp_systype().
     *
     * @param resource $ftp_stream The ftp resource.
     *
     * @return boolean True on ftp systype.
     */
    public function systype($ftp_stream)
    {
        return ftp_systype($ftp_stream);
    }
}
