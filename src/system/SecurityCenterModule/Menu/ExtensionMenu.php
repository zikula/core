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
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
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

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
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
        $menu = $this->factory->createItem('securityAdminMenu');
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }
        $menu->addChild('Settings', [
            'route' => 'zikulasecuritycentermodule_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');
        $menu->addChild('Allowed HTML settings', [
            'route' => 'zikulasecuritycentermodule_config_allowedhtml',
        ])->setAttribute('icon', 'fas fa-list');
        $menu->addChild('View IDS log', [
            'route' => 'zikulasecuritycentermodule_idslog_view',
        ])->setAttribute('icon', 'fas fa-clipboard-list')
        ->setAttribute('class', 'align-justify')
            ->setAttribute('dropdown', true);
        $menu['View IDS log']->addChild('View IDS log', [
            'route' => 'zikulasecuritycentermodule_idslog_view'
        ]);
        $menu['View IDS log']->addChild('Export IDS log', [
            'route' => 'zikulasecuritycentermodule_idslog_view'
        ]);
        $menu['View IDS log']->addChild('Purge IDS log', [
            'route' => 'zikulasecuritycentermodule_idslog_purge'
        ]);

        $outputfilter = $this->variableApi->getSystemVar('outputfilter');
        if (1 === $outputfilter) {
            $menu->addChild('HTMLPurifier settings', [
                'route' => 'zikulasecuritycentermodule_config_purifierconfig',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSecurityCenterModule';
    }
}
