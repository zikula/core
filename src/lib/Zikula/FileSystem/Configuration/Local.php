<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 * @subpackage Zikula_FileSystem_Configuration
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Local configuration class.
 *
 * Configuration class for LOCAL driver. please see documentation for Zikula_FileSystem_Configuration
 * for more details on configuration classes. This class implements Zikula_FileSystem_Configuration.
 * The only purpose to this class is to provide a configuration object to be used by the
 * Local driver.
 */
class Zikula_FileSystem_Configuration_Local implements Zikula_FileSystem_ConfigurationInterface
{
    /**
     * Start directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * Constructor.
     *
     * @param string $dir Directory.
     */
    public function __construct($dir = '')
    {
        $this->dir = $dir;
    }

    /**
     * Get dir property.
     *
     * @return string Directory.
     */
    public function getDir()
    {
        return $this->dir;
    }
}
