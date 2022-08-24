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

namespace Zikula\DefaultThemeBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;

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
        if ($this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_MODERATE)) {
            $menu->addChild('Admin area', ['route' => 'zikulaadminbundle_admin_view']);
        }
        if ($this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $menu->addChild('Users', ['route' => 'zikulausersbundle_useradministration_listusers']);
        }
        if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Groups', ['route' => 'zikulagroupsbundle_group_adminlist']);
        }
        if ($this->permissionApi->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Permissions', ['route' => 'zikulapermissionsbundle_permission_listpermissions']);
        }
        if ($this->permissionApi->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            $menu->addChild('Themes', ['route' => 'zikulathemebundle_config_config']);
        }
        if (!$this->currentUserApi->isLoggedIn()) {
            $menu->addChild('Log in', ['route' => 'zikulausersbundle_access_login'])->setAttribute('icon', 'fas fa-sign-in-alt');
        } else {
            $menu->addChild('Log out', ['route' => 'zikulausersbundle_access_logout'])->setAttribute('icon', 'fas fa-sign-out-alt');
        }

        return $menu;
    }
}
