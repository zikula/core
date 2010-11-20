<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract controller for module installer.
 */
abstract class Zikula_Installer extends Zikula_Base
{
    /**
     * Version instance of the module.
     * 
     * @var Zikula_Version
     */
    protected $version;

    /**
     * Setup internal properties.
     *
     * @return void
     */
    protected function _setup()
    {
        $this->reflection = new ReflectionObject($this);
        $parts = explode('_', $this->reflection->getName());
        $this->name = $parts[0];
        $this->baseDir = dirname(realpath($this->reflection->getFileName()));
        $this->modinfo = array();
        $p = explode(DIRECTORY_SEPARATOR, $this->baseDir);
        $this->modinfo['directory'] = end($p);
        $this->modinfo['type'] = prev($p);
        $this->systemBaseDir = realpath("{$this->baseDir}/../..");
        $this->libBaseDir = realpath("{$this->baseDir}/lib/" . $this->modinfo['directory']);
        $versionClass = "{$this->name}_Version";
        $this->version = new $versionClass;
    }

    /**
     * Install interface.
     *
     * @return boolean
     */
    public function install()
    {
        return true;
    }

    /**
     * Upgrade interface.
     *
     * @param string $oldversion Old version number.
     *
     * @return boolean|string $args True, false or last successful version number upgrade.
     */
    public function upgrade($oldversion)
    {
        return true;
    }

    /**
     * Uninstall interface.
     *
     * @return boolean
     */
    public function uninstall()
    {
        return true;
    }
}