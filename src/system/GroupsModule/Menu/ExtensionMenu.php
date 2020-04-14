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

namespace Zikula\GroupsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
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
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var GroupApplicationRepository
     */
    private $groupApplicationRepository;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUser;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        GroupRepositoryInterface $groupRepository,
        GroupApplicationRepository $groupApplicationRepository,
        CurrentUserApiInterface $currentUserApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->groupRepository = $groupRepository;
        $this->groupApplicationRepository = $groupApplicationRepository;
        $this->currentUser = $currentUserApi;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (self::TYPE_USER === $type) {
            return $this->getUser();
        }
        if (self::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('adminAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $menu->addChild('Groups list', [
                'route' => 'zikulagroupsmodule_group_adminlist',
            ])->setAttribute('icon', 'fas fa-list');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('New group', [
                'route' => 'zikulagroupsmodule_group_create',
            ])->setAttribute('icon', 'fas fa-plus');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulagroupsmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }
        $apps = $this->groupApplicationRepository->findAll();
        $appCount = count($apps);
        if (($appCount > 0) && $this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $menu->addChild('%amount% pending applications', [
                'route' => 'zikulagroupsmodule_group_adminlist',
                'routeParameters' => ['_fragment' => 'applications']
            ])->setExtra('translation_params', ['%amount%' => $appCount])
            ->setAttribute('icon', 'fas fa-exclamation-triangle');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('groupsAccountMenu');
        $menu->addChild('Group list', [
            'route' => 'zikulagroupsmodule_group_list',
        ])->setAttribute('icon', 'fas fa-users');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $menu->addChild('Groups admin', [
                'route' => 'zikulagroupsmodule_group_adminlist',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        if (!$this->currentUser->isLoggedIn()) {
            return null;
        }

        // Check if there is at least one group to show
        $groups = $this->groupRepository->findAll();
        if (count($groups) > 0) {
            $menu = $this->factory->createItem('groupsAccountMenu');
            $menu->addChild('Groups manager', [
                'route' => 'zikulagroupsmodule_group_list',
            ])->setAttribute('icon', 'fas fa-users');
        }

        return $menu ?? null;
    }

    public function getBundleName(): string
    {
        return 'ZikulaGroupsModule';
    }
}
