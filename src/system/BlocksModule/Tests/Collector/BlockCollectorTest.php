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

namespace Zikula\BlocksModule\Tests\Collector;

use PHPUnit\Framework\TestCase;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\BlockHandlerInterface;

class BlockCollectorTest extends TestCase
{
    /**
     * @var BlockCollector
     */
    private $collector;

    protected function setUp(): void
    {
        $this->collector = new BlockCollector();
    }

    /**
     * @covers BlockCollector::add
     */
    public function testAdd(): void
    {
        $this->assertCount(0, $this->collector->getBlocks());
        $block = $this->getMockBuilder(BlockHandlerInterface::class)
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('A');
        $this->collector->add($block);
        $this->assertCount(1, $this->collector->getBlocks());
        $block = $this->getMockBuilder(BlockHandlerInterface::class)
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('B');
        $this->collector->add($block);
        $this->assertCount(2, $this->collector->getBlocks());
    }

    /**
     * @covers BlockCollector::add
     */
    public function testGet(): void
    {
        $block = $this->getMockBuilder(BlockHandlerInterface::class)
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('A');
        $this->collector->add($block);
        $a = $this->collector->getBlocks()[0];
        $this->assertEquals('A', $a->getType());
    }

    /**
     * @covers BlockCollector::getBlocks
     */
    public function testGetBlocks(): void
    {
        $expected = [];
        $block = $this->getMockBuilder(BlockHandlerInterface::class)
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('A');
        $expected['a'] = $block;
        $this->collector->add($block);
        $block = $this->getMockBuilder(BlockHandlerInterface::class)
            ->getMock();
        $block
            ->method('getType')
            ->willReturn('B');
        $expected['b'] = $block;
        $this->collector->add($block);
        $this->assertEquals($expected, $this->collector->getBlocks());
    }
}
