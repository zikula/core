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
use Symfony\Component\EventDispatcher\Event;

/**
 * Event class for extending menus.
 */
class ConfigureMenuEvent extends Event
{
    const POST_CONFIGURE = 'zikulamenumodule.menu_post_configure';

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

    /**
     * ConfigureMenuEvent constructor.
     *
     * @param FactoryInterface $factory
     * @param ItemInterface    $menu
     * @param array            $options
     */
    public function __construct(FactoryInterface $factory, ItemInterface $menu, array $options = [])
    {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->options = $options;
    }

    /**
     * Returns the factory.
     *
     * @return FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Returns the menu.
     *
     * @return ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Returns the options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
