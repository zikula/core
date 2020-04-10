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

namespace Zikula\BootstrapTheme\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class MenuBuilder
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

    public function createAdminMenu(array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('bootstrapThemeAdminMenu');
        $menu->setChildrenAttribute('class', 'navbar-nav');
        $menu->addChild('Home', ['route' => 'home']);
        if ($this->permissionApi->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', ['route' => 'zikulasettingsmodule_settings_main']);
        }
        if ($this->permissionApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Extensions', ['route' => 'zikulaextensionsmodule_extension_list']);
        }
        if ($this->permissionApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Blocks', ['route' => 'zikulablocksmodule_admin_view']);
        }
        if ($this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $menu->addChild('Users', ['route' => 'zikulausersmodule_useradministration_list']);
        }
        if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Groups', ['route' => 'zikulagroupsmodule_group_adminlist']);
        }
        if ($this->permissionApi->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Permissions', ['route' => 'zikulapermissionsmodule_permission_list']);
        }
        if ($this->permissionApi->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Themes', ['route' => 'zikulathememodule_config_config']);
        }
        $menu->addChild('Log out', ['route' => 'zikulausersmodule_access_logout'])->setAttribute('icon', 'fas fa-sign-out-alt');

        return $menu;
    }
}
