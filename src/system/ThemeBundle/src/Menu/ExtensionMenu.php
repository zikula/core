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
use function Symfony\Component\Translation\t;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): iterable
    {
        yield MenuItem::linktoRoute(t('Branding'), 'fas fa-palette', 'zikula_theme_branding_overview')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linktoRoute(t('Test mail settings'), 'fas fa-envelope', 'zikula_theme_tool_testmail')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linktoRoute(t('PHP configuration'), 'fab fa-php', 'zikula_theme_tool_phpinfo')
            ->setPermission('ROLE_ADMIN');
    }

    public function getBundleName(): string
    {
        return 'ZikulaThemeBundle';
    }
}
