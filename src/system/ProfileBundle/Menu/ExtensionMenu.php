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

namespace Zikula\ProfileBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Collector\ProfileBundleCollector;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\ZAuthConstant;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly ProfileBundleCollector $profileBundleCollector
    ) {
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        $method = 'get' . ucfirst(mb_strtolower($type));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('profileAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $menu->addChild('Property list', [
                'route' => 'zikulaprofilebundle_property_listproperties',
            ])->setAttribute('icon', 'fas fa-list');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('Create new property', [
                'route' => 'zikulaprofilebundle_property_edit',
            ])->setAttribute('icon', 'fas fa-plus');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('profileUserMenu');
        if (!$this->currentUserApi->isLoggedIn()) {
            return 0 === $menu->count() ? null : $menu;
        }

        if ($this->permissionApi->hasPermission('ZikulaUsersBundle::', '::', ACCESS_READ)) {
            $menu->addChild('Account menu', [
                'route' => 'zikulausersbundle_account_menu',
            ])->setAttribute('icon', 'fas fa-user-circle');
        }

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            $menu->addChild('Profile', [
                'route' => 'zikulaprofilebundle_profile_display',
            ])->setAttribute('icon', 'fas fa-user');

            $menu['Profile']->addChild('Display profile', [
                'route' => 'zikulaprofilebundle_profile_display',
            ]);
            $menu['Profile']->addChild('Edit profile', [
                'route' => 'zikulaprofilebundle_profile_edit',
            ]);

            $attributes = $this->currentUserApi->get('attributes');
            $authMethod = $attributes->offsetExists(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY)
                ? $attributes->get(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY)->getValue()
                : ZAuthConstant::AUTHENTICATION_METHOD_UNAME
            ;
            $zauthMethods = [
                ZAuthConstant::AUTHENTICATION_METHOD_EITHER,
                ZAuthConstant::AUTHENTICATION_METHOD_UNAME,
                ZAuthConstant::AUTHENTICATION_METHOD_EMAIL,
            ];
            if (in_array($authMethod, $zauthMethods, true)) {
                $menu['Profile']->addChild('Change email address', [
                    'route' => 'zikulazauthbundle_account_changeemail',
                ]);
                $menu['Profile']->addChild('Change password', [
                    'route' => 'zikulazauthbundle_account_changepassword',
                ]);
            }
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('profileAccountMenu');
        // do not show any account links if Profile is not the Profile manager
        if ($this->profileBundleCollector->getSelectedName() !== $this->getBundleName()) {
            return null;
        }

        if (!$this->currentUserApi->isLoggedIn()) {
            return null;
        }

        $menu->addChild('Profile', [
            'route' => 'zikulaprofilebundle_profile_display',
            'routeParameters' => ['uid' => $this->currentUserApi->get('uid')]
        ])->setAttribute('icon', 'fas fa-user');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaProfileBundle';
    }
}
