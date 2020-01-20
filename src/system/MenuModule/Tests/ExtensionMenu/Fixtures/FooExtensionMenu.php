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

namespace Zikula\MenuModule\Tests\ExtensionMenu\Fixtures;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;

class FooExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    public function __construct(
        FactoryInterface $factory
    ) {
        $this->factory = $factory;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (self::TYPE_USER === $type) {
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
