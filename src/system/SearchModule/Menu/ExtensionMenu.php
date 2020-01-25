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

namespace Zikula\SearchModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

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
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var SearchStatRepositoryInterface
     */
    private $statRepo;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        SearchStatRepositoryInterface $searchStatRepository
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->statRepo = $searchStatRepository;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (self::TYPE_USER === $type) {
            return $this->getUser();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('searchAdminMenu');
        $menu->addChild('User page', [
            'route' => 'zikulasearchmodule_search_execute',
        ])->setAttribute('icon', 'fas fa-search');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulasearchmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('searchUserMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Admin page', [
                'route' => 'zikulasearchmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            $menu->addChild('New search', [
                'route' => 'zikulasearchmodule_search_execute',
            ])->setAttribute('icon', 'fas fa-search');

            if ($this->currentUserApi->isLoggedIn() && $this->statRepo->countStats() > 0) {
                $menu->addChild('Recent searches list', [
                    'route' => 'zikulasearchmodule_search_recent',
                ])->setAttribute('icon', 'fas fa-list');
            }
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSearchModule';
    }
}
