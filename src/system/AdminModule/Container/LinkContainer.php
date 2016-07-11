<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Container;

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
     * LinkContainer constructor.
     *
     * @param Translator      $translator    Translator service instance.
     * @param RouterInterface $router        RouterInterface service instance.
     * @param PermissionApi   $permissionApi PermissionApi service instance.
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
        $links = [];

        if ($this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_READ)) {
            $links[] = [
                'url' => $this->router->generate('zikulaadminmodule_admin_view'),
                'text' => $this->translator->__('Module categories list'),
                'icon' => 'list'
            ];
        }
        if ($this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADD)) {
            $links[] = [
                'url' => $this->router->generate('zikulaadminmodule_admin_newcat'),
                'text' => $this->translator->__('Create new module category'),
                'icon' => 'plus'
            ];
        }
        if ($this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulaadminmodule_admin_help'),
                'text' => $this->translator->__('Help'),
                'icon' => 'info'
            ];
            $links[] = [
                'url' => $this->router->generate('zikulaadminmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    /**
     * get the Account links for this extension
     *
     * @return array
     */
    private function getAccount()
    {
        $links = [];

        if ($this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulaadminmodule_admin_adminpanel'),
                'text' => $this->translator->__('Administration panel'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaAdminModule';
    }
}
