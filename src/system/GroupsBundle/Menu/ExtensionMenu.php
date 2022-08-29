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

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        protected readonly PermissionApiInterface $permissionApi,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly CurrentUserApiInterface $currentUserApi
    ) {
    }

    protected function getAdmin(): iterable
    {
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            yield MenuItem::linktoRoute('Groups list', 'fas fa-list', 'zikulagroupsbundle_group_adminlist');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            yield MenuItem::linktoRoute('New group', 'fas fa-plus', 'zikulagroupsbundle_group_create');
        }
    }

    protected function getUser(): iterable
    {
        yield MenuItem::linktoRoute('Groups list', 'fas fa-users', 'zikulagroupsbundle_group_listgroups');

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            yield MenuItem::linktoRoute('Groups admin', 'fas fa-wrench', 'zikulagroupsbundle_group_adminlist');
        }
    }

    protected function getAccount(): iterable
    {
        if (!$this->currentUserApi->isLoggedIn()) {
            return;
        }

        // Check if there is at least one group to show
        $groups = $this->groupRepository->findAll();
        if (0 < count($groups)) {
            yield MenuItem::linktoRoute('Groups manager', 'fas fa-users', 'zikulagroupsbundle_group_listgroups');
        }
    }

    public function getBundleName(): string
    {
        return 'ZikulaGroupsBundle';
    }
}
