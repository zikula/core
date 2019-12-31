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

namespace Zikula\BlocksModule\Api;

use InvalidArgumentException;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * Class BlockApi
 *
 * This class provides an API for interaction with and management of blocks. The class is mainly used internally
 * by Twig-based theme tags in order to 'decorate' a page with the requested blocks.
 */
class BlockApi implements BlockApiInterface
{
    public const BLOCK_ACTIVE = 1;

    public const BLOCK_INACTIVE = 0;

    /**
     * @var BlockPositionRepositoryInterface
     */
    private $blockPositionRepository;

    /**
     * @var BlockFactoryApiInterface
     */
    private $blockFactory;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRespository;

    /**
     * @var BlockCollector;
     */
    private $blockCollector;

    public function __construct(
        BlockPositionRepositoryInterface $blockPositionRepository,
        BlockFactoryApiInterface $blockFactoryApi,
        ExtensionRepositoryInterface $extensionRepository,
        BlockCollector $blockCollector
    ) {
        $this->blockPositionRepository = $blockPositionRepository;
        $this->blockFactory = $blockFactoryApi;
        $this->extensionRespository = $extensionRepository;
        $this->blockCollector = $blockCollector;
    }

    public function getBlocksByPosition(string $positionName): array
    {
        if (empty($positionName)) {
            throw new InvalidArgumentException('Name must not be empty.');
        }

        /** @var BlockPositionEntity $position */
        $position = $this->blockPositionRepository->findByName($positionName);
        $blocks = [];
        if (null === $position) {
            return $blocks;
        }

        foreach ($position->getPlacements() as $placement) {
            /** @var BlockEntity $block */
            $block = $placement->getBlock();
            $blocks[$block->getBid()] = $block;
        }

        return $blocks;
    }

    public function createInstanceFromBKey(string $bKey): BlockHandlerInterface
    {
        if (false !== strpos($bKey, ':')) {
            @trigger_error('The BKey should not contain the module name with a colon as of Core-3.0.0');
        }

        return $this->blockFactory->getInstance($bKey);
    }

    public function getAvailableBlockTypes(ExtensionEntity $moduleEntity = null): array
    {
        $modulesByName = [];
        $modules = isset($moduleEntity) ? [$moduleEntity] : $this->extensionRespository->findBy(['state' => Constant::STATE_ACTIVE]);
        /** @var ExtensionEntity $module */
        foreach ($modules as $module) {
            $modulesByName[$module->getName()] = $module;
        }

        $foundBlocks = [];
        foreach ($this->blockCollector->getBlocks() as $id => $blockInstance) {
            /** @var AbstractBlockHandler $blockInstance */
            $bundleName = $blockInstance->getBundle()->getName();
            if (!array_key_exists($bundleName, $modulesByName)) {
                continue;
            }

            $foundBlocks[$id] = $modulesByName[$bundleName]->getDisplayname() . '/' . $blockInstance->getType();
        }
        asort($foundBlocks);

        return $foundBlocks;
    }

    public function getModulesContainingBlocks(): array
    {
        $modules = $this->extensionRespository->findBy(['state' => Constant::STATE_ACTIVE]);
        $modulesContainingBlocks = [];
        foreach ($modules as $module) {
            /** @var ExtensionEntity $module */
            $blocks = $this->getAvailableBlockTypes($module);
            if (!empty($blocks)) {
                $modulesContainingBlocks[$module->getId()] = $module->getName();
            }
        }
        asort($modulesContainingBlocks);

        return $modulesContainingBlocks;
    }
}
