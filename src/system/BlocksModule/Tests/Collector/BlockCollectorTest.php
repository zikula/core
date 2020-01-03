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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\Tests\Collector\Fixture\ABlock;
use Zikula\BlocksModule\Tests\Collector\Fixture\BBlock;
use Zikula\BlocksModule\Tests\Collector\Fixture\CBlock;

class BlockCollectorTest extends KernelTestCase
{
    /**
     * @var BlockCollector
     */
    private $collector;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->collector = new BlockCollector();
    }

    /**
     * @covers BlockCollector::add
     */
    public function testAdd(): void
    {
        $this->assertCount(0, $this->collector->getBlocks());
        $this->collector->add(self::$container->get(ABlock::class));
        $this->assertCount(1, $this->collector->getBlocks());
        $this->collector->add(self::$container->get(BBlock::class));
        $this->assertCount(2, $this->collector->getBlocks());
    }

    /**
     * @covers BlockCollector::get
     */
    public function testGet(): void
    {
        $this->collector->add(self::$container->get(CBlock::class));
        $c = $this->collector->get(CBlock::class);
        $this->assertEquals('C', $c->getType());
    }

    /**
     * @covers BlockCollector::getBlocks
     */
    public function testGetBlocks(): void
    {
        $expected = [];
        $block = self::$container->get(ABlock::class);
        $expected[ABlock::class] = $block;
        $this->collector->add($block);
        $block = self::$container->get(BBlock::class);
        $expected[BBlock::class] = $block;
        $this->collector->add($block);
        $this->assertEquals($expected, $this->collector->getBlocks());
    }
}
