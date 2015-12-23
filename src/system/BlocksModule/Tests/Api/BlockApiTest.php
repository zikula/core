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

namespace Zikula\BlocksModule\Tests\Api;

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Tests\Api\Fixture\FooBlock;

class BlockApiTest extends \PHPUnit_Framework_TestCase
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
    public function setUp()
    {
        $this->setUpBlockPlacements();
        $this->fooBlock = new FooBlock();

        $blockPosRepo = $this
            ->getMockBuilder('Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface')
            ->getMock();
        $position = $this
            ->getMockBuilder('Zikula\BlocksModule\Entity\BlockPositionEntity')
            ->getMock();
        $position
            ->method('getPlacements')
            ->willReturn($this->blockPlacements);
        $blockPosRepo
            ->method('findByName')
            ->willReturn($position);
        $blockFactory = $this
            ->getMockBuilder('Zikula\BlocksModule\Api\BlockFactoryApi')
            ->disableOriginalConstructor()
            ->getMock();
        $blockFactory
            ->method('getInstance')
            ->willReturn($this->fooBlock);
        $extensionApi = $this
            ->getMockBuilder('Zikula\ExtensionsModule\Api\ExtensionApi')
            ->disableOriginalConstructor()
            ->getMock();
        $extensionApi
            ->method('getModulesBy')
            ->willReturn([]);
        $blockCollector = new BlockCollector();

        $this->api = new BlockApi($blockPosRepo, $blockFactory, $extensionApi, $blockCollector, '/');
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
