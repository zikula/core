<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Listener;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\ExtensionsModule\AbstractTheme;
use Zikula\ExtensionsModule\Event\ExtensionPostInstallEvent;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;

/**
 * When a new theme is installed, add non-existing block positions.
 */
class BlockPositionInstallerListener implements EventSubscriberInterface
{
    /**
     * @var BlockPositionRepositoryInterface
     */
    private $blockPositionRepository;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        BlockPositionRepositoryInterface $blockPositionRepository,
        ManagerRegistry $managerRegistry
    ) {
        $this->blockPositionRepository = $blockPositionRepository;
        $this->managerRegistry = $managerRegistry;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionPostInstallEvent::class => [['createBlockPositions']],
        ];
    }

    public function createBlockPositions(ExtensionStateEvent $event): void
    {
        $bundle = $event->getExtensionBundle();
        if (!($bundle instanceof AbstractTheme)) {
            return;
        }
        $config = $bundle->getConfig();
        $positions =[];
        foreach ($config as $realm => $attributes) {
            if (!isset($attributes['block']) || !isset($attributes['block']['positions'])) {
                continue;
            }
            $positions = array_merge($positions, $attributes['block']['positions']);
        }

        foreach (array_keys($positions) as $name) {
            $existing = $this->blockPositionRepository->findByName($name);
            if (!empty($existing)) {
                continue;
            }
            $positionEntity = new BlockPositionEntity();
            $positionEntity->setName($name);
            $positionEntity->setDescription($name . ' blocks');
            $this->managerRegistry->getManager()->persist($positionEntity);
        }
        $this->managerRegistry->getManager()->flush();
    }
}
