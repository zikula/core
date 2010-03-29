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
class FileSystem_Facade_Local
{
    /**
     * Implement Setup.
     *
     * @return void
     */
    public function setup()
    {
        $this->driver = new FileSystem_Facade_Ftp();
    }
    
    public function copy($source, $dest, $context)
    {
        return copy($source, $dest, $context);
    }
    
    public function put_contents($filename, $data, $flags = 0, $context)
    {
        return file_put_contents($filename, $data, $flags, $context);
    }
      
    public function file_open($filename, $mode, $use_include_path = false, $context)
    {
        return fopen($filename, $mode, $use_include_path, $context);
    }
    
    public function chmod($filename, $mode)
    {
        return chmod($filename, $mode);
    }
    
    public function scandir($directory, $sorting_order = 0, $context)
    {
        return scandir($directory, $sorting_order, $context);
    }
    
    public function chdir($dir)
    {
        return chdir($dir);
    }
    
    public function rename($oldname, $newname, $context)
    {
        return rename($oldname, $newname, $context);
    }
    
    public function delete($filename, $context)
    {
        return unlink($filename, $context);
    }
    
}
