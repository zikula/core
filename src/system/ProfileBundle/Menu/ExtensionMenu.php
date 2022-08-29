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
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Collector\ProfileBundleCollector;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\ZAuthConstant;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        protected readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly ProfileBundleCollector $profileBundleCollector
    ) {
    }

    protected function getAdmin(): iterable
    {
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            yield MenuItem::linktoRoute('Property list', 'fas fa-list', 'zikulaprofilebundle_property_listproperties');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            yield MenuItem::linktoRoute('Create new property', 'fas fa-plus', 'zikulaprofilebundle_property_edit');
        }
    }

    protected function getUser(): iterable
    {
        if (!$this->currentUserApi->isLoggedIn()) {
            return;
        }

        if ($this->permissionApi->hasPermission('ZikulaUsersBundle::', '::', ACCESS_READ)) {
            yield MenuItem::linktoRoute('Account menu', 'fas fa-user-circle', 'zikulausersbundle_account_menu');
        }

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            yield MenuItem::linktoRoute('Display profile', 'fas fa-user', 'zikulaprofilebundle_profile_display');
            yield MenuItem::linktoRoute('Edit profile', 'fas fa-user-pen', 'zikulaprofilebundle_profile_edit');

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
                yield MenuItem::linktoRoute('Change email address', 'fas fa-envelope', 'zikulazauthbundle_account_changeemail');
                yield MenuItem::linktoRoute('Change password', 'fas fa-key', 'zikulazauthbundle_account_changepassword');
            }
        }
    }

    protected function getAccount(): iterable
    {
        // do not show any account links if Profile is not the Profile manager
        if ($this->profileBundleCollector->getSelectedName() !== $this->getBundleName()) {
            return null;
        }

        if (!$this->currentUserApi->isLoggedIn()) {
            return null;
        }

        yield MenuItem::linktoRoute('Profile', 'fas fa-user', 'zikulaprofilebundle_profile_display', [
            'uid' => $this->currentUserApi->get('uid'),
        ]);
    }

    public function getBundleName(): string
    {
        return 'ZikulaProfileBundle';
    }
}
