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
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\BlocksModule\Tests\Api\Fixture\AcmeFooModule;
use Zikula\BlocksModule\Tests\Api\Fixture\FooBlock;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class BlockApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BlockApi
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

    /**
     * BlockApiTest setup.
     */
    protected function setUp()
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
        $kernel = $this
            ->getMockBuilder('ZikulaKernel')
            ->disableOriginalConstructor()
            ->getMock();
        $kernel
            ->method('getModule')
            ->willReturn(new AcmeFooModule());

        $this->api = new BlockApi($blockPosRepo, $blockFactory, $extensionRepo, $blockCollector, $kernel);
    }

    /**
     * @covers BlockApi::getBlocksByPosition
     */
    public function testGetBlocksByPosition()
    {
        $this->assertCount(3, $this->api->getBlocksByPosition('left'));
    }

    /**
     * @covers BlockApi::createInstanceFromBKey
     */
    public function testCreateInstanceFromBKey()
    {
        $this->assertEquals($this->fooBlock, $this->api->createInstanceFromBKey('AcmeFooModule:Zikula\BlocksModule\Tests\Api\Fixture\FooBlock'));
    }

    private function setUpBlockPlacements()
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
