<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\Translator;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var Translator
     */
    private $translator;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * constructor.
     *
     * @param $translator
     * @param RouterInterface $router
     * @param PermissionApi $permissionApi
     */
    public function __construct($translator, RouterInterface $router, PermissionApi $permissionApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $method = 'get' . ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = array();

        if ($this->permissionApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulaextensionsmodule_module_viewmodulelist'),
                'text' => $this->translator->__('Extension List'),
                'icon' => 'list'
            ];

            $links[] = array(
                'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins'),
                'text' => $this->translator->__('Plugins list'),
                'icon' => 'table',
                'links' => array(
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins'),
                        'text' => $this->translator->__('All')),
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => \PluginUtil::NOTINSTALLED)),
                        'text' => $this->translator->__('Not installed')),
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => \PluginUtil::DISABLED)),
                        'text' => $this->translator->__('Inactive')),
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => \PluginUtil::ENABLED)),
                        'text' => $this->translator->__('Active'))
                ));

            $links[] = array(
                'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('systemplugins' => true)),
                'text' => $this->translator->__('System Plugins'),
                'icon' => 'table',
                'links' => array(
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('systemplugins' => true)),
                        'text' => $this->translator->__('All')),
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('systemplugins' => true, 'state' => \PluginUtil::NOTINSTALLED)),
                        'text' => $this->translator->__('Not installed')),
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('systemplugins' => true, 'state' => \PluginUtil::DISABLED)),
                        'text' => $this->translator->__('Inactive')),
                    array('url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', array('systemplugins' => true, 'state' => \PluginUtil::ENABLED)),
                        'text' => $this->translator->__('Active'))
                ));

            $links[] = array(
                'url' => $this->router->generate('zikulaextensionsmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench');
        }

        return $links;
    }

    /**
     * set the BundleName as required buy the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaExtensionsModule';
    }
}
