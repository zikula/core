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

namespace Zikula\BlocksModule\Tests\Api;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\BlocksModule\Tests\Api\Fixture\FooBlock;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class BlockApiTest extends TestCase
{
    /**
     * @var BlockApiInterface
     */
    private $api;

    /**
     * @var ArrayCollection
     */
    private $blockPlacements;

    /**
     * @var FooBlock
     */
    private $fooBlock;

    protected function setUp(): void
    {
        $this->setUpBlockPlacements();
        $this->fooBlock = new FooBlock();

        $blockPosRepo = $this
            ->getMockBuilder(BlockPositionRepositoryInterface::class)
            ->getMock();
        $position = $this
            ->getMockBuilder(BlockPositionEntity::class)
            ->getMock();
        $position
            ->method('getPlacements')
            ->willReturn($this->blockPlacements);
        $blockPosRepo
            ->method('findByName')
            ->willReturn($position);
        $blockFactory = $this
            ->getMockBuilder(BlockFactoryApiInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockFactory
            ->method('getInstance')
            ->willReturn($this->fooBlock);
        $extensionRepo = $this
            ->getMockBuilder(ExtensionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockCollector = new BlockCollector();

        $this->api = new BlockApi($blockPosRepo, $blockFactory, $extensionRepo, $blockCollector);
    }

    /**
     * @covers BlockApi::getBlocksByPosition
     */
    public function testGetBlocksByPosition(): void
    {
        $this->assertCount(3, $this->api->getBlocksByPosition('left'));
    }

    /**
     * @covers BlockApi::createInstanceFromBKey
     */
    public function testCreateInstanceFromBKey(): void
    {
        $this->assertEquals($this->fooBlock, $this->api->createInstanceFromBKey('Zikula\BlocksModule\Tests\Api\Fixture\FooBlock'));
    }

    private function setUpBlockPlacements(): void
    {
        $this->blockPlacements = new ArrayCollection();
        $block = new BlockEntity();
        $block->setBid(1);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($block);
        $this->blockPlacements->set(1, $placement);
        $block = new BlockEntity();
        $block->setBid(2);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($block);
        $this->blockPlacements->set(2, $placement);
        $block = new BlockEntity();
        $block->setBid(5);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($block);
        $this->blockPlacements->set(5, $placement);
    }
}
