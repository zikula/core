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

namespace Zikula\ExtensionsModule\Event;

use Knp\Menu\ItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConnectionsMenuEvent extends Event
{
    /**
     * The full menu object
     *
     * @var ItemInterface
     */
    private $menu;

    /**
     * The name of the extension in use
     *
     * @var string
     */
    private $extensionName;

    public function __construct(ItemInterface $menu, string $extensionName)
    {
        $this->extensionName = $extensionName;
        $this->menu = $menu;
    }

    public function getExtensionName(): string
    {
        return $this->extensionName;
    }

    /**
     * Add a child menu item to the connections menu
     *
     * Returns this event object to allow method chaining
     *
     * @param ItemInterface|string $child   An ItemInterface instance or the name of a new item to create
     * @param array                $options If creating a new item, the options passed to the factory for the item
     *
     * @return $this
     */
    public function addChild($child, array $options = []): self
    {
        $this->menu->getChild('connections')->addChild($child, $options);

        return $this;
    }
}
