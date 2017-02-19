<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\CapabilityApi;
use Zikula\SearchModule\Collector\SearchableModuleCollector;

/**
 * Class ModuleEventListener
 *
 * Modify Search block properties based on the availability and searchability of a module as its state changes
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

    /**
     * ModuleEventListener constructor.
     * @param SearchableModuleCollector $searchableModuleCollector
     */
    public function __construct(SearchableModuleCollector $searchableModuleCollector, BlockRepositoryInterface $blockRepository)
    {
        $this->searchableModuleCollector = $searchableModuleCollector;
        $this->blockRepository = $blockRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_INSTALL => ['moduleEnable'],
            CoreEvents::MODULE_ENABLE => ['moduleEnable'],
            CoreEvents::MODULE_DISABLE => ['moduleDisable'],
            CoreEvents::MODULE_REMOVE => ['moduleRemove'],
        ];
    }

    public function moduleEnable(ModuleStateEvent $event)
    {
        $moduleName = $this->getModuleName($event);
        if (null == $moduleName) {
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

    public function moduleDisable(ModuleStateEvent $event)
    {
        $moduleName = $this->getModuleName($event);
        if (null == $moduleName) {
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

    public function moduleRemove(ModuleStateEvent $event)
    {
        $moduleName = $this->getModuleName($event);
        if (null == $moduleName) {
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

    private function getModuleName(ModuleStateEvent $event)
    {
        // at Core-2.0, remove the capability check and only use the searchableModuleCollector
        $moduleName = $event->getModule()->getName();
        if ((null == $this->searchableModuleCollector->get($moduleName))) {
            return null;
        }

        return $moduleName;
    }
}
