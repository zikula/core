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

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use Zikula\UsersBundle\ProfileBundle\ProfileBundleCollector;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(private readonly ProfileBundleCollector $profileBundleCollector)
    {
    }

    protected function getAdmin(): iterable
    {
        yield MenuItem::linktoRoute('Property list', 'fas fa-list', 'zikulaprofilebundle_property_listproperties')
            ->setPermission('ROLE_EDITOR');
        yield MenuItem::linktoRoute('Create new property', 'fas fa-plus', 'zikulaprofilebundle_property_edit')
            ->setPermission('ROLE_EDITOR');
    }

    protected function getUser(): iterable
    {
        yield MenuItem::linktoRoute('Account menu', 'fas fa-user-circle', 'zikulausersbundle_account_menu')
            ->setPermission('IS_AUTHENTICATED');
        yield MenuItem::linktoRoute('Display profile', 'fas fa-user', 'zikulaprofilebundle_profile_display')
            ->setPermission('IS_AUTHENTICATED');
        yield MenuItem::linktoRoute('Edit profile', 'fas fa-user-pen', 'zikulaprofilebundle_profile_edit')
            ->setPermission('IS_AUTHENTICATED');
    }

    protected function getAccount(): iterable
    {
        // do not show any account links if Profile is not the Profile manager
        if ($this->profileBundleCollector->getSelectedName() !== $this->getBundleName()) {
            return null;
        }

        yield MenuItem::linktoRoute('Profile', 'fas fa-user', 'zikulaprofilebundle_profile_display', [
            'uid' => $this->currentUserApi->get('uid'),
        ])
            ->setPermission('IS_AUTHENTICATED');
    }

    public function getBundleName(): string
    {
        return 'ZikulaProfileBundle';
    }
}
