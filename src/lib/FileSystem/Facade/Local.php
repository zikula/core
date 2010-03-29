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
    	//@codeCoverageIgnoreStart
        $this->driver = new FileSystem_Facade_Ftp();
        //@codeCoverageIgnoreEnd
    }
    
    public function copy($source, $dest, $context)
    {
    	//@codeCoverageIgnoreStart
        return copy($source, $dest, $context);
        //@codeCoverageIgnoreEnd
    }
    
    public function put_contents($filename, $data, $flags = 0, $context)
    {
    	//@codeCoverageIgnoreStart
        return file_put_contents($filename, $data, $flags, $context);
        //@codeCoverageIgnoreEnd
    }
      
    public function file_open($filename, $mode, $use_include_path = false, $context)
    {
    	//@codeCoverageIgnoreStart
        return fopen($filename, $mode, $use_include_path, $context);
        //@codeCoverageIgnoreEnd
    }
    
    public function chmod($filename, $mode)
    {
    	//@codeCoverageIgnoreStart
        return chmod($filename, $mode);
        //@codeCoverageIgnoreEnd
    }
    
    public function scandir($directory, $sorting_order = 0, $context)
    {
    	//@codeCoverageIgnoreStart
        return scandir($directory, $sorting_order, $context);
        //@codeCoverageIgnoreEnd
    }
    
    public function chdir($dir)
    {
    	//@codeCoverageIgnoreStart
        return chdir($dir);
        //@codeCoverageIgnoreEnd
    }
    
    public function rename($oldname, $newname, $context)
    {
    	//@codeCoverageIgnoreStart
        return rename($oldname, $newname, $context);
        //@codeCoverageIgnoreEnd
    }
    
    public function delete($filename, $context)
    {
    	//@codeCoverageIgnoreStart
        return unlink($filename, $context);
        //@codeCoverageIgnoreEnd
    }
    
}
