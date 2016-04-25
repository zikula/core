<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Container;

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
        $links = [];

        if ($this->permissionApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            $links[] = [
                'url' => $this->router->generate('zikulablocksmodule_admin_view'),
                'text' => $this->translator->__('Blocks list'),
                'icon' => 'table'
            ];
        }

        if ($this->permissionApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADD)) {
            $links[] = [
                'url' => $this->router->generate('zikulablocksmodule_block_new'),
                'text' => $this->translator->__('Create new block'),
                'icon' => 'plus'
            ];
        }
        if ($this->permissionApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADD)) {
            $links[] = [
                'url' => $this->router->generate('zikulablocksmodule_position_edit'),
                'text' => $this->translator->__('Create new block position'),
                'icon' => 'plus'
            ];
        }
        if ($this->permissionApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulablocksmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
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
        return 'ZikulaBlocksModule';
    }
}
