<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager, array $options = array())
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $eventManager;
        $this->options = $options;
        $this->_setup();

        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }

        $this->postInitialize();
    }

    private function _setup()
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
    }

    abstract public function install();
    abstract public function upgrade($args);
    abstract public function uninstall();
}