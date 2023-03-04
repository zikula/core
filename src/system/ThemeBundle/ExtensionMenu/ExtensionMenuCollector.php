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

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExtensionMenuCollector
{
    /**
     * @var ExtensionMenuInterface[]
     */
    private array $extensionMenus;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        #[TaggedIterator(ExtensionMenuInterface::class)]
        iterable $extensionMenus = []
    ) {
        $this->extensionMenus = [];
        foreach ($extensionMenus as $extensionMenu) {
            $this->add($extensionMenu);
        }
    }

    public function add(ExtensionMenuInterface $extensionMenu): void
    {
        $this->extensionMenus[$extensionMenu->getBundleName()] = $extensionMenu;
    }

    public function get(string $bundleName, string $context = ExtensionMenuInterface::CONTEXT_ADMIN): iterable
    {
        if ($this->has($bundleName)) {
            $menu = $this->extensionMenus[$bundleName]->get($context);

            // fire event here to add more menu items like additional services, etc
            $event = new ExtensionMenuEvent($bundleName, $context, $menu);
            $menu = $this->eventDispatcher->dispatch($event)->getMenu();

            return $menu;
        }

        return null;
    }

    public function getAllByContext(string $context = ExtensionMenuInterface::CONTEXT_ADMIN): array
    {
        $menus = [];
        foreach ($this->extensionMenus as $bundleName => $extensionMenu) {
            $menu = $extensionMenu->get($context);
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
