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

namespace Zikula\ThemeBundle\Tests\ExtensionMenu;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuEvent;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\ThemeBundle\Tests\ExtensionMenu\Fixtures\BarExtensionMenu;
use Zikula\ThemeBundle\Tests\ExtensionMenu\Fixtures\FooExtensionMenu;

#[CoversClass(ExtensionMenuCollector::class)]
class ExtensionMenuCollectorTest extends TestCase
{
    private ExtensionMenuCollector $collector;

    protected function setUp(): void
    {
        $dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dispatcher
            ->method('dispatch')
            ->with($this->isInstanceOf(ExtensionMenuEvent::class), $this->anything())
            ->willReturnCallback(fn ($arg) => $arg);
        $this->collector = new ExtensionMenuCollector($dispatcher, []);

        $this->collector->add(new FooExtensionMenu());
        $this->collector->add(new BarExtensionMenu());
    }

    public function testAdd(): void
    {
        $menu = $this->getMockBuilder(ExtensionMenuInterface::class)->getMock();
        $menu
            ->method('getBundleName')
            ->willReturn('MockExtension');
        $this->collector->add($menu);
        $this->assertTrue($this->collector->has('MockExtension'));
    }

    public function testHas(): void
    {
        $this->assertTrue($this->collector->has('ZikulaFooExtension'));
        $this->assertTrue($this->collector->has('ZikulaBarExtension'));
        $this->assertFalse($this->collector->has('ZikulaFazExtension'));
        $this->assertFalse($this->collector->has('ZikulaBazExtension'));
    }

    #[DataProvider('menuProvider')]
    public function testGet(string $extension, string $context, int $count): void
    {
        $menu = $this->collector->get($extension, $context);
        $this->assertEquals($count, count(iterator_to_array($menu)));
    }

    public static function menuProvider(): array
    {
        return [
            ['Unknown Extension', ExtensionMenuInterface::CONTEXT_ADMIN, 0],
            ['ZikulaFooExtension', 'UnknownContext', 0],
            ['ZikulaFooExtension', ExtensionMenuInterface::CONTEXT_ADMIN, 3],
            ['ZikulaFooExtension', ExtensionMenuInterface::CONTEXT_USER, 1],
            ['ZikulaBarExtension', 'bar', 1],
        ];
    }

    #[DataProvider('allMenusProvider')]
    public function testGetAllByContext(string $context, array $expected = []): void
    {
        $this->assertEquals($expected, array_keys($this->collector->getAllByContext($context)));
    }

    public static function allMenusProvider(): array
    {
        return [
            [ExtensionMenuInterface::CONTEXT_ACCOUNT, ['ZikulaBarExtension']],
            [ExtensionMenuInterface::CONTEXT_USER, ['ZikulaFooExtension', 'ZikulaBarExtension']],
            [ExtensionMenuInterface::CONTEXT_ADMIN, ['ZikulaFooExtension']],
            ['bar', ['ZikulaBarExtension']],
        ];
    }
}
