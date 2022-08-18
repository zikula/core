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

use Knp\Menu\ItemInterface;
use Zikula\MenuBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }
        $menu = $this->factory->createItem('themeAdminMenu');
        $menu->addChild('Settings', [
            'route' => 'zikulathemebundle_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaThemeBundle';
    }
}
