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
 * Zikula_Plugin abstract class.
 */
abstract class Zikula_Plugin extends Zikula_EventHandler
{
    /**
     * Module plugin identifier.
     *
     * @var constant
     */
    const TYPE_MODULE = 1;

    /**
     * Systemwide plugin identifier.
     *
     * @var constant
     */
    const TYPE_SYSTEM = 2;

    /**
     * EventManager.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * ServiceManager.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * Has this plugin booted.
     *
     * @var boolean
     */
    protected $booted = false;

    /**
     * Plugin meta data.
     *
     * @var array
     */
    protected $meta;

    /**
     * Plugin type.
     *
     * @var integer
     */
    protected $pluginType;

    /**
     * Service ID.
     *
     * @var string
     */
    protected $serviceId;

    /**
     * Class name.
     *
     * @var string
     */
    protected $className;

    /**
     * Translation domain.
     *
     * @var string|null
     */
    protected $domain;

    /**
     * Module name.
     *
     * @var string
     */
    protected $moduleName;

    /**
     * Plugin name.
     *
     * @var string
     */
    protected $pluginName;

    /**
     * Gettext capable.
     *
     * @var boolean
     */
    protected $gettextEnabled = true;

    /**
     * Base dir.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * This object's own reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Plugin controller class.
     *
     * @var Zikula_Plugin_Controller
     */
    protected $controllerClass;


    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     * @param Zikula_EventManager   $eventManager   EventManager.
     *
     * @throws LogicException If no metadata is defined.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->serviceManager = $serviceManager;
        $this->_setup();
        if (!$this->getMetaDisplayName() || !$this->getMetaDescription() || !$this->getMetaVersion()) {
            throw new LogicException(sprintf("setMeta() must be defined in %s must and return array('displayname' => 'displayname', 'description' => 'description', 'version' => 'a.b.c')", get_class($this)));
        }
    }

    /**
     * Get this reflection.
     *
     * @return ReflectionObject
     */
    public function getReflection()
    {
        if (!is_null($this->reflection)) {
            return $this->reflection;
        }

        return new ReflectionObject($this);
    }

    /**
     * Internal setup.
     *
     * @throws LogicException If plugin is not named correctly.
     *
     * @return void
     */
    private function _setup()
    {
        $this->className = get_class($this);
        $this->serviceId = PluginUtil::getServiceId($this->className);
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

    /**
     * Get plugin meta data.
     *
     * @return array
     */
    protected function getMeta()
    {
        $meta = array('displayname' => '', // implement as $this->__('Display name'),
                      'description' => '', // implement as $this->__('Description goes here'),
                      'version' => ''      // implement as 'a.b.c'
                );

        return $meta;
    }

    /**
     * Get meta display name.
     *
     * @return string
     */
    final public function getMetaDisplayName()
    {
        return $this->meta['displayname'];
    }

    /**
     * Get meta description.
     *
     * @return string
     */
    final public function getMetaDescription()
    {
        return $this->meta['description'];
    }

    /**
     * Get meta version number.
     *
     * @return string
     */
    final public function getMetaVersion()
    {
        return $this->meta['version'];
    }

    /**
     * Get module info.
     *
     * @return array
     */
    public function getModInfo()
    {
        return $this->modinfo;
    }


    /**
     * Return basedir.
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * Get this plugin type.
     *
     * @return integer
     */
    public function getPluginType()
    {
        return $this->pluginType;
    }

    /**
     * Translate.
     *
     * @param string $msgid String to be translated.
     *
     * @return string
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated.
     * @param string|array $params Args for sprintf().
     *
     * @return string
     */
    protected function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance.
     * @param string $plural   Plural instance.
     * @param string $count    Object count.
     *
     * @return string Translated string.
     */
    protected function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance.
     * @param string       $plu    Plural instance.
     * @param string       $n      Object count.
     * @param string|array $params Sprintf() arguments.
     *
     * @return string
     */
    protected function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

    /**
     * Get this service ID.
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Return the translation domain property.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get module name this belongs to.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Get this plugin name.
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Pre intialise hook.
     *
     * @return void
     */
    public function preInitialize()
    {
    }

    /**
     * Initialize plugin.
     *
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * Post intialise hook.
     *
     * @return void
     */
    public function postInitialize()
    {
    }

    /**
     * Post enable handler.
     *
     * @return void
     */
    public function postEnable()
    {
    }

    /**
     * Post disable handler.
     *
     * @return void
     */
    public function postDisable()
    {
    }

    /**
     * Has booted check.
     *
     * @return boolean
     */
    public function hasBooted()
    {
        return $this->booted;
    }

    /**
     * Flag booted.
     *
     * @return void
     */
    public function setBooted()
    {
        $this->booted = true;
    }

    /**
     * Whether or not the plugin is enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        $plugin = PluginUtil::getState($this->serviceId, PluginUtil::getDefaultState());
        return ($plugin['state'] === PluginUtil::ENABLED) ? true : false;
    }

    /**
     * Whether or not the plugin is installed.
     *
     * @return boolean
     */
    public function isInstalled()
    {
        $plugin = PluginUtil::getState($this->serviceId, PluginUtil::getDefaultState());
        return ($plugin['state'] === PluginUtil::NOTINSTALLED) ? false : true;
    }

    /**
     * Pre install handler.
     *
     * @return boolean
     */
    public function preInstall()
    {
        return true;
    }

    /**
     * Install.
     *
     * @return boolean
     */
    public function install()
    {
        return true;
    }

    /**
     * Post install handler.
     *
     * @return boolean
     */
    public function postInstall()
    {
        return true;
    }

    /**
     * Pre uninstall handler.
     *
     * @return boolean
     */
    public function preUninstall()
    {
        return true;
    }

    /**
     * Uninstall.
     *
     * @return boolean
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * Post uninstall handler.
     *
     * @return boolean
     */
    public function postUninstall()
    {
        return true;
    }

    /**
     * Pre upgrade handler.
     *
     * @param string $oldversion Old version.
     *
     * @return boolean
     */
    public function preUpgrade($oldversion)
    {
        return true;
    }

    /**
     * Upgrade
     *
     * @param string $oldversion Old version.
     *
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        return true;
    }

    /**
     * Post upgrade handler.
     *
     * @return boolean
     */
    public function postUpgrade()
    {
        return true;
    }
}
