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

namespace Zikula\Module\ExtensionsModule\Controller;

use LogUtil;
use SecurityUtil;
use PluginUtil;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula_Plugin_ConfigurableInterface;

/**
 * Extensions_Plugin controller.
 */
class AdminpluginController extends \Zikula_AbstractController
{
    /**
     * Plugin instance.
     *
     * @var \Zikula_AbstractPlugin
     */
    protected $plugin;

    /**
     * Plugin controller instance.
     *
     * @var \Zikula_Controller_AbstractPlugin
     */
    protected $pluginController;

    /**
     * initialise.
     *
     * @return void
     */
    protected function initialize()
    {
        // Do not setupt a view for this controller.
    }

    /**
     * Dispatch a module view request.
     *
     * @return mixed
     */
    public function dispatchAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get input.
        $moduleName = $this->request->query->filter('_module', null, FILTER_SANITIZE_STRING);
        $pluginName = $this->request->query->filter('_plugin', null, FILTER_SANITIZE_STRING);
        $action = $this->request->query->filter('_action', null, FILTER_SANITIZE_STRING);

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

        if (!$this->getContainer()->has($serviceId)) {
            throw new NotFoundHttpException();
        }

        $this->plugin = $this->getContainer()->get($serviceId);

        // Sanity checks.
        if (!$this->plugin instanceof Zikula_Plugin_ConfigurableInterface) {
            throw new AccessDeniedHttpException(__f('Plugin "%s" is not configurable', $this->plugin->getMetaDisplayName()));
        }

        $this->pluginController = $this->plugin->getConfigurationController();
        if (!$this->pluginController->getReflection()->hasMethod($action)) {
            throw new NotFoundHttpException();
        }

        return $this->pluginController->$action();
    }
}
