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

namespace Zikula\MenuModule\Tests\ExtensionMenu\Fixtures;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;

class BarExtensionMenu implements ExtensionMenuInterface
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
        $method = 'get' . ucfirst($type);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('admin');
        $menu->addChild('list', [
            'route' => 'list',
        ])->setAttribute('icon', 'fas fa-list');
        $menu->addChild('new', [
            'route' => 'edit',
        ]);

        return $menu;
    }

    private function getBar(): ?ItemInterface
    {
        $menu = $this->factory->createItem('foo');
        $menu->addChild('bar admin', [
            'route' => 'bar_admin',
        ])->setAttribute('icon', 'fas fa-plus');

        return $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('account');
        $menu->addChild('bar acct', [
            'route' => 'bar_acct',
        ]);

        return $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaBarExtension';
    }
}
