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

namespace Zikula\Framework;

/**
 * Abstract controller for module installer.
 */
abstract class AbstractInstaller extends AbstractBase
{
    /**
     * Version instance of the module.
     *
     * @var AbstractVersion
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
        $class = get_class($this);
        $parts = strpos($class, '_') ? explode('_', $class) : explode('\\', $class);
        $this->name = $parts[0];
        $this->baseDir = realpath(dirname($this->getReflection()->getFileName()).'/../..');
        $this->modinfo = array(
            'directory' => $this->name,
            'type'      => \ModUtil::getModuleBaseDir($this->name) == 'system' ? \ModUtil::TYPE_SYSTEM : \ModUtil::TYPE_MODULE
        );
        $versionClass = "{$this->name}\Version";
        $this->version = new $versionClass;
        if ($this->modinfo['type'] == \ModUtil::TYPE_MODULE) {
            $this->domain = \ZLanguage::getModuleDomain($this->name);
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