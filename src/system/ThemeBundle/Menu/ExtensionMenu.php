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

namespace Zikula\ThemeBundle\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): iterable
    {
        yield MenuItem::linktoRoute('Branding', 'fas fa-palette', 'zikulathemebundle_branding_overview')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linktoRoute('Test mail settings', 'fas fa-envelope', 'zikulathemebundle_tool_testmail')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linktoRoute('PHP configuration', 'fab fa-php', 'zikulathemebundle_tool_phpinfo')
            ->setPermission('ROLE_ADMIN');
    }

    public function getBundleName(): string
    {
        return 'ZikulaThemeBundle';
    }
}
