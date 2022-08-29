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

namespace Zikula\ThemeBundle\ExtensionMenu;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;

class ExtensionMenuEvent
{
    public function __construct(
        private readonly string $bundleName,
        private readonly string $context,
        private readonly iterable $menu
    ) {
    }

    public function getBundleName(): string
    {
        return $this->bundleName;
    }

    public function getMenuContext(): string
    {
        return $this->context;
    }

    /**
     * @return MenuItemInterface[]
     */
    public function getMenu(): iterable
    {
        return $this->menu;
    }
}
