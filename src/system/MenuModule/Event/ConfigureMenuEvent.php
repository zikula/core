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

namespace Zikula\MenuModule\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * Event class for extending menus.
 */
class ConfigureMenuEvent
{
    public function __construct(
        protected readonly FactoryInterface $factory,
        protected readonly ItemInterface $menu,
        protected readonly array $options = []
    ) {
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
