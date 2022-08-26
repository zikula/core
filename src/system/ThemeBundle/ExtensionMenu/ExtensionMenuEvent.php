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

use Knp\Menu\ItemInterface;

class ExtensionMenuEvent
{
    public function __construct(
        private readonly string $bundleName,
        private readonly string $menuType,
        private readonly ?ItemInterface $menu
    ) {
    }

    public function getBundleName(): string
    {
        return $this->bundleName;
    }

    public function getMenuType(): string
    {
        return $this->menuType;
    }

    public function getMenu(): ?ItemInterface
    {
        return $this->menu;
    }
}
