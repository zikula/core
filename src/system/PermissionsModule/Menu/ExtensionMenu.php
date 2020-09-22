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

namespace Zikula\PermissionsModule\Menu;

use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('adminAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            $menu->addChild('Permission rules list', [
                'route' => 'zikulapermissionsmodule_permission_listpermissions',
            ])
                ->setLinkAttribute('id', 'permissions_view')
                ->setAttribute('icon', 'fas fa-list')
            ;
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('Create new permission rule', [
                'uri' => '#',
            ])
                ->setLinkAttribute('class', 'create-new-permission')
                ->setAttribute('icon', 'fas fa-plus')
            ;
        }
        $menu->addChild('Permission rules information', [
            'uri' => '#',
        ])
            ->setLinkAttribute('class', 'view-instance-info')
            ->setAttribute('icon', 'fas fa-info')
        ;
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulapermissionsmodule_config_config',
            ])
                ->setLinkAttribute('id', 'permissions_modifyconfig')
                ->setAttribute('icon', 'fas fa-wrench')
            ;
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaPermissionsModule';
    }
}
