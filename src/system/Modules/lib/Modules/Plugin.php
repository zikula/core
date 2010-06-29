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
class Modules_Plugin extends Zikula_Controller
{
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
    {class_exists('ModulePlugin_Modules_Example_Plugin');
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

        $plugin = $this->serviceManager->getService($serviceId);

        // Sanity checks.
        $this->registerErrorUnless($plugin->isInstalled(), __f('Plugin "%s" is not installed', $plugin->getMetaDisplayName()));
        $this->throwNotFoundUnless($plugin->getReflection()->hasMethod($action));

        return $plugin->$action();
    }
}