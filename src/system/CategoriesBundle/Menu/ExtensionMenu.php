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

namespace Zikula\CategoriesBundle\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): iterable
    {
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            yield MenuItem::linktoRoute('Category tree', 'fas fa-tree', 'zikulacategoriesbundle_category_listcategories');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            yield MenuItem::linktoRoute('Category registry', 'fas fa-archive', 'zikulacategoriesbundle_registry_edit');
        }
    }

    public function getBundleName(): string
    {
        return 'ZikulaCategoriesBundle';
    }
}
