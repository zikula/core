<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Menu;

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

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (self::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('adminAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            $menu->addChild('Module categories list', [
                'route' => 'zikulaadminmodule_admin_view',
            ])->setAttribute('icon', 'fas fa-list');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('Create new module category', [
                'route' => 'zikulaadminmodule_admin_newcat',
            ])->setAttribute('icon', 'fas fa-plus');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('Settings', [
                'route' => 'zikulaadminmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return $menu->count() === 0 ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('adminAccountMenu');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Administration panel', [
                'route' => 'zikulaadminmodule_admin_adminpanel',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return $menu->count() === 0 ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSecurityCenterModule';
    }
}
