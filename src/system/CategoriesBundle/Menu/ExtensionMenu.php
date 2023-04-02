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
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use function Symfony\Component\Translation\t;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): iterable
    {
        yield MenuItem::linkToCrud(t('Categories'), 'fas fa-sitemap', Category::class);
        yield MenuItem::linkToCrud(t('Add category'), 'fas fa-plus', Category::class)
            ->setAction('new');

        yield MenuItem::section('Old stuff');

        yield MenuItem::linktoRoute('Category tree', 'fas fa-tree', 'zikulacategoriesbundle_category_listcategories')
            ->setPermission('ROLE_EDITOR');
        yield MenuItem::linktoRoute('Category registry', 'fas fa-archive', 'zikulacategoriesbundle_registry_edit')
            ->setPermission('ROLE_ADMIN');
    }

    public function getBundleName(): string
    {
        return 'ZikulaCategoriesBundle';
    }
}
