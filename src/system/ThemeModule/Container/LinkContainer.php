<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\Translator;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
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
     * @var VariableApi
     */
    private $variableApi;

    /**
     * LinkContainer constructor.
     *
     * @param Translator      $translator    Translator service instance.
     * @param RouterInterface $router        RouterInterface service instance.
     * @param PermissionApi   $permissionApi PermissionApi service instance.
     * @param VariableApi     $variableApi   VariableApi service instance.
     */
    public function __construct($translator, RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
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

        if (!$this->permissionApi->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            return $links;
        }

        $links[] = [
            'url' => $this->router->generate('zikulathememodule_theme_view'),
            'text' => $this->translator->__('Themes list'),
            'icon' => 'list'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulathememodule_admin_modifyconfig'),
            'text' => $this->translator->__('Settings'),
            'icon' => 'wrench'
        ];

        return $links;
    }

    private function getAccount()
    {
        $links = [];

        if ($this->variableApi->get(VariableApi::CONFIG, 'theme_change')) {
            $links[] = [
                'url' => $this->router->generate('zikulathememodule_user_index'),
                'text' => $this->translator->__('Theme switcher'),
                'icon' => 'admin.png'
            ];
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
        return 'ZikulaThemeModule';
    }
}
