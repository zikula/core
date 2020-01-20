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

namespace Zikula\UsersModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;

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

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUser;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        LocaleApiInterface $localeApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->currentUser = $currentUserApi;
        $this->localeApi = $localeApi;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (self::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }
        if (self::TYPE_USER === $type) {
            return $this->getUser();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('usersAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_MODERATE)) {
            $menu->addChild('Users list', [
                'route' => 'zikulausersmodule_useradministration_list',
            ])->setAttribute('icon', 'fas fa-list');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulausersmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
            $menu->addChild('Authentication methods', [
                'route' => 'zikulausersmodule_config_authenticationmethods',
            ])->setAttribute('icon', 'fas fa-lock');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_MODERATE)) {
            $menu->addChild('Export users', [
                'route' => 'zikulausersmodule_fileio_export',
            ])->setAttribute('icon', 'fas fa-download');
            $menu->addChild('Find/Mail/Delete users', [
                'route' => 'zikulausersmodule_useradministration_search',
            ])->setAttribute('icon', 'fas fa-search');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        if (!$this->currentUser->isLoggedIn()) {
            return null;
        }
        $menu = $this->factory->createItem('usersAccountMenu');

        if ($this->variableApi->getSystemVar('multilingual')) {
            $locales = $this->localeApi->getSupportedLocales();
            if (count($locales) > 1) {
                $menu->addChild('Language switcher', [
                    'route' => 'zikulausersmodule_account_changelanguage',
                ])->setAttribute('icon', 'fas fa-language');
            }
        }
        $menu->addChild('Log out', [
            'route' => 'zikulausersmodule_access_logout',
        ])->setAttribute('icon', 'fas fa-power-off')
        ->setAttribute('class', 'text-danger');

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('usersUserMenu');
        $menu->addChild('Account menu', [
            'route' => 'zikulausersmodule_account_menu',
        ])->setAttribute('icon', 'fas fa-user-circle');
        if (!$this->currentUser->isLoggedIn()) {
            $menu->addChild('Log in', [
                'route' => 'zikulausersmodule_access_login',
            ])->setAttribute('icon', 'fas fa-sign-in-alt');
            if ($this->variableApi->get($this->getBundleName(), UsersConstant::MODVAR_REGISTRATION_ENABLED)) {
                $menu->addChild('New account', [
                    'route' => 'zikulausersmodule_registration_register',
                ])->setAttribute('icon', 'fas fa-plus');
            }
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersModule';
    }
}
