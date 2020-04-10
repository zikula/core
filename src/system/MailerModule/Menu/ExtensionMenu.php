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

namespace Zikula\MailerModule\Menu;

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
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }

        $menu = $this->factory->createItem('mailerAdminMenu');
        $menu->addChild('Test current settings', [
            'route' => 'zikulamailermodule_config_test',
        ])->setAttribute('icon', 'fas fa-envelope');
        $menu->addChild('Settings', [
            'route' => 'zikulamailermodule_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaMailerModule';
    }
}
