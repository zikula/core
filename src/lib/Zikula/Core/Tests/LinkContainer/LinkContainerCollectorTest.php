<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Tests\LinkContainer;

use Zikula\Core\LinkContainer\LinkContainerCollector;
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
     * @covers LinkContainerCollector::addContainer
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
     * @covers LinkContainerCollector::hasContainer
     */
    public function testHasContainer()
    {
        $this->assertTrue($this->collector->hasContainer('ZikulaFooExtension'));
        $this->assertTrue($this->collector->hasContainer('ZikulaBarExtension'));
        $this->assertFalse($this->collector->hasContainer('ZikulaFazExtension'));
        $this->assertFalse($this->collector->hasContainer('ZikulaBazExtension'));
    }

    /**
     * @covers LinkContainerCollector::getLinks
     * @dataProvider linksProvider
     * @param string $extension
     * @param string $type
     * @param array $expected
     */
    public function testGetLinks($extension, $type, array $expected)
    {
        $this->assertEquals($expected, $this->collector->getLinks($extension, $type));
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
}
