<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
     * @covers BlockCollector::add
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
     * @covers BlockCollector::add
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
     * @covers BlockCollector::getBlocks
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
