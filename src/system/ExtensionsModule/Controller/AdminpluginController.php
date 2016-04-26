<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Controller;

use SecurityUtil;
use PluginUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula_Plugin_ConfigurableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove

/**
 * @Route("/adminplugin")
 *
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
        // Do not setup a view for this controller.
    }

    /**
     * @Route("/dispatch")
     *
     * Dispatch a module view request.
     *
     * @return mixed
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module or
     *                                          if the plugin isn't configurable
     * @throws NotFoundHttpException Thrown if the plugin doesn't have the requested service or action methods
     */
    public function dispatchAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
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
            throw new AccessDeniedException(__f('Plugin "%s" is not configurable', $this->plugin->getMetaDisplayName()));
        }

        $this->pluginController = $this->plugin->getConfigurationController();
        if (!$this->pluginController->getReflection()->hasMethod($action)) {
            throw new NotFoundHttpException();
        }

        return $this->response($this->pluginController->$action());
    }
}
