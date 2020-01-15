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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExtensionMenuCollector
{
    /**
     * @var ExtensionMenuInterface[]
     */
    private $extensionMenus;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, iterable $extensionMenus = [])
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->extensionMenus = [];
        foreach ($extensionMenus as $extensionMenu) {
            $this->add($extensionMenu);
        }
    }

    public function add(ExtensionMenuInterface $extensionMenu): void
    {
        $this->extensionMenus[$extensionMenu->getBundleName()] = $extensionMenu;
    }

    public function get(string $bundleName, string $type = ExtensionMenuInterface::TYPE_ADMIN): ?ItemInterface
    {
        if ($this->has($bundleName)) {
            try {
                $menu = $this->extensionMenus[$bundleName]->get($type);
            } catch (\Exception $exception) {
                // do nothing
            }

            // fire event here to add more menu items like hooks, moduleServices, etc
            $event = new ExtensionMenuEvent($bundleName, $type, $menu);
            $menu = $this->eventDispatcher->dispatch($event)->getMenu();

            return $menu;
        }
        return null;
    }

    public function getAllByType(string $type = ExtensionMenuInterface::TYPE_ACCOUNT): array
    {
        $menus = [];
        foreach ($this->extensionMenus as $bundleName => $extensionMenu) {
            $menu = $extensionMenu->get($type);
            if (null !== $menu) {
                $menus[$bundleName] = $menu;
            }
        }

        return $menus;
    }

    public function has(string $bundleName): bool
    {
        return isset($this->extensionMenus[$bundleName]);
    }
}
