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
 * Zikula_Controller_AbstractPlugin class.
 * @deprecated
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
     * @param Zikula_ServiceManager $serviceManager ServiceManager
     * @param Zikula_AbstractPlugin $plugin         Plugin
     * @param array                 $options        Options
     */
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_AbstractPlugin $plugin, array $options = [])
    {
        @trigger_error('Plugins are deprecated, please use tagged services instead.', E_USER_DEPRECATED);

        $this->plugin = $plugin;
        parent::__construct($serviceManager, null, $options);
    }

    /**
     * Setup base properties.
     *
     * @param $bundle
     *
     * @return void
     */
    protected function _configureBase($bundle = null)
    {
        @trigger_error('Plugins are deprecated, please use tagged services instead.', E_USER_DEPRECATED);

        $this->systemBaseDir = realpath('.');
        $separator = (false === strpos(get_class($this), '_')) ? '\\' : '_';
        $parts = explode($separator, get_class($this));
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
     * @param Zikula_View $view Default null means new Render instance for this module name
     *
     * @return Zikula_Controller_AbstractPlugin
     */
    protected function setView(Zikula_View $view = null)
    {
        @trigger_error('Plugins are deprecated, please use tagged services instead.', E_USER_DEPRECATED);

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
     * @param Zikula_AbstractPlugin $plugin Plugin instance
     *
     * @return void
     */
    public function setPlugin(Zikula_AbstractPlugin $plugin)
    {
        @trigger_error('Plugins are deprecated, please use tagged services instead.', E_USER_DEPRECATED);

        $this->plugin = $plugin;
    }
}
