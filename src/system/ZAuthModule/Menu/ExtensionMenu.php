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

namespace Zikula\ZAuthModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

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
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        AuthenticationMappingRepositoryInterface $mappingRepository
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->currentUser = $currentUserApi;
        $this->mappingRepository = $mappingRepository;
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
        $menu = $this->factory->createItem('zauthAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Users list', [
                'route' => 'zikulazauthmodule_useradministration_list',
            ])->setAttribute('icon', 'fas fa-list');
        }
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ENABLED)) {
            $createUserAccessLevel = ACCESS_ADD;
        } else {
            $createUserAccessLevel = ACCESS_ADMIN;
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', $createUserAccessLevel)) {
            $menu->addChild('New users', [
                'route' => 'zikulazauthmodule_useradministration_create',
            ])->setAttribute('icon', 'fas fa-plus')
                ->setAttribute('dropdown', true);
            $menu['New users']->addChild('Create new user', [
                'route' => 'zikulazauthmodule_useradministration_create',
            ]);
            $menu['New users']->addChild('Import users', [
                'route' => 'zikulazauthmodule_fileio_import',
            ]);
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulazauthmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        if (!$this->currentUser->isLoggedIn()) {
            return null;
        }
        $menu = $this->factory->createItem('zauthAccountMenu');
        $userMapping = $this->mappingRepository->findOneBy(['uid' => $this->currentUser->get('uid')]);
        if (isset($userMapping)) {
            $menu->addChild('Change password', [
                'route' => 'zikulazauthmodule_account_changepassword',
            ])->setAttribute('icon', 'fas fa-key')
            ->setLinkAttribute('class', 'text-success');
            $menu->addChild('Change e-mail address', [
                'route' => 'zikulazauthmodule_account_changeemail',
            ])->setAttribute('icon', 'fas fa-at');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('zauthUserMenu');
        $menu->addChild('Account menu', [
            'route' => 'zikulausersmodule_account_menu',
        ])->setAttribute('icon', 'fas fa-user-circle');

        $acctMenu = $this->getAccount();
        foreach ($acctMenu as $item) {
            $menu->addChild($item);
        }

        $menu->addChild('Recover account information or password', [
            'route' => 'zikulausersmodule_account_menu',
        ])->setAttribute('icon', 'fas fa-key');
        $menu['Recover account information or password']->addChild('Recover Lost User Name', [
            'route' => 'zikulazauthmodule_account_lostusername',
        ]);
        $menu['Recover account information or password']->addChild('Recover Lost Password', [
            'route' => 'zikulazauthmodule_account_lostpassword',
        ]);

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaZAuthModule';
    }
}
