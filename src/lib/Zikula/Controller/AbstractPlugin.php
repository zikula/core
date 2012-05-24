<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Controller_AbstractPlugin class.
 */
abstract class Zikula_Controller_AbstractPlugin extends Zikula_AbstractController
{
    /**
     * Plugin name.
     *
     * @var string
     */
    protected $pluginName;

    /**
     * Parent module name.
     *
     * @var string
     */
    protected $moduleName;

    /**
     * Parent plugin instance.
     *
     * @var Zikula_AbstractPlugin
     */
    protected $plugin;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     * @param Zikula_AbstractPlugin $plugin         Plugin.
     * @param array                 $options        Options.
     */
    public function  __construct(Zikula_ServiceManager $serviceManager, Zikula_AbstractPlugin $plugin, array $options = array())
    {
        $this->plugin = $plugin;
        parent::__construct($serviceManager, $options);
    }

    /**
     * Setup base properties.
     *
     * @return void
     */
    protected function _configureBase()
    {
        $this->systemBaseDir = realpath('.');
        $parts = explode('_', get_class($this));
        $this->name = $parts[0];
        $this->baseDir = $this->plugin->getBaseDir();
        $this->pluginName = $this->plugin->getPluginName();
        $this->moduleName = $this->plugin->getModuleName();
        $this->modinfo = $this->plugin->getModInfo();
        if ($this->plugin->getPluginType() == Zikula_AbstractPlugin::TYPE_SYSTEM) {
            $this->libBaseDir = realpath("{$this->baseDir}/plugins/{$this->pluginName}/lib/{$this->pluginName}");
        } else {
            $modbase = ($this->modinfo['type'] == Zikula_AbstractPlugin::TYPE_MODULE) ? 'modules' : 'system';
            $this->baseDir = realpath("{$this->systemBaseDir}/$modbase/{$this->moduleName}/plugins/{$this->pluginName}");
            $this->libBaseDir = realpath("{$this->baseDir}/lib/{$this->pluginName}");
        }
        $this->domain = $this->plugin->getDomain();
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Default null means new Render instance for this module name.
     *
     * @return Zikula_Controller_AbstractPlugin
     */
    protected function setView(Zikula_View $view = null)
    {
        // please note the docblock param signature is deliberately different to the method signature - drak
        if (is_null($view)) {
            if ($this->plugin->getPluginType() == Zikula_AbstractPlugin::TYPE_MODULE) {
                $view = Zikula_View_Plugin::getModulePluginInstance($this->moduleName, $this->pluginName);
            } else {
                $view = Zikula_View_Plugin::getSystemPluginInstance($this->pluginName);
            }
        } else {
            if (!$view instanceof Zikula_View_Plugin) {
                $name = is_object($view) ? get_class($view) : '$view';
                throw new InvalidArgumentException(sprintf('%s must be an instance of Zikula_View_Plugin', $name));
            }
        }

        $this->view = $view;

        return $this;
    }

    /**
     * Set plugin for this controller.
     *
     * @param Zikula_AbstractPlugin $plugin Plugin instance.
     *
     * @return void
     */
    public function setPlugin(Zikula_AbstractPlugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
