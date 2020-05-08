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

namespace Zikula\BlocksModule\Menu;

use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    protected function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('blocksAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_EDIT)) {
            $menu->addChild('Blocks list', [
                'route' => 'zikulablocksmodule_admin_view',
            ])->setAttribute('icon', 'fas fa-table');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADD)) {
            $menu->addChild('Create new block', [
                'route' => 'zikulablocksmodule_block_new',
            ])->setAttribute('icon', 'fas fa-plus');
            $menu->addChild('Create new block position', [
                'route' => 'zikulablocksmodule_position_edit',
            ])->setAttribute('icon', 'fas fa-plus');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulablocksmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaBlocksModule';
    }
}
