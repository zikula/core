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

namespace Zikula\RoutesModule\Menu;

use Knp\Menu\ItemInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\RoutesModule\Menu\Base\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN !== $type) {
            return parent::get($type);
        }

        if (!$this->permissionHelper->hasPermission(ACCESS_ADMIN)) {
            return null;
        }

        $menu = $this->factory->createItem('zikularoutesmodule' . ucfirst($type) . 'Menu');
        $menu->addChild('Routes', [
            'route' => 'zikularoutesmodule_route_adminview',
        ])
            ->setAttribute('icon', 'fas fa-list')
            ->setLinkAttribute('title', 'Route list')
        ;
        $menu->addChild('Reload routes', [
            'route' => 'zikularoutesmodule_update_reload',
        ])
            ->setAttribute('icon', 'fas fa-sync-alt')
            ->setLinkAttribute('title', 'Reload routes')
        ;
        $menu->addChild('Reload multilingual routing settings', [
            'route' => 'zikularoutesmodule_update_renew',
        ])
            ->setAttribute('icon', 'fas fa-sync-alt')
            ->setLinkAttribute('title', 'Reload multilingual routing settings')
        ;
        $menu->addChild('Dump exposed js routes to file', [
            'route' => 'zikularoutesmodule_update_dumpjsroutes',
        ])
            ->setAttribute('icon', 'fas fa-file')
            ->setLinkAttribute('title', 'Dump exposed js routes to file')
        ;

        $menu->addChild('Configuration', [
            'route' => 'zikularoutesmodule_config_config',
        ])
            ->setAttribute('icon', 'fas fa-wrench')
            ->setLinkAttribute('title', 'Manage settings for this application')
        ;

        return 0 === $menu->count() ? null : $menu;
    }
}
