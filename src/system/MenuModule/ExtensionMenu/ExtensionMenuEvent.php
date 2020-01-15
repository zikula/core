<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\ExtensionMenu;

use Knp\Menu\ItemInterface;

class ExtensionMenuEvent
{
    private $bundleName;

    private $menuType;

    private $menu;

    public function __construct(string $bundleName, string $menuType, ItemInterface $menu)
    {
        $this->bundleName = $bundleName;
        $this->menuType = $menuType;
        $this->menu = $menu;
    }

    public function getBundleName(): string
    {
        return $this->bundleName;
    }

    public function getMenuType(): string
    {
        return $this->menuType;
    }

    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }
}
