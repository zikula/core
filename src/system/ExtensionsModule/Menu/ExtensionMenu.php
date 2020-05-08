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

namespace Zikula\ExtensionsModule\Menu;

use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            return null;
        }
        $menu = $this->factory->createItem('extensionsAdminMenu');
        $menu->addChild('Extension List', [
            'route' => 'zikulaextensionsmodule_extension_list',
        ])->setAttribute('icon', 'fas fa-list');
        $menu->addChild('Settings', [
            'route' => 'zikulaextensionsmodule_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');

        return $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaExtensionsModule';
    }
}
