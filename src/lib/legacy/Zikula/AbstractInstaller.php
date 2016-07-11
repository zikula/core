<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract controller for module installer.
 *
 * @deprecated
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
     * @param $bundle
     *
     * @return void
     */
    protected function _configureBase($bundle = null)
    {
        $this->systemBaseDir = realpath('.');
        if (null !== $bundle) {
            $this->name = $bundle->getName();
            $this->domain = ZLanguage::getModuleDomain($this->name);
            $this->baseDir = $bundle->getPath();
            $versionClass =  $bundle->getVersionClass();
            $this->version = new $versionClass($bundle);
        } else {
            $className = $this->getReflection()->getName();
            $separator = (false === strpos($className, '_')) ? '\\' : '_';
            $parts = explode($separator, $className);
            $this->name = $parts[0];
            $this->baseDir = $this->libBaseDir = realpath(dirname($this->reflection->getFileName()).'/../..');
            if (realpath("{$this->baseDir}/lib/" . $this->name)) {
                $this->libBaseDir = realpath("{$this->baseDir}/lib/" . $this->name);
            }

            $versionClass = "{$this->name}\\{$this->name}Version";
            $versionClassOld = "{$this->name}_Version";
            $versionClass = class_exists($versionClass) ? $versionClass : $versionClassOld;
            $this->version = new $versionClass();
        }

        $this->modinfo = [
            'directory' => $this->name,
            'type'      => ModUtil::getModuleBaseDir($this->name) == 'system' ? ModUtil::TYPE_SYSTEM : ModUtil::TYPE_MODULE
        ];

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
