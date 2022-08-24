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

namespace Zikula\GroupsBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly CurrentUserApiInterface $currentUserApi
    ) {
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
                'route' => 'zikulagroupsbundle_group_adminlist',
            ])->setAttribute('icon', 'fas fa-list');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('New group', [
                'route' => 'zikulagroupsbundle_group_create',
            ])->setAttribute('icon', 'fas fa-plus');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('groupsAccountMenu');
        $menu->addChild('Group list', [
            'route' => 'zikulagroupsbundle_group_listgroups',
        ])->setAttribute('icon', 'fas fa-users');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $menu->addChild('Groups admin', [
                'route' => 'zikulagroupsbundle_group_adminlist',
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
                'route' => 'zikulagroupsbundle_group_listgroups',
            ])->setAttribute('icon', 'fas fa-users');
        }

        return $menu ?? null;
    }

    public function getBundleName(): string
    {
        return 'ZikulaGroupsBundle';
    }
}
