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

namespace Zikula\SearchModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Bundle\CoreBundle\CoreEvents;
use Zikula\Bundle\CoreBundle\Event\ModuleStateEvent;
use Zikula\SearchModule\Collector\SearchableModuleCollector;

/**
 * Class ModuleEventListener
 *
 * Modify search block properties based on the availability and searchability of a module as its state changes.
 */
class ModuleEventListener implements EventSubscriberInterface
{
    /**
     * @var SearchableModuleCollector
     */
    private $searchableModuleCollector;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    public function __construct(
        SearchableModuleCollector $searchableModuleCollector,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->searchableModuleCollector = $searchableModuleCollector;
        $this->blockRepository = $blockRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_INSTALL => ['moduleEnable'],
            CoreEvents::MODULE_ENABLE => ['moduleEnable'],
            CoreEvents::MODULE_DISABLE => ['moduleDisable'],
            CoreEvents::MODULE_REMOVE => ['moduleRemove']
        ];
    }

    public function moduleEnable(ModuleStateEvent $event): void
    {
        $moduleName = $this->getModuleName($event);
        if (null === $moduleName) {
            return;
        }

        // get all search blocks
        $blocks = $this->blockRepository->findBy(['blocktype' => 'Search']);
        /** @var BlockEntity[] $blocks */
        foreach ($blocks as $block) {
            $properties = $block->getProperties();
            if (!isset($properties['active'])) {
                $properties['active'] = [];
            }
            $properties['active'][$moduleName] = 1;
            $block->setProperties($properties);
            $this->blockRepository->persistAndFlush($block);
        }
    }

    public function moduleDisable(ModuleStateEvent $event): void
    {
        $moduleName = $this->getModuleName($event);
        if (null === $moduleName) {
            return;
        }

        // get all search blocks
        $blocks = $this->blockRepository->findBy(['blocktype' => 'Search']);
        /** @var BlockEntity[] $blocks */
        foreach ($blocks as $block) {
            $properties = $block->getProperties();
            if (!isset($properties['active'])) {
                $properties['active'] = [];
            }
            $properties['active'][$moduleName] = 0;
            $block->setProperties($properties);
            $this->blockRepository->persistAndFlush($block);
        }
    }

    public function moduleRemove(ModuleStateEvent $event): void
    {
        $moduleName = $this->getModuleName($event);
        if (null === $moduleName) {
            return;
        }

        // get all search blocks
        $blocks = $this->blockRepository->findBy(['blocktype' => 'Search']);
        /** @var BlockEntity[] $blocks */
        foreach ($blocks as $block) {
            $properties = $block->getProperties();
            if (isset($properties['active'][$moduleName])) {
                unset($properties['active'][$moduleName]);
            }
            $block->setProperties($properties);
            $this->blockRepository->persistAndFlush($block);
        }
    }

    private function getModuleName(ModuleStateEvent $event): ?string
    {
        if (null === $event->getModule()) {
            return null;
        }
        $moduleName = $event->getModule()->getName();
        if (null === $this->searchableModuleCollector->get($moduleName)) {
            return null;
        }

        return $moduleName;
    }
}
