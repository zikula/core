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

namespace Zikula\RoutesBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\RoutesBundle\Helper\ControllerHelper;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        protected readonly FactoryInterface $factory,
        protected readonly PermissionApiInterface $permissionApi
    ) {
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        $permLevel = self::TYPE_ADMIN === $type ? ACCESS_ADMIN : ACCESS_READ;
        if (self::TYPE_ADMIN !== $type) {
            return null;
        }

        if (!$this->permissionApi->hasPermission('ZikulaRoutesBundle::', '::', $permLevel)) {
            return null;
        }

        $menu = $this->factory->createItem('zikularoutesmodule' . ucfirst($type) . 'Menu');
        $menu->addChild('Dump JS routes', [
            'route' => 'zikularoutesbundle_config_dumpjsroutes',
        ])
            ->setAttribute('icon', 'fas fa-file')
            ->setLinkAttribute('title', 'Dump exposed JS routes to file')
        ;

        $menu->addChild('Configuration', [
            'route' => 'zikularoutesbundle_config_config',
        ])
            ->setAttribute('icon', 'fas fa-wrench')
            ->setLinkAttribute('title', 'Manage settings for this application')
        ;

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaRoutesBundle';
    }
}
