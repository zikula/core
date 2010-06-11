<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


abstract class Zikula_Plugin extends Zikula_EventHandler
{
    const VERSION = '1.0.0';

    protected $className;
    protected $modVarName;

    public function __construct(Zikula_EventManager $eventManager, Zikula_ServiceManager $serviceManager)
    {
        parent::__construct($eventManager, $serviceManager);
        $this->_setup();
    }

    private function _setup()
    {
        $this->className = get_class($this);
        $this->modVarName = strtolower(str_replace('_', '.', $this->className));
        $this->baseDir = realpath(dirname(__FILE__));
    }

    public function getVersion()
    {
        return self::VERSION;
    }

    public function getModVarName()
    {
        return $this->modVarName;
    }

    public function preInitialize()
    {
    }

    public function initialize()
    {
    }

    public function postInitialize()
    {
    }

    public function postEnable()
    {
    }

    public function postDisable()
    {
    }

    public function isEnabled()
    {
        $plugin = PluginUtil::getState($this->modVarName, PluginUtil::getDefaultState());
        return ($plugin['state'] === PluginUtil::ENABLED) ? true : false;
    }

    public function isInstalled()
    {
        $plugin = PluginUtil::getState($this->modVarName, PluginUtil::getDefaultState());
        return ($plugin['state'] === PluginUtil::NOTINSTALLED) ? false : true;
    }

//    public function getState()
//    {
//        PluginUtil::getState($this->modVarName, PluginUtil::getDefaultState());
//    }
//
//    public function setState($state, $version = false)
//    {
//        if (!isset($state['state'])) {
//            throw new InvalidArgumentException('State key must be set');
//        }
//        $plugin = PluginUtil::getVar($this->modVarName, PluginUtil::getDefaultState());
//        $plugin['state'] = $state;
//        if ($version) {
//            $plugin['version'] = self::VERSION;
//        }
//
//        $plugin = PluginUtil::getState($this->modVarName, $state);
//    }

    public function preInstall()
    {
        return true;
    }

    public function install()
    {
        return true;
    }

    public function postInstall()
    {
        return true;
    }

    public function preUninstall()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    public function postUninstall()
    {
        return true;
    }

    public function preUpgrade($oldversion)
    {
        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    public function postUpgrade()
    {
        return true;
    }
}