<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Tests\LinkContainer;

use Zikula\Core\LinkContainer\LinkContainerCollector;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\Core\Tests\LinkContainer\Fixtures\BarLinkContainer;
use Zikula\Core\Tests\LinkContainer\Fixtures\FooLinkContainer;

class LinkContainerCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LinkContainerCollector
     */
    private $collector;

    public function setUp()
    {
        $dispatcher = $this
            ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dispatcher
            ->method('dispatch')
            ->with($this->anything(), $this->isInstanceOf('Zikula\Core\Event\GenericEvent'))
            ->will($this->returnArgument(1));
        $this->collector = new LinkContainerCollector($dispatcher);
        $this->collector->addContainer(new FooLinkContainer());
        $this->collector->addContainer(new BarLinkContainer());
    }

    /**
     * @covers \LinkContainerCollector::addContainer
     */
    public function testAddContainer()
    {
        $container = $this
            ->getMockBuilder('Zikula\Core\LinkContainer\LinkContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->method('getBundleName')
            ->willReturn('MockExtension');
        $this->collector->addContainer($container);
        $this->assertTrue($this->collector->hasContainer('MockExtension'));
    }

    /**
     * @covers \LinkContainerCollector::hasContainer
     */
    public function testHasContainer()
    {
        $this->assertTrue($this->collector->hasContainer('ZikulaFooExtension'));
        $this->assertTrue($this->collector->hasContainer('ZikulaBarExtension'));
        $this->assertFalse($this->collector->hasContainer('ZikulaFazExtension'));
        $this->assertFalse($this->collector->hasContainer('ZikulaBazExtension'));
    }

    /**
     * @covers \LinkContainerCollector::getLinks
     * @dataProvider linksProvider
     * @param string $extension
     * @param string $type
     * @param array $expected
     */
    public function testGetLinks($extension, $type, array $expected)
    {
        $this->assertEquals($expected, $this->collector->getLinks($extension, $type));
    }

    /**
     * @covers \LinkContainerCollector::getAllLinksByType
     * @dataProvider allLinksProvider
     * @param string $type
     * @param array $expected
     */
    public function testGetAllLinksByType($type, $expected)
    {
        $this->assertEquals($expected, $this->collector->getAllLinksByType($type));
    }

    public function linksProvider()
    {
        return [
            ['Unknown Extension', 'Admin', []],
            ['ZikulaFooExtension', 'UnknownType', []],
            ['ZikulaFooExtension', 'Admin',
                [['url' => '/foo/admin',
                    'text' => 'Foo Admin',
                    'icon' => 'wrench']]],
            ['ZikulaFooExtension', 'User',
                [['url' => '/foo',
                    'text' => 'Foo',
                    'icon' => 'check-square-o']]],
            ['ZikulaBarExtension', 'Bar',
                [['url' => '/bar/admin',
                    'text' => 'Bar Admin',
                    'icon' => 'plus']]],
            ['ZikulaBarExtension', 'User',
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

    public function allLinksProvider()
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
