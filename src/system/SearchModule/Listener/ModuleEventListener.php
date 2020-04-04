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
use Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostEnabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostInstallEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
use Zikula\SearchModule\Collector\SearchableModuleCollector;

/**
 * Class ModuleEventListener
 *
 * Modify search block properties based on the availability and searchability of a extension as its state changes.
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
            ExtensionPostInstallEvent::class => ['extensionEnable'],
            ExtensionPostEnabledEvent::class => ['extensionEnable'],
            ExtensionPostDisabledEvent::class => ['extensionDisable'],
            ExtensionPostRemoveEvent::class => ['extensionRemove']
        ];
    }

    public function extensionEnable(ExtensionPostEnabledEvent $event): void
    {
        $extensionName = $this->getExtensionName($event);
        if (null === $extensionName) {
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
            $properties['active'][$extensionName] = 1;
            $block->setProperties($properties);
            $this->blockRepository->persistAndFlush($block);
        }
    }

    public function extensionDisable(ExtensionPostDisabledEvent $event): void
    {
        $extensionName = $this->getExtensionName($event);
        if (null === $extensionName) {
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
            $properties['active'][$extensionName] = 0;
            $block->setProperties($properties);
            $this->blockRepository->persistAndFlush($block);
        }
    }

    public function extensionRemove(ExtensionPostRemoveEvent $event): void
    {
        $extensionName = $this->getExtensionName($event);
        if (null === $extensionName) {
            return;
        }

        // get all search blocks
        $blocks = $this->blockRepository->findBy(['blocktype' => 'Search']);
        /** @var BlockEntity[] $blocks */
        foreach ($blocks as $block) {
            $properties = $block->getProperties();
            if (isset($properties['active'][$extensionName])) {
                unset($properties['active'][$extensionName]);
            }
            $block->setProperties($properties);
            $this->blockRepository->persistAndFlush($block);
        }
    }

    private function getExtensionName(ExtensionStateEvent $event): ?string
    {
        if (null === $event->getExtensionBundle()) {
            return null;
        }
        $extensionName = $event->getExtensionBundle()->getName();
        if (null === $this->searchableModuleCollector->get($extensionName)) {
            return null;
        }

        return $extensionName;
    }
}
