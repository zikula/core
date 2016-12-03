<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Tests\Collector;

use Zikula\BlocksModule\Collector\BlockCollector;

class BlockCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockCollector
     */
    private $collector;

    public function setUp()
    {
        $this->collector = new BlockCollector();
    }

    /**
     * @covers \BlockCollector::add
     */
    public function testAdd()
    {
        $this->assertEquals(0, count($this->collector->getBlocks()));
        $block = $this->getMockBuilder('Zikula\BlocksModule\BlockHandlerInterface')
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('A');
        $this->collector->add('a', $block);
        $this->assertEquals(1, count($this->collector->getBlocks()));
        $block = $this->getMockBuilder('Zikula\BlocksModule\BlockHandlerInterface')
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('B');
        $this->collector->add('b', $block);
        $this->assertEquals(2, count($this->collector->getBlocks()));
    }

    /**
     * @covers \BlockCollector::add
     */
    public function testGet()
    {
        $block = $this->getMockBuilder('Zikula\BlocksModule\BlockHandlerInterface')
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('A');
        $this->collector->add('a', $block);
        $a = $this->collector->get('a');
        $this->assertEquals('A', $a->getType());
    }

    /**
     * @covers \BlockCollector::getBlocks
     */
    public function testGetBlocks()
    {
        $expected = [];
        $block = $this->getMockBuilder('Zikula\BlocksModule\BlockHandlerInterface')
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('A');
        $expected['a'] = $block;
        $this->collector->add('a', $block);
        $block = $this->getMockBuilder('Zikula\BlocksModule\BlockHandlerInterface')
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('B');
        $expected['b'] = $block;
        $this->collector->add('b', $block);
        $this->assertEquals($expected, $this->collector->getBlocks());
    }
}
