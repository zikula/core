<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function getBundleName(): string
    {
        return 'ZikulaExtensionsModule';
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN !== $type) {
            return null;
        }

        if (!$this->permissionApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            return null;
        }
        $menu = $this->factory->createItem('extensionsAdminMenu');
        $menu->addChild('Extension List', [
            'route' => 'zikulaextensionsmodule_extension_list',
        ])->setAttribute('icon', 'fas fa-list');
        $menu->addChild('Settings', [
            'route' => 'zikulaextensionsmodule_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');

        return $menu;
    }
}
