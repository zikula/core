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

abstract class Zikula_Plugin extends Zikula_EventHandler
{
    const TYPE_MODULE = 1;
    const TYPE_SYSTEM = 2;

    protected $eventManager;
    protected $serviceManager;

    protected $booted = false;
    
    protected $meta;
    protected $pluginType;
    protected $serviceId;
    protected $className;
    protected $domain;
    protected $moduleName;
    protected $pluginName;
    protected $gettextEnabled = true;
    protected $baseDir;
    protected $reflection;
    
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->serviceManager = $serviceManager;
        $this->_setup();
        if (!$this->getMetaDisplayName() || !$this->getMetaDescription() || !$this->getMetaVersion()) {
            throw new LogicException(sprintf("setMeta() must be defined in %s must and return array('displayname' => 'displayname', 'description' => 'description', 'version' => 'a.b.c')", get_class($this)));
        }
    }

    public function getReflection()
    {
        if (!is_null($this->reflection)) {
            return $this->reflection;
        }

        return new ReflectionObject($this);
    }

    private function _setup()
    {
        $this->className = get_class($this);
        $this->serviceId = strtolower(str_replace('_', '.', $this->className));
        $this->baseDir = dirname($this->getReflection()->getFileName());
        $p = explode('_', $this->className);
        if (strpos($this->serviceId, 'moduleplugin') === 0) {
            $this->moduleName = $p[1];
            $this->pluginName = $p[2];
            $this->pluginType = self::TYPE_MODULE;
            if ($this->gettextEnabled) {
                $this->domain = ZLanguage::getModulePluginDomain($this->moduleName, $this->pluginName);
                ZLanguage::bindModulePluginDomain($this->moduleName, $this->pluginName);
            }
        } elseif (strpos($this->serviceId, 'systemplugin') === 0) {
            $this->moduleName = 'zikula';
            $this->pluginName = $p[1];
            $this->pluginType = self::TYPE_SYSTEM;
            if ($this->gettextEnabled) {
                $this->domain = ZLanguage::getSystemPluginDomain($this->moduleName, $this->pluginName);
                ZLanguage::bindSystemPluginDomain($this->pluginName);
            }
        } else {
            throw new LogicException(sprintf('This class %s does not appear to be named correctly', $this->className));
        }
        $this->meta = $this->getMeta();
    }

    protected function getMeta()
    {
        return array('displayname' => $this->__(''),
                     'description' => $this->__(''),
                     'version' => ''
                );
    }

    final public function getMetaDisplayName()
    {
        return $this->meta['displayname'];
    }

    final public function getMetaDescription()
    {
        return $this->meta['description'];
    }

    final public function getMetaVersion()
    {
        return $this->meta['version'];
    }

    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    protected function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    protected function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    protected function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function getPluginName()
    {
        return $this->pluginName;
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

    public function hasBooted()
    {
        return $this->booted;
    }

    public function setBooted()
    {
        $this->booted = true;
    }

    public function isEnabled()
    {
        $plugin = PluginUtil::getState($this->serviceId, PluginUtil::getDefaultState());
        return ($plugin['state'] === PluginUtil::ENABLED) ? true : false;
    }

    public function isInstalled()
    {
        $plugin = PluginUtil::getState($this->serviceId, PluginUtil::getDefaultState());
        return ($plugin['state'] === PluginUtil::NOTINSTALLED) ? false : true;
    }

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