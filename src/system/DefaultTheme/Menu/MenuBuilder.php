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

namespace Zikula\DefaultTheme\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

class MenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi
    ) {
    }

    public function createAdminMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('defaultThemeAdminMenu');
        $menu->setChildrenAttribute('class', 'navbar-nav');
        $menu->addChild('Home', ['route' => 'home']);
        if ($this->permissionApi->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', ['route' => 'zikulasettingsmodule_settings_mainsettings']);
        }
        if ($this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $menu->addChild('Users', ['route' => 'zikulausersmodule_useradministration_listusers']);
        }
        if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Groups', ['route' => 'zikulagroupsmodule_group_adminlist']);
        }
        if ($this->permissionApi->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Permissions', ['route' => 'zikulapermissionsmodule_permission_listpermissions']);
        }
        if ($this->permissionApi->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Themes', ['route' => 'zikulathememodule_config_config']);
        }
        if (!$this->currentUserApi->isLoggedIn()) {
            $menu->addChild('Log in', ['route' => 'zikulausersmodule_access_login'])->setAttribute('icon', 'fas fa-sign-in-alt');
        } else {
            $menu->addChild('Log out', ['route' => 'zikulausersmodule_access_logout'])->setAttribute('icon', 'fas fa-sign-out-alt');
        }

        return $menu;
    }
}
