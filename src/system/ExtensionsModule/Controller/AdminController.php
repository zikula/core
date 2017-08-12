<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Controller;

use Zikula_View;
use SecurityUtil;
use PluginUtil;
use Zikula_Plugin_AlwaysOnInterface;
use Zikula_Plugin_ConfigurableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\RouterInterface;

/**
 * No need for a route prefix, as there isn't a user controller.
 *
 * Administrative controllers for the extensions module
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * @Route("")
     *
     * Extensions Module main admin function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_module_viewmodulelist', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * Extensions Module main admin function
     *
     * @deprecated since 1.4.0 use indexAction instead
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_module_viewmodulelist', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins")
     *
     * Lists all plugins.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function viewPluginsAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        $this->view->assign('state', $state);

        // generate an auth key to use in urls
        $csrfToken = SecurityUtil::generateCsrfToken($this->getContainer(), true);
        $plugins = [];
        $pluginClasses = ($systemplugins) ? PluginUtil::loadAllSystemPlugins() : PluginUtil::loadAllModulePlugins();

        foreach ($pluginClasses as $className) {
            $instance = PluginUtil::loadPlugin($className);
            $pluginstate = PluginUtil::getState($instance->getServiceId(), PluginUtil::getDefaultState());

            // Tweak UI if the plugin is AlwaysOn
            if ($instance instanceof Zikula_Plugin_AlwaysOnInterface) {
                $pluginstate['state'] = PluginUtil::ENABLED;
                $pluginstate['version'] = $instance->getMetaVersion();
            }

            // state filer
            if ($state >= 0 && $pluginstate['state'] != $state) {
                continue;
            }

            // module filter
            if (!empty($module) && $instance->getModuleName() != $module) {
                continue;
            }

            $actions = [];
            // Translate state
            switch ($pluginstate['state']) {
                case PluginUtil::NOTINSTALLED:
                    $status = $this->__('Not installed');
                    $statusclass = 'danger';

                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_initialiseplugin', [
                                    'plugin' => $className,
                                    'state'  => $state,
                                    'bymodule' => $module,
                                    'sort'   => $sort,
                                    'systemplugins' => $systemplugins,
                                    'csrftoken' => $csrfToken
                        ]),
                        'image' => 'cog fa-lg text-success',
                        'color' => '#0c0',
                        'title' => $this->__('Install')
                    ];

                    break;
                case PluginUtil::ENABLED:
                    $status = $this->__('Active');
                    $statusclass = 'success';
                    $pluginLink = [];
                    if (!$systemplugins) {
                        $pluginLink['_module'] = $instance->getModuleName();
                    }
                    $pluginLink['_plugin'] = $instance->getPluginName();
                    $pluginLink['_action'] = 'configure';

                    if ($instance instanceof Zikula_Plugin_ConfigurableInterface) {
                        $actions[] = [
                            'url' => $this->get('router')->generate('zikulaextensionsmodule_adminplugin_dispatch', $pluginLink),
                            'image' => 'wrench fa-lg',
                            'color' => '#111',
                            'title' => $this->__('Configure plugin')
                        ];
                    }

                    // Dont allow to disable/uninstall plugins that are AlwaysOn
                    if (!$instance instanceof Zikula_Plugin_AlwaysOnInterface) {
                        $actions[] = [
                            'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_deactivateplugin', [
                                        'plugin' => $className,
                                        'state'  => $state,
                                        'bymodule' => $module,
                                        'sort'   => $sort,
                                        'systemplugins' => $systemplugins,
                                        'csrftoken' => $csrfToken
                            ]),
                            'image' => 'minus-circle fa-lg text-danger',
                            'color' => '#c00',
                            'title' => $this->__('Deactivate')
                        ];

                        $actions[] = [
                            'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_removeplugin', [
                                        'plugin' => $className,
                                        'state'  => $state,
                                        'bymodule' => $module,
                                        'sort'   => $sort,
                                        'systemplugins' => $systemplugins,
                                        'csrftoken' => $csrfToken
                            ]),
                            'image' => 'trash-o fa-lg',
                            'color' => '#c00',
                            'title' => $this->__('Remove plugin')
                        ];
                    }

                    break;
                case PluginUtil::DISABLED:
                    $status = $this->__('Inactive');
                    $statusclass = 'warning';

                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_activateplugin', [
                                        'plugin' => $className,
                                        'state'  => $state,
                                        'bymodule' => $module,
                                        'sort'   => $sort,
                                        'systemplugins' => $systemplugins,
                                        'csrftoken' => $csrfToken
                        ]),
                        'image' => 'plus-square fa-lg text-success',
                        'color' => '#0c0',
                        'title' => $this->__('Activate')
                    ];

                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_removeplugin', [
                                        'plugin' => $className,
                                        'state' => $state,
                                        'bymodule' => $module,
                                        'sort'   => $sort,
                                        'systemplugins' => $systemplugins,
                                        'csrftoken' => $csrfToken
                        ]),
                        'image' => 'trash-o fa-lg',
                        'color' => '#c00',
                        'title' => $this->__('Remove plugin')
                    ];

                    break;
            }

            // upgrade ?
            if ($pluginstate['state'] != PluginUtil::NOTINSTALLED
                && $pluginstate['version'] != $instance->getMetaVersion()) {
                $status = $this->__('New version');
                $statusclass = 'danger';

                $actions = [];
                $actions[] = [
                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_upgradeplugin', [
                                        'plugin' => $className,
                                        'state'  => $state,
                                        'bymodule' => $module,
                                        'sort'   => $sort,
                                        'systemplugins' => $systemplugins,
                                        'csrftoken' => $csrfToken
                    ]),
                    'image' => 'refresh fa-lg',
                    'color' => '#00c',
                    'title' => $this->__('Upgrade')
                ];

                $actions[] = [
                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_removeplugin', [
                                        'plugin' => $className,
                                        'state' => $state,
                                        'bymodule' => $module,
                                        'sort'   => $sort,
                                        'systemplugins' => $systemplugins,
                                        'csrftoken' => $csrfToken
                    ]),
                    'image' => 'trash-o fa-lg',
                    'color' => '#c00',
                    'title' => $this->__('Remove plugin')
                ];
            }

            $info =  [
                'instance'    => $instance,
                'status'      => $status,
                'statusclass' => $statusclass,
                'actions'     => $actions,
                'version'     => $pluginstate['state'] == PluginUtil::NOTINSTALLED ?
                                        $instance->getMetaVersion() : $pluginstate['version']
            ];

            // new version of plugin?
            if ($pluginstate['state'] != PluginUtil::NOTINSTALLED
                && $pluginstate['version'] != $instance->getMetaVersion()) {
                $info['newversion'] = $instance->getMetaVersion();
            }

            $plugins[] = $info;
        }

        // sort plugins array
        if (empty($sort) || $sort == 'module') {
            usort($plugins, [$this, 'viewPluginsSorter_byModule']);
        } elseif ($sort == 'name') {
            usort($plugins, [$this, 'viewPluginsSorter_byName']);
        }

        $this->view->assign('plugins', $plugins)
                   ->assign('module', $module)
                   ->assign('sort', $sort)
                   ->assign('state', $state)
                   ->assign('systemplugins', $systemplugins)
                   ->assign('_type', ($systemplugins) ? 'system' : 'module');

        // Return the output that has been generated by this function
        return new Response($this->view->fetch('Admin/viewPlugins.tpl'));
    }

    /**
     * @Route("/plugins/initialize/{plugin}")
     *
     * Initialise a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function initialisePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::install($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Installed plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', [
            'state' => $state,
            'sort'  => $sort,
            'bymodule' => $module,
            'systemplugins' => $systemplugins], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/deactivate/{plugin}")
     *
     * Deactivate a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function deactivatePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->query->get('csrftoken', false);
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::disable($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Deactivated plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', [
            'state' => $state,
            'sort'  => $sort,
            'bymodule' => $module,
            'systemplugins' => $systemplugins], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/activate/{plugin}")
     *
     * Activate a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function activatePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->query->get('csrftoken', false);
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::enable($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Activated plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', [
            'state' => $state,
            'sort'  => $sort,
            'bymodule' => $module,
            'systemplugins' => $systemplugins], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/remove/{plugin}")
     *
     * Remove a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function removePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::uninstall($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! De-installed plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', [
            'state' => $state,
            'sort'  => $sort,
            'bymodule' => $module,
            'systemplugins' => $systemplugins], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/upgrade/{plugin}")
     *
     * Upgrade a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function upgradePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::upgrade($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Upgraded plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', [
            'state' => $state,
            'sort'  => $sort,
            'bymodule' => $module,
            'systemplugins' => $systemplugins], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * viewPlugins sorter: Sorting by module name
     *
     * @param $a array first item to compare
     * @param $b array second item to compare
     *
     * @return int < 0 if plugin a should be ordered before module b > 0 otherwise
     */
    private function viewPluginsSorter_byModule($a, $b)
    {
        return strcmp($a['instance']->getModuleName(), $b['instance']->getModuleName());
    }

    /**
     * viewPlugins sorter: Sorting by plugin internal name
     *
     * @param $a array first item to compare
     * @param $b array second item to compare
     *
     * @return int < 0 if plugin a should be ordered before module b > 0 otherwise
     */
    private function viewPluginsSorter_byName($a, $b)
    {
        return strcmp($a['instance']->getPluginName(), $b['instance']->getPluginName());
    }
}
