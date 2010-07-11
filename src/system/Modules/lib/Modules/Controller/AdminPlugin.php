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
 * Modules_Plugin controller.
 */
class Modules_Controller_AdminPlugin extends Zikula_Controller
{
    /**
     * Plugin instance.
     *
     * @var Zikula_Plugin
     */
    protected $plugin;

    /**
     * Plugin controller instance.
     *
     * @var Zikula_Plugin_Controller
     */
    protected $pluginController;

    /**
     * Dispatch a module view request.
     *
     * @return mixed
     */
    public function dispatch()
    {
        if (!SecurityUtil::checkPermission('Modules::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get input.
        $moduleName = $this->getInput('_module', null, 'GET', FILTER_SANITIZE_STRING);
        $pluginName = $this->getInput('_plugin', null, 'GET', FILTER_SANITIZE_STRING);
        $action = $this->getInput('_action', null, 'GET', FILTER_SANITIZE_STRING);

        // Load plugins.
        if (!$moduleName) {
            $type = 'SystemPlugin';
            PluginUtil::loadAllSystemPlugins();
        } else {
            $type = 'ModulePlugin';
            PluginUtil::loadAllModulePlugins();
        }
        
        if ($moduleName) {
            $serviceId = PluginUtil::getServiceId("{$type}_{$moduleName}_{$pluginName}_Plugin");
        } else {
            $serviceId = PluginUtil::getServiceId("{$type}_{$pluginName}_Plugin");
        }
        
        $this->throwNotFoundUnless($this->serviceManager->hasService($serviceId));

        $this->plugin = $this->serviceManager->getService($serviceId);

        // Sanity checks.
        $this->throwNotFoundUnless($this->plugin->isInstalled(), __f('Plugin "%s" is not installed', $this->plugin->getMetaDisplayName()));
        $this->throwForbiddenUnless($this->plugin instanceof Zikula_Plugin_Configurable, __f('Plugin "%s" is not configurable', $this->plugin->getMetaDisplayName()));

        $this->pluginController = $this->plugin->getConfigurationController();
        $this->throwNotFoundUnless($this->pluginController->getReflection()->hasMethod($action));

        return $this->pluginController->$action();
    }
}