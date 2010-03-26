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
 * Driver Abstract.
 *
 * This abstract class contains the basic construct for every driver, which
 * simply gets the FileSystem_Configuration and saves it. all drivers which
 * extend this class must implement FileSystem_Interface. furthermore This class
 * extends FileSystem_Error, meaning that all drivers which extend this class
 * will have access to FileSystem_Error functions for their object. Please see
 * the documentation for FileSystem_Error and FileSystem_Interface for more
 * information.
 */
abstract class FileSystem_Driver extends FileSystem_Error implements FileSystem_Interface
{
    /**
     * Configuration object.
     *
     * @var FileSystem_Configuration
     */
    protected $configuration;

    /**
     * Construct the driver with the configuration.
     *
     * @param FileSystem_Configuration $configuration The configuration to be used.
     */
    public function __construct(FileSystem_Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->connect();
    }
}