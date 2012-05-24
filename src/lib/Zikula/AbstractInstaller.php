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
abstract class Zikula_AbstractInstaller extends Zikula_AbstractBase
{
    /**
     * Version instance of the module.
     *
     * @var Zikula_AbstractVersion
     */
    protected $version;

    /**
     * Setup internal properties.
     *
     * @return void
     */
    protected function _configureBase()
    {
        $this->systemBaseDir = realpath('.');
        $parts = explode('_', $this->getReflection()->getName());
        $this->name = $parts[0];
        $this->baseDir = realpath(dirname($this->reflection->getFileName()).'/../..');
        $this->libBaseDir = realpath("{$this->baseDir}/lib/" . $this->name);
        $this->modinfo = array(
            'directory' => $this->name,
            'type'      => ModUtil::getModuleBaseDir($this->name) == 'system' ? ModUtil::TYPE_SYSTEM : ModUtil::TYPE_MODULE
        );
        $versionClass = "{$this->name}_Version";
        $this->version = new $versionClass;
        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }
    }

    /**
     * Install interface.
     *
     * @return boolean
     */
    abstract public function install();

    /**
     * Upgrade interface.
     *
     * @param string $oldversion Old version number.
     *
     * @return boolean|string $args True, false or last successful version number upgrade.
     */
    abstract public function upgrade($oldversion);

    /**
     * Uninstall interface.
     *
     * @return boolean
     */
    abstract public function uninstall();
}
