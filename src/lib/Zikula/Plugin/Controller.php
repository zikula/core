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
 * Zikula_Plugin_Controller class.
 */
class Zikula_Plugin_Controller extends Zikula_Controller
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
     * @var Zikula_Plugin
     */
    protected $plugin;

    /**
     * (non-PHPdoc)
     * @see Zikula_Base::_setupLanguageDomain()
     */
    protected function _setupLanguageDomain()
    {
        $this->domain = $this->plugin->getDomain();
    }

    /**
     * Setup base properties.
     *
     * @return void
     */
    protected function _setup()
    {
        $this->reflection = new ReflectionObject($this);
        $parts = explode('_', $this->reflection->getName());
        $this->name = $parts[0];
        $this->baseDir = $this->plugin->getBaseDir();
        $this->pluginName = $this->plugin->getPluginName();
        $this->moduleName = $this->plugin->getModuleName();
        $this->modinfo = $this->plugin->getModInfo();
        if ($this->plugin->getPluginType() == Zikula_Plugin::TYPE_SYSTEM) {
            $this->systemBaseDir = realpath("{$this->baseDir}/../..");
            $this->libBaseDir = realpath("{$this->baseDir}/plugins/{$this->pluginName}/lib/{$this->pluginName}");
        } else {
            $modbase = ($this->modinfo['type'] == Zikula_Plugin::TYPE_MODULE) ? 'modules' : 'system';
            $this->systemBaseDir = realpath("{$this->baseDir}/$modbase/..");
            $this->baseDir = realpath("{$this->systemBaseDir}/$modbase/{$this->moduleName}/plugins/{$this->pluginName}");
            $this->libBaseDir = realpath("{$this->baseDir}/lib/{$this->pluginName}");
        }
    }

    /**
     * Set renderer property.
     *
     * @param PluginRender $renderer Default null means new Render instance for this module name.
     *
     * @return Zikula_Plugin_Controller
     */
    protected function setView(Zikula_View_Plugin $view = null)
    {
        if (is_null($view)) {
            if ($this->plugin->getPluginType() == Zikula_Plugin::TYPE_MODULE) {
                $view = Zikula_View_Plugin::getModulePluginInstance($this->moduleName, $this->pluginName);
            } else {
                $view = Zikula_View_Plugin::getSystemPluginInstance($this->pluginName);
            }
        }

        $this->view = $view;

        return $this;
    }

    /**
     * Set plugin for this controller.
     *
     * @param Zikula_Plugin $plugin Plugin instance.
     *
     * @return void
     */
    public function setPlugin(Zikula_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}