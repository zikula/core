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

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\UsersConstant;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        protected readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly LocaleApiInterface $localeApi,
        private readonly RegistrationHelper $registrationHelper,
        private readonly bool $allowSelfDeletion
    ) {
    }

    protected function getAdmin(): iterable
    {
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_MODERATE)) {
            yield MenuItem::linktoRoute('Users list', 'fas fa-list', 'zikulausersbundle_useradministration_listusers');
            yield MenuItem::linktoRoute('Export users', 'fas fa-download', 'zikulausersbundle_fileio_export');
            yield MenuItem::linktoRoute('Find & Mail|Delete users', 'fas fa-search', 'zikulausersbundle_useradministration_search');
        }
    }

    protected function getUser(): iterable
    {
        if (!$this->currentUserApi->isLoggedIn()) {
            yield MenuItem::linktoRoute('Help', 'fas fa-ambulance', 'zikulausersbundle_account_menu');
            if ($this->registrationHelper->isRegistrationEnabled()) {
                yield MenuItem::linktoRoute('New account', 'fas fa-plus', 'zikulausersbundle_registration_register');
            }
        } else {
            yield MenuItem::linktoRoute('Account menu', 'fas fa-user-circle', 'zikulausersbundle_account_menu');
        }
    }

    protected function getAccount(): iterable
    {
        if (!$this->currentUserApi->isLoggedIn()) {
            yield MenuItem::linktoRoute('I would like to login', 'fas fa-sign-in-alt', 'zikulausersbundle_access_login');
            if ($this->registrationHelper->isRegistrationEnabled()) {
                yield MenuItem::linktoRoute('I would like to create a new account', 'fas fa-plus', 'zikulausersbundle_registration_register');
            }
        } else {
            if ($this->localeApi->multilingual()) {
                $locales = $this->localeApi->getSupportedLocales();
                if (1 < count($locales)) {
                    yield MenuItem::linktoRoute('Language switcher', 'fas fa-language', 'zikulausersbundle_account_changelanguage');
                }
            }
            if ($this->allowSelfDeletion) {
                if (UsersConstant::USER_ID_ADMIN !== $this->currentUserApi->get('uid')) {
                    yield MenuItem::linktoRoute('Delete my account', 'fas fa-trash-alt', 'zikulausersbundle_account_deletemyaccount')
                        ->setCssClass('text-danger')
                    ;
                }
            }
            yield MenuItem::linktoRoute('Log out', 'fas fa-power-off', 'zikulausersbundle_access_logout')
                ->setCssClass('text-danger')
            ;
        }
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersBundle';
    }
}
