<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Driver Abstract.
 */
abstract class Zikula_FileSystem_AbstractDriver
{
    /**
     * Configuration object.
     *
     * @var Zikula_FileSystem_Configuration
     */
    protected $configuration;

    /**
     * The Driver object (facade).
     *
     * @var object
     */
    protected $driver;

    /**
     * The error handler.
     *
     * @var object
     */
    protected $errorHandler;

    /**
     * Construct the driver with the configuration.
     *
     * @param Zikula_FileSystem_ConfigurationInterface $configuration The configuration to be used.
     *
     * @throws InvalidArgumentException If wrong configuration class received.
     */
    public function __construct(Zikula_FileSystem_ConfigurationInterface $configuration)
    {
        // validate we get correct configuration class type.
        $type = str_ireplace('Zikula_FileSystem_', '', get_class($this));
        $validName = "Zikula_FileSystem_Configuration_{$type}";

        if ($validName != get_class($configuration)) {
            throw new InvalidArgumentException(
                sprintf('Invalid configuration class for %1$s.  Expected %2$s but got %3$s instead.',
                get_class($this), $validName, get_class($configuration)));
        }

        $this->configuration = $configuration;

        $facade = "Zikula_FileSystem_Facade_{$type}";
        $this->driver = new $facade();
        $this->errorHandler = new Zikula_FileSystem_Error();
    }

    /**
     * Setter for facade driver.
     *
     * @param object $driver The facade driver.
     *
     * @return void
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * Getter for errorHanler.
     *
     * @return object ErrorHandler class.
     */
    public function getErrorHandler()
    {
        return $this->errorHandler;
    }

    /**
     * Interface connect function.
     *
     * For most functions errors will be regesterd on fail, See FileSystem/Error class
     * for more details.
     *
     * @return boolean True on connect, false on failure.
     */
    abstract public function connect();

    /**
     * Interface get function.
     *
     * @param string $local  The pathname to the desired local file.
     * @param string $remote The pathname to the remote file to get.
     *
     * @return boolean True on success false on failure.
     */
    abstract public function get($local, $remote);

    /**
     * Interface fget function.
     *
     * @param string $remote The path to the remote file.
     *
     * @return resource|bool The resource on success false on fail.
     */
    abstract public function fget($remote);

    /**
     * Interface put function.
     *
     * @param string $local  The pathname to the local file.
     * @param string $remote The pathname to the desired remote file.
     *
     * @return boolean True on success false on failure.
     */
    abstract public function put($local, $remote);

    /**
     * Interface fput function.
     *
     * @param stream|resource $stream The resource to put remotely, probably the resource returned from a fget.
     * @param string          $remote The pathname to the desired remote pathname.
     *
     * @return boolean|integer Number of bytes written on success, false on failure.
     */
    abstract public function fput($stream, $remote);

    /**
     * Interface chmod function.
     *
     * @param integer $perm The permission to assign to the file, unix style (example: 777 for full permission).
     * @param string  $file The pathname to the remote file to chmod.
     *
     * @return boolean|integer The new permission or false if failed.
     */
    abstract public function chmod($perm, $file);

    /**
     * Interface ls function.
     *
     * @param string $dir The directory to get the contents of, blank for current directory, start with / for absolute path.
     *
     * @return array|boolean An array of the contents of $dir or false if fail.
     */
    abstract public function ls($dir = '');

    /**
     * Interface cd function.
     *
     * @param string $dir The directory on the remote machine to enter, start with '/' for absolute path.
     *
     * @return boolean True on success false on failure.
     */
    abstract public function cd($dir = '');

    /**
     * Interface cp function.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to copy the source file.
     *
     * @return boolean True on success false on failure.
     */
    abstract public function cp($sourcepath, $destpath);

    /**
     * Interface mf function.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to move the source file.
     *
     * @return boolean True on success false on failure.
     */
    abstract public function mv($sourcepath, $destpath);

    /**
     * Interface rm function.
     *
     * @param string $sourcepath The path to the remote file to remove.
     *
     * @return boolean
     */
    abstract public function rm($sourcepath);
}
