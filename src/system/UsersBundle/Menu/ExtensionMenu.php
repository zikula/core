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

namespace Zikula\UsersBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\UsersConstant;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly LocaleApiInterface $localeApi,
        private readonly RegistrationHelper $registrationHelper,
        private readonly bool $allowSelfDeletion
    ) {
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
                'route' => 'zikulausersbundle_useradministration_listusers',
            ])->setAttribute('icon', 'fas fa-list');
            $menu->addChild('Export users', [
                'route' => 'zikulausersbundle_fileio_export',
            ])->setAttribute('icon', 'fas fa-download');
            $menu->addChild('Find & Mail|Delete users', [
                'route' => 'zikulausersbundle_useradministration_search',
            ])->setAttribute('icon', 'fas fa-search');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('usersAccountMenu');
        if (!$this->currentUserApi->isLoggedIn()) {
            $menu->addChild('Login', [
                'label' => 'I would like to login',
                'route' => 'zikulausersbundle_access_login',
            ])->setAttribute('icon', 'fas fa-sign-in-alt');
            if ($this->registrationHelper->isRegistrationEnabled()) {
                $menu->addChild('New account', [
                    'label' => 'I would like to create a new account',
                    'route' => 'zikulausersbundle_registration_register',
                ])->setAttribute('icon', 'fas fa-plus');
            }
        } else {
            if ($this->localeApi->multilingual()) {
                $locales = $this->localeApi->getSupportedLocales();
                if (1 < count($locales)) {
                    $menu->addChild('Language switcher', [
                        'route' => 'zikulausersbundle_account_changelanguage',
                    ])->setAttribute('icon', 'fas fa-language');
                }
            }
            if ($this->allowSelfDeletion) {
                if (UsersConstant::USER_ID_ADMIN !== $this->currentUserApi->get('uid')) {
                    $menu->addChild('Delete my account', [
                        'route' => 'zikulausersbundle_account_deletemyaccount',
                    ])->setAttribute('icon', 'fas fa-trash-alt')
                        ->setLinkAttribute('class', 'text-danger');
                }
            }
            $menu->addChild('Log out', [
                'route' => 'zikulausersbundle_access_logout',
            ])->setAttribute('icon', 'fas fa-power-off')
                ->setLinkAttribute('class', 'text-danger');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('usersUserMenu');

        if (!$this->currentUserApi->isLoggedIn()) {
            $menu->addChild('Help', [
                'route' => 'zikulausersbundle_account_menu',
            ])->setAttribute('icon', 'text-danger fas fa-ambulance');
            if ($this->registrationHelper->isRegistrationEnabled()) {
                $menu->addChild('New account', [
                    'route' => 'zikulausersbundle_registration_register',
                ])->setAttribute('icon', 'fas fa-plus');
            }
        } else {
            $menu->addChild('Account menu', [
                'route' => 'zikulausersbundle_account_menu',
            ])->setAttribute('icon', 'fas fa-user-circle');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersBundle';
    }
}
