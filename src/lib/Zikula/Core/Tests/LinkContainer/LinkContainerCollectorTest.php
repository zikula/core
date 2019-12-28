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

namespace Zikula\Core\Tests\LinkContainer;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\LinkContainer\LinkContainerCollector;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\Core\Tests\LinkContainer\Fixtures\BarLinkContainer;
use Zikula\Core\Tests\LinkContainer\Fixtures\FooLinkContainer;

class LinkContainerCollectorTest extends TestCase
{
    /**
     * @var LinkContainerCollector
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
            ->with($this->anything(), $this->isInstanceOf(GenericEvent::class))
            ->will($this->returnArgument(1));
        $this->collector = new LinkContainerCollector($dispatcher, []);
        $this->collector->addContainer(new FooLinkContainer());
        $this->collector->addContainer(new BarLinkContainer());
    }

    /**
     * @covers LinkContainerCollector::addContainer
     */
    public function testAddContainer(): void
    {
        $container = $this
            ->getMockBuilder(LinkContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->method('getBundleName')
            ->willReturn('MockExtension');
        $this->collector->addContainer($container);
        $this->assertTrue($this->collector->hasContainer('MockExtension'));
    }

    /**
     * @covers LinkContainerCollector::hasContainer
     */
    public function testHasContainer(): void
    {
        $this->assertTrue($this->collector->hasContainer('ZikulaFooExtension'));
        $this->assertTrue($this->collector->hasContainer('ZikulaBarExtension'));
        $this->assertFalse($this->collector->hasContainer('ZikulaFazExtension'));
        $this->assertFalse($this->collector->hasContainer('ZikulaBazExtension'));
    }

    /**
     * @covers LinkContainerCollector::getLinks
     * @dataProvider linksProvider
     */
    public function testGetLinks(string $extension, string $type, array $expected = []): void
    {
        $this->assertEquals($expected, $this->collector->getLinks($extension, $type));
    }

    /**
     * @covers LinkContainerCollector::getAllLinksByType
     * @dataProvider allLinksProvider
     */
    public function testGetAllLinksByType(string $type, array $expected = []): void
    {
        $this->assertEquals($expected, $this->collector->getAllLinksByType($type));
    }

    public function linksProvider(): array
    {
        return [
            ['Unknown Extension', 'admin', []],
            ['ZikulaFooExtension', 'UnknownType', []],
            ['ZikulaFooExtension', 'admin',
                [['url' => '/foo/admin',
                    'text' => 'Foo Admin',
                    'icon' => 'wrench']]],
            ['ZikulaFooExtension', 'user',
                [['url' => '/foo',
                    'text' => 'Foo',
                    'icon' => 'check-square-o']]],
            ['ZikulaBarExtension', 'bar',
                [['url' => '/bar/admin',
                    'text' => 'Bar Admin',
                    'icon' => 'plus']]],
            ['ZikulaBarExtension', 'user',
                [
                    ['url' => '/bar',
                        'text' => 'Bar',
                        'icon' => 'check'],
                    ['url' => '/bar2',
                        'text' => 'Bar 2',
                        'icon' => 'check'],
                ]
            ],
        ];
    }

    public function allLinksProvider(): array
    {
        return [
            [LinkContainerInterface::TYPE_ACCOUNT, [
                'ZikulaFooExtension' => [
                    ['url' => '/foo/account',
                        'text' => 'Foo Account',
                        'icon' => 'wrench'
                    ]
                ],
                'ZikulaBarExtension' => [
                    ['url' => '/bar/account',
                        'text' => 'Bar Account',
                        'icon' => 'check'
                    ]
                ],
            ]],
            [LinkContainerInterface::TYPE_ADMIN, [
                'ZikulaFooExtension' => [
                    ['url' => '/foo/admin',
                        'text' => 'Foo Admin',
                        'icon' => 'wrench'
                    ]
                ],
            ]],
            [LinkContainerInterface::TYPE_USER, [
                'ZikulaFooExtension' => [
                    ['url' => '/foo',
                        'text' => 'Foo',
                        'icon' => 'check-square-o'
                    ]
                ],
                'ZikulaBarExtension' => [
                    ['url' => '/bar',
                        'text' => 'Bar',
                        'icon' => 'check'
                    ],
                    ['url' => '/bar2',
                        'text' => 'Bar 2',
                        'icon' => 'check'
                    ]
                ],
            ]],
        ];
    }
}
