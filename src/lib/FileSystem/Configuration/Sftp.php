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
 * SFTP Configuration class.
 *
 * Configuration class for SFTP driver. please see documentation for FileSystem_Configuration
 * for more details on configuration classes. This class extends FileSystem_Configuration.
 * The only purpose to this class is to provide a configuration object to be used by the
 * sftp driver.
 */
class FileSystem_Configuration_Sftp extends FileSystem_Configuration
{
    /**
     * SFTP host.
     *
     * @var $host string
     */
    protected $host;

    /**
     * SFTP username.
     *
     * @var string
     */
    protected $user;

    /**
     * SFTP pasword.
     *
     * @var string
     */
    protected $pass;

    /**
     * SFTP start directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * SFTP port number.
     *
     * @var integer
     */
    protected $port;

    /**
     * Constructs a configuration for the SFTP driver.
     *
     * @param string  $host The host to connect to.
     * @param string  $user The username when connecting (default = 'Anonymous').
     * @param string  $pass The password associated with $user (default = '').
     * @param string  $dir  The directory on which to enter immediatly after connecting (default = './') (optional).
     * @param integer $port The port to use when connecting to $host (default = 22) (optional).
     *
     * @return void
     */
    public function __construct($host = 'localhost', $user = 'Anonymous', $pass = '', $dir = './', $port = 22)
    {
        $this->host = ($host == '' ? 'localhost' : $host);
        $this->user = $user;
        $this->pass = $pass;
        $this->dir = ($dir == '' ? './' : (substr($dir, 0, 1) == '/' || substr($dir, 0, 2) == './' ? $dir : "./$dir"));
        $this->port = ($port == "" || !is_numeric($port) ? 22 : $port);
    }

    /**
     * Get user property.
     *
     * @return string User.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get pass property.
     *
     * @return string Password.
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Get host property.
     *
     * @return string Host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get FTP port property.
     *
     * @return int Port number.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get directory property.
     *
     * @return string Directory.
     */
    public function getDir()
    {
        return $this->dir;
    }
}