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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\SettingsBundle\SettingsConstant;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Collector\MessageModuleCollector;
use Zikula\UsersBundle\Constant as UsersConstant;
use Zikula\ZAuthBundle\ZAuthConstant;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly VariableApiInterface $variableApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly MessageModuleCollector $messageModuleCollector
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
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulaprofilebundle_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('profileUserMenu');
        if ($this->currentUserApi->isLoggedIn()) {
            if ($this->permissionApi->hasPermission(UsersConstant::MODNAME . '::', '::', ACCESS_READ)) {
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
                    ZAuthConstant::AUTHENTICATION_METHOD_EMAIL
                ];
                if (in_array($authMethod, $zauthMethods)) {
                    $menu['Profile']->addChild('Change email address', [
                        'route' => 'zikulazauthbundle_account_changeemail',
                    ]);
                    $menu['Profile']->addChild('Change password', [
                        'route' => 'zikulazauthbundle_account_changepassword',
                    ]);
                }
            }

            $messageModule = $this->variableApi->getSystemVar(SettingsConstant::SYSTEM_VAR_MESSAGE_MODULE, '');
            if (null !== $messageModule && '' !== $messageModule && $this->kernel->isBundle($messageModule)
                && $this->permissionApi->hasPermission($messageModule . '::', '::', ACCESS_READ)
            ) {
                $menu->addChild('Messages', [
                    'uri' => $this->messageModuleCollector->getSelected()->getInboxUrl(),
                ])->setAttribute('icon', 'fas fa-envelope');
            }
        }

        $component = $this->getBundleName() . ':Members:';
        if ($this->permissionApi->hasPermission($component, '::', ACCESS_READ)) {
            $menu->addChild('Members', [
                'route' => 'zikulaprofilebundle_members_listmembers',
            ])
                ->setAttribute('icon', 'fas fa-users')
                ->setAttribute('dropdown', true)
            ;

            if ($this->permissionApi->hasPermission($component, '::', ACCESS_READ)) {
                $menu['Members']->addChild('Members list', [
                    'route' => 'zikulaprofilebundle_members_listmembers',
                ])->setAttribute('icon', 'fas fa-user-friends');
            }
            if ($this->permissionApi->hasPermission($component . 'recent', '::', ACCESS_READ)) {
                $menu['Members']->addChild('Last %s%% registered users', [
                    'route' => 'zikulaprofilebundle_members_recent',
                ])->setExtra('translation_params', ['%s%%' => $this->variableApi->get($this->getBundleName(), 'recentmembersitemsperpage', 10)])
                    ->setAttribute('icon', 'fas fa-door-open');
            }
            if ($this->permissionApi->hasPermission($component . 'online', '::', ACCESS_READ)) {
                $menu['Members']->addChild('Users online', [
                    'route' => 'zikulaprofilebundle_members_online',
                ])->setAttribute('icon', 'fas fa-user-check');
            }
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('profileAccountMenu');
        // do not show any account links if Profile is not the Profile manager
        $profileModule = $this->variableApi->getSystemVar(SettingsConstant::SYSTEM_VAR_PROFILE_MODULE, '');
        if ($profileModule !== $this->getBundleName()) {
            return null;
        }

        if (!$this->currentUserApi->isLoggedIn()) {
            return null;
        }

        $menu->addChild('Profile', [
            'route' => 'zikulaprofilebundle_profile_display',
            'routeParameters' => ['uid' => $this->currentUserApi->get('uid')]
        ])->setAttribute('icon', 'fas fa-user');

        if ($this->permissionApi->hasPermission($this->getBundleName() . ':Members:', '::', ACCESS_READ)) {
            $menu->addChild('Registered users', [
                'route' => 'zikulaprofilebundle_members_listmembers',
            ])->setAttribute('icon', 'fas fa-user-friends');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaProfileBundle';
    }
}
