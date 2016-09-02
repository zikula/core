<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Container;

use Symfony\Component\HttpFoundation\RequestStack;
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
    protected $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * LinkContainer constructor.
     *
     * @param Translator      $translator    Translator service instance
     * @param RouterInterface $router        RouterInterface service instance
     * @param PermissionApi   $permissionApi PermissionApi service instance
     * @param VariableApi     $variableApi   VariableApi service instance
     * @param RequestStack    $requestStack  RequestStack service instance
     */
    public function __construct($translator, RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
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

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            $links[] = [
                'url' => $this->router->generate('zikulacategoriesmodule_admin_view'),
                'text' => $this->translator->__('Categories list'),
                'icon' => 'list'
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $links[] = [
                'url' => $this->router->generate('zikulacategoriesmodule_admin_newcat'),
                'text' => $this->translator->__('Create new category'),
                'icon' => 'plus'
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulacategoriesmodule_admin_editregistry'),
                'text' => $this->translator->__('Category registry'),
                'icon' => 'archive'
            ];
            $links[] = [
                'url' => $this->router->generate('zikulacategoriesmodule_admin_rebuild'),
                'text' => $this->translator->__('Rebuild paths'),
                'icon' => 'refresh'
            ];
            $links[] = [
                'url' => $this->router->generate('zikulacategoriesmodule_admin_preferences'),
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

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT) && $this->variableApi->get($this->getBundleName(), 'allowusercatedit', 0)) {
            $request = $this->requestStack->getCurrentRequest();
            $referer = $request->headers->get('referer');
            if (false === strpos($referer, 'categories')) {
                $request->getSession()->set('categories_referer', $referer);
            }

            $links[] = [
                'url' => $this->router->generate('zikulacategoriesmodule_user_edituser'),
                'text' => $this->translator->__('Categories manager'),
                'icon' => 'server'
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
        return 'ZikulaCategoriesModule';
    }
}
