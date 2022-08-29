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

namespace Zikula\PermissionsBundle\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): iterable
    {
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            yield MenuItem::linktoRoute('Permission rules list', 'fas fa-list', 'zikulapermissionsbundle_permission_listpermissions');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            yield MenuItem::linktoUrl('Create new permission rule', 'fas fa-plus', '#')
                ->setCssClass('create-new-permission')
            ;
        }
        yield MenuItem::linktoUrl('Permission rules information', 'fas fa-info', '#')
            ->setCssClass('view-instance-info')
        ;
    }

    public function getBundleName(): string
    {
        return 'ZikulaPermissionsBundle';
    }
}
