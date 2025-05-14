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

namespace Zikula\ThemeBundle\Tests\ExtensionMenu\Fixtures;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

class FooExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function get(string $type = ExtensionMenuInterface::TYPE_ADMIN): ?ItemInterface
    {
        if (ExtensionMenuInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (ExtensionMenuInterface::TYPE_USER === $type) {
            return $this->getUser();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('foo');
        $menu->addChild('list', [
            'route' => 'list',
        ]);
        $menu->addChild('foo', [
            'route' => 'foo',
        ]);
        $menu->addChild('bar', [
            'route' => 'bar',
        ]);

        return $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('foo');
        $menu->addChild('user list', [
            'route' => 'user_list',
        ]);

        return $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaFooExtension';
    }
}
