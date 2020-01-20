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

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . ':route:', '::', ACCESS_ADMIN)) {
            return null;
        }
        $menu = $this->factory->createItem('routesAdminMenu');
        $menu->addChild('Routes', [
            'route' => 'zikularoutesmodule_route_adminview',
        ])->setAttribute('icon', 'fas fa-list')
            ->setLinkAttribute('title', 'Route list');
        $menu->addChild('Reload routes', [
            'route' => 'zikularoutesmodule_update_reload',
        ])->setAttribute('icon', 'fas fa-sync-alt')
            ->setLinkAttribute('title', 'Reload routes');
        $menu->addChild('Reload multilingual routing settings', [
            'route' => 'zikularoutesmodule_update_renew',
        ])->setAttribute('icon', 'fas fa-sync-alt')
            ->setLinkAttribute('title', 'Reload multilingual routing settings');
        $menu->addChild('Dump exposed js routes to file', [
            'route' => 'zikularoutesmodule_update_dumpjsroutes',
        ])->setAttribute('icon', 'fas fa-file')
            ->setLinkAttribute('title', 'Dump exposed js routes to file');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Configuration', [
                'route' => 'zikularoutesmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench')
                ->setLinkAttribute('title', 'Manage settings for this application');
        }

        return $menu->count() === 0 ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaRoutesModule';
    }
}
