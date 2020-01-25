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

namespace Zikula\MenuModule\Tests\ExtensionMenu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuEvent;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\MenuModule\Tests\ExtensionMenu\Fixtures\BarExtensionMenu;
use Zikula\MenuModule\Tests\ExtensionMenu\Fixtures\FooExtensionMenu;

class ExtensionMenuCollectorTest extends TestCase
{
    /**
     * @var ExtensionMenuCollector
     */
    private $collector;

    protected function setUp(): void
    {
        $dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dispatcher
            ->method('dispatch')
            ->with($this->isInstanceOf(ExtensionMenuEvent::class), $this->anything())
            ->will($this->returnArgument(0));
        $this->collector = new ExtensionMenuCollector($dispatcher, []);

        $factory = new MenuFactory();
        $this->collector->add(new FooExtensionMenu($factory));
        $this->collector->add(new BarExtensionMenu($factory));
    }

    /**
     * @covers ExtensionMenuCollector::add
     */
    public function testAdd(): void
    {
        $menu = $this->getMockBuilder(ExtensionMenuInterface::class)->getMock();
        $menu
            ->method('getBundleName')
            ->willReturn('MockExtension');
        $this->collector->add($menu);
        $this->assertTrue($this->collector->has('MockExtension'));
    }

    /**
     * @covers ExtensionMenuCollector::has
     */
    public function testHas(): void
    {
        $this->assertTrue($this->collector->has('ZikulaFooExtension'));
        $this->assertTrue($this->collector->has('ZikulaBarExtension'));
        $this->assertFalse($this->collector->has('ZikulaFazExtension'));
        $this->assertFalse($this->collector->has('ZikulaBazExtension'));
    }

    /**
     * @covers ExtensionMenuCollector::get
     * @dataProvider menuProvider
     */
    public function testGet(string $extension, string $type, int $count): void
    {
        $menu = $this->collector->get($extension, $type);
        $this->assertEquals($count, $menu ? $menu->count() : 0);
    }

    public function menuProvider(): array
    {
        return [
            ['Unknown Extension', ExtensionMenuInterface::TYPE_ADMIN, 0],
            ['ZikulaFooExtension', 'UnknownType', 0],
            ['ZikulaFooExtension', ExtensionMenuInterface::TYPE_ADMIN, 3],
            ['ZikulaFooExtension', ExtensionMenuInterface::TYPE_USER, 1],
            ['ZikulaBarExtension', 'bar', 1],
        ];
    }

    /**
     * @covers ExtensionMenuCollector::getAllByType
     * @dataProvider allMenusProvider
     */
    public function testGetAllByType(string $type, array $expected = []): void
    {
        $this->assertEquals($expected, array_keys($this->collector->getAllByType($type)));
    }

    public function allMenusProvider(): array
    {
        return [
            [ExtensionMenuInterface::TYPE_ACCOUNT, ['ZikulaBarExtension']],
            [ExtensionMenuInterface::TYPE_USER, ['ZikulaFooExtension', 'ZikulaBarExtension']],
            [ExtensionMenuInterface::TYPE_ADMIN, ['ZikulaFooExtension']],
            ['bar', ['ZikulaBarExtension']],
        ];
    }
}
