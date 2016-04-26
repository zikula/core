<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_AbstractPlugin abstract class.
 *
 * @deprecated
 */
abstract class Zikula_AbstractPlugin extends Zikula_AbstractEventHandler implements Zikula_TranslatableInterface
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
     * Base dir.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * Module info.
     *
     * @var array
     */
    protected $modinfo;

    /**
     * This object's own reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Plugin controller class.
     *
     * @var Zikula_Controller_AbstractPlugin
     */
    protected $controllerClass;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     *
     * @throws InvalidArgumentException If getMeta() is not implemented correctly.
     */
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->get('event_dispatcher');
        $this->_setup();

        $meta = $this->getMeta();
        if (!isset($meta['displayname']) && !isset($meta['description']) && !isset($meta['version'])) {
            throw new InvalidArgumentException(sprintf('%s->getMeta() must be implemented according to the abstract.  See docblock in Zikula_AbstractPlugin for details', get_class($this)));
        }

        // Load any handlers if they exist
        if ($this->getReflection()->hasMethod('setupHandlerDefinitions')) {
            $this->setupHandlerDefinitions();
        }
    }

    /**
     * Optional setup of handler definitions.
     *
     * Overriding the Zikula_AbstractEventHandler interface which required this.
     *
     * <samp>
     *    $this->addHandlerDefinition('some.event', 'handler', 10);
     *    $this->addHandlerDefinition('some.event', 'handler2', 10);
     * </samp>
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
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

        $this->reflection = new ReflectionObject($this);

        return $this->reflection;
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

        // Split class name into parts - commented in if statement below.
        $separator = (false === strpos($this->className, '_')) ? '\\' : '_';
        $p = explode($separator, $this->className);

        if (strpos($this->serviceId, 'moduleplugin') === 0) {
            // ModulePlugin_{ModuleName}_{PluginName}_Plugin
            // $p[1] = ModuleName, $p[2] = PluginName
            $this->moduleName = $p[1];
            $this->pluginName = $p[2];
            $this->pluginType = self::TYPE_MODULE;
            $this->domain = ZLanguage::getModulePluginDomain($this->moduleName, $this->pluginName);
            ZLanguage::bindModulePluginDomain($this->moduleName, $this->pluginName);
        } elseif (strpos($this->serviceId, 'systemplugin') === 0) {
            // SystemPlugin_{PluginName}_Plugin
            // $p[1] = ModuleName
            $this->moduleName = 'zikula';
            $this->pluginName = $p[1];
            $this->pluginType = self::TYPE_SYSTEM;
            $this->domain = ZLanguage::getSystemPluginDomain($this->pluginName);
            ZLanguage::bindSystemPluginDomain($this->pluginName);
        } else {
            throw new LogicException(sprintf('This class %s does not appear to be named correctly.  System plugins should be named {SystemPlugin}_{Name}_Plugin, module plugins should be named {ModulePlugin}_{ModuleName}_{PluginName}_Plugin.', $this->className));
        }

        $this->meta = $this->getMeta();
    }

    /**
     * Get plugin meta data.
     *
     * Should return an array like this:
     * <sample>
     * $meta = [
     *     'displayname' => $this->__('Display name'),
     *     'description' => $this->__('Description goes here'),
     *     'version' => '1.0.0'
     * ];
     *
     * return $meta;
     * </sample>
     *
     * @return array
     */
    abstract protected function getMeta();

    /**
     * Get meta display name.
     *
     * @return string
     */
    public function getMetaDisplayName()
    {
        return $this->meta['displayname'];
    }

    /**
     * Get meta description.
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->meta['description'];
    }

    /**
     * Get meta version number.
     *
     * @return string
     */
    public function getMetaVersion()
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
        if (!$this->modinfo) {
            // this is deliberate lazy load for dependency.
            $this->modinfo = ModUtil::getInfoFromName($this->moduleName);
        }

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
    public function __f($msgid, $params)
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
    public function _n($singular, $plural, $count)
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
    public function _fn($sin, $plu, $n, $params)
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
        if ($this instanceof Zikula_Plugin_AlwaysOnInterface) {
            return true;
        }

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
        if ($this instanceof Zikula_Plugin_AlwaysOnInterface) {
            return true;
        }

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
