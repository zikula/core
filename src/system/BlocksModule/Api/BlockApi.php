<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * BlockApi constructor.
     * @param BlockPositionRepositoryInterface $blockPositionRepository
     * @param BlockFactoryApiInterface $blockFactoryApi
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param BlockCollector $blockCollector
     * @param KernelInterface $kernel
     */
    public function __construct(
        BlockPositionRepositoryInterface $blockPositionRepository,
        BlockFactoryApiInterface $blockFactoryApi,
        ExtensionRepositoryInterface $extensionRepository,
        BlockCollector $blockCollector,
        KernelInterface $kernel
    ) {
        $this->blockPositionRepository = $blockPositionRepository;
        $this->blockFactory = $blockFactoryApi;
        $this->extensionRespository = $extensionRepository;
        $this->blockCollector = $blockCollector;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
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
        list($moduleName, $blockFqCn) = explode(':', $bKey);
        $moduleInstance = $this->kernel->getModule($moduleName);

        return $this->blockFactory->getInstance($blockFqCn, $moduleInstance);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableBlockTypes(ExtensionEntity $moduleEntity = null)
    {
        $foundBlocks = [];
        $modules = isset($moduleEntity) ? [$moduleEntity] : $this->extensionRespository->findBy(['state' => Constant::STATE_ACTIVE]);
        /** @var \Zikula\ExtensionsModule\Entity\ExtensionEntity $module */
        foreach ($modules as $module) {
            $moduleInstance = $this->kernel->getModule($module->getName());
            list($nameSpace, $path) = $this->getModuleBlockPath($moduleInstance, $module->getName());
            if (!isset($path)) {
                continue;
            }
            $finder = new Finder();
            $foundFiles = $finder
                ->files()
                ->name('*.php')
                ->in($path)
                ->depth(0);
            foreach ($foundFiles as $file) {
                preg_match("/class (\\w+) (?:extends|implements) \\\\?(\\w+)/", $file->getContents(), $matches);
                $blockInstance = $this->blockFactory->getInstance($nameSpace . $matches[1], $moduleInstance);
                $foundBlocks[$module->getName() . ':' . $nameSpace . $matches[1]] = $module->getDisplayname() . '/' . $blockInstance->getType();
            }
        }
        // Add service defined blocks.
        foreach ($this->blockCollector->getBlocks() as $id => $blockInstance) {
            $className = get_class($blockInstance);
            list($moduleName, $serviceId) = explode(':', $id);
            if (isset($moduleEntity) && $moduleEntity->getName() != $moduleName) {
                continue;
            }
            if (isset($foundBlocks["$moduleName:$className"])) {
                // remove blocks found in file search with same class name
                unset($foundBlocks["$moduleName:$className"]);
            }
            $moduleEntity = $this->extensionRespository->findOneBy(['name' => $moduleName]);
            $foundBlocks[$id] = $moduleEntity->getDisplayname() . '/' . $blockInstance->getType();
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

    /**
     * {@inheritdoc}
     */
    public function getModuleBlockPath(AbstractModule $moduleInstance = null)
    {
        $path = null;
        $nameSpace = null;
        if (isset($moduleInstance)) {
            if (is_dir($moduleInstance->getPath() . '/Block')) {
                $path = $moduleInstance->getPath() . '/Block';
                $nameSpace = $moduleInstance->getNamespace() . '\Block\\';
            }
        }

        return [$nameSpace, $path];
    }
}
