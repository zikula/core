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

namespace Zikula\MenuModule\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * Event class for extending menus.
 */
class ConfigureMenuEvent
{
    public const POST_CONFIGURE = 'zikulamenumodule.menu_post_configure';

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var ItemInterface
     */
    protected $menu;

    /**
     * @var array
     */
    protected $options;

    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        array $options = []
    ) {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->options = $options;
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
