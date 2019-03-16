<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Collector\BlockCollector;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\Core\AbstractModule;
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
    const BLOCK_ACTIVE = 1;

    const BLOCK_INACTIVE = 0;

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

    /**
     * BlockApi constructor.
     * @param BlockPositionRepositoryInterface $blockPositionRepository
     * @param BlockFactoryApiInterface $blockFactoryApi
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param BlockCollector $blockCollector
     */
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

    /**
     * {@inheritdoc}
     * @param string $positionName
     */
    public function getBlocksByPosition($positionName)
    {
        if (empty($positionName)) {
            throw new \InvalidArgumentException('Name must not be empty.');
        }

        /** @var \Zikula\BlocksModule\Entity\BlockPositionEntity $position */
        $position = $this->blockPositionRepository->findByName($positionName);
        $blocks = [];
        if (empty($position)) {
            return $blocks;
        }

        foreach ($position->getPlacements() as $placement) {
            $blocks[$placement->getBlock()->getBid()] = $placement->getBlock();
        }

        return $blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstanceFromBKey($bKey)
    {
        list(/*$moduleName*/, $blockFqCn) = explode(':', $bKey);

        return $this->blockFactory->getInstance($blockFqCn);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableBlockTypes(ExtensionEntity $moduleEntity = null)
    {
        $modulesByName = [];
        $modules = isset($moduleEntity) ? [$moduleEntity] : $this->extensionRespository->findBy(['state' => Constant::STATE_ACTIVE]);
        /** @var \Zikula\ExtensionsModule\Entity\ExtensionEntity $module */
        foreach ($modules as $module) {
            $modulesByName[$module->getName()] = $module;
        }

        $foundBlocks = [];
        foreach ($this->blockCollector->getBlocks() as $id => $blockInstance) {
            $bundleName = $blockInstance->getBundle()->getName();
            if (!in_array($bundleName, array_keys($modulesByName))) {
                continue;
            }

            $foundBlocks[$id] = $modulesByName[$bundleName]->getDisplayname() . '/' . $blockInstance->getType();
        }
        asort($foundBlocks);

        return $foundBlocks;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulesContainingBlocks()
    {
        $modules = $this->extensionRespository->findBy(['state' => Constant::STATE_ACTIVE]);
        $modulesContainingBlocks = [];
        foreach ($modules as $module) {
            $blocks = $this->getAvailableBlockTypes($module);
            if (!empty($blocks)) {
                $modulesContainingBlocks[$module->getId()] = $module->getName();
            }
        }
        asort($modulesContainingBlocks);

        return $modulesContainingBlocks;
    }
}
