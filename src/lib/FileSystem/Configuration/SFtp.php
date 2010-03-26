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
 * Configuration class for SFTP driver. please see documentation for FileSystem_Configuration
 * for more details on configuration classes. This class extends FileSystem_Configuration.
 * The only purpose to this class is to provide a configuration object to be used by the
 * sftp driver.
 * @author kage
 *
 */
class FileSystem_Configuration_SFtp extends FileSystem_Configuration
{
    protected $host = "localhost";
    protected $user = "Anonymous";
    protected $pass = "";
    protected $dir = "./";
    protected $port = "22";

    /**
     * Constructs a configuration for the SFTP driver.
     *
     * @param String $host The host to connect to.
     * @param String $user The username when connecting (default = Anonymous)
     * @param String $pass The password associated with $user (default = "")
     * @param String $dir  The directory on which to enter immediatly after connecting (default = "./") (optional)
     * @param Int $port    The port to use when connecting to $host (default = 22) (optional)
     */
    public function __construct($host = "localhost", $user = "Anonymous", $pass
        = "", $dir = "./", $port = "22")
    {
        $this->host = ($host == "" ? 'localhost' : $host);
        $this->user = $user;
        $this->pass = $pass;
        $this->dir = ($dir == "" ? './' : (substr($dir, 0, 1) == '/' ? $dir : "./$dir"));
        $this->port = ($port == "" || !is_numeric($port) ? '22' : $port);
    }
    
    public function getUser()
    {
        return $this->user;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getDir()
    {
        return $this->dir;
    }
}