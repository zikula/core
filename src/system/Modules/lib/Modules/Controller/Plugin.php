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
class Modules_Controller_Plugin extends Zikula_Controller
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
     * Default action.
     *
     * @return mixed
     */
    public function main()
    {
        return $this->dispatch();
    }

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
        $pluginName = $this->getInput('_name', null, 'GET');
        $type = $this->getInput('_type', null, 'GET');
        $action = $this->getInput('_action', null, 'GET');

        // Load plugins.
        if ($type == 'system') {
            $type = 'SystemPlugin';
            PluginUtil::loadAllSystemPlugins();
        } else if ($type == 'module') {
            $type = 'ModulePlugin';
            PluginUtil::loadAllModulePlugins();
        } else {
            $this->throwNotFound($this->__('Invalid plugin type'));
        }

        $serviceId = PluginUtil::getServiceId("${type}_${pluginName}_Plugin");
        $this->throwNotFoundUnless($this->serviceManager->hasService($serviceId));

        $this->plugin = $this->serviceManager->getService($serviceId);

        // Sanity checks.
        $this->registerErrorUnless($this->plugin->isInstalled(), __f('Plugin "%s" is not installed', $this->plugin->getMetaDisplayName()));

        $this->pluginController = $this->plugin->getController();
        $this->throwNotFoundUnless($this->pluginController->getReflection()->hasMethod($action));
        //$this->view->assign('plugin_content', $this->pluginController->$action());
        //array_push($this->view->plugins_dir, 'system/Admin/templates/plugins');
        //$this->view->load_filter('output', 'admintitle');
        //return $this->view->fetch('modules_plugin_dispatchcontroller.tpl');

        return $this->pluginController->$action();
    }
}