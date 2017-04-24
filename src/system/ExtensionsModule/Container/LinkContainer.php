<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * LinkContainer constructor.
     *
     * @param TranslatorInterface $translator    TranslatorInterface service instance
     * @param RouterInterface     $router        RouterInterface service instance
     * @param PermissionApiInterface $permissionApi PermissionApi service instance
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, PermissionApiInterface $permissionApi)
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
        $links = [];

        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return $links;
        }

        $links[] = [
            'url' => $this->router->generate('zikulaextensionsmodule_module_viewmodulelist'),
            'text' => $this->translator->__('Extension List'),
            'icon' => 'list'
        ];

        $links[] = [
            'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins'),
            'text' => $this->translator->__('Plugins list'),
            'icon' => 'table',
            'links' => [
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins'),
                    'text' => $this->translator->__('All')
                ],
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['state' => \PluginUtil::NOTINSTALLED]),
                    'text' => $this->translator->__('Not installed')
                ],
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['state' => \PluginUtil::DISABLED]),
                    'text' => $this->translator->__('Inactive')
                ],
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['state' => \PluginUtil::ENABLED]),
                    'text' => $this->translator->__('Active')
                ]
            ]
        ];

        $links[] = [
            'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['systemplugins' => true]),
            'text' => $this->translator->__('System Plugins'),
            'icon' => 'table',
            'links' => [
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['systemplugins' => true]),
                    'text' => $this->translator->__('All')
                ],
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['systemplugins' => true, 'state' => \PluginUtil::NOTINSTALLED]),
                    'text' => $this->translator->__('Not installed')
                ],
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['systemplugins' => true, 'state' => \PluginUtil::DISABLED]),
                    'text' => $this->translator->__('Inactive')
                ],
                [
                    'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', ['systemplugins' => true, 'state' => \PluginUtil::ENABLED]),
                    'text' => $this->translator->__('Active')
                ]
            ]
        ];

        $links[] = [
            'url' => $this->router->generate('zikulaextensionsmodule_config_config'),
            'text' => $this->translator->__('Settings'),
            'icon' => 'wrench'
        ];

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaExtensionsModule';
    }
}
