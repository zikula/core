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

namespace Zikula\Bundle\CoreInstallerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class Core3UpgradeListener implements EventSubscriberInterface
{
    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        ExtensionRepositoryInterface $extensionRepository,
        ManagerRegistry $managerRegistry
    ) {
        $this->extensionRepository = $extensionRepository;
        $this->managerRegistry = $managerRegistry;
    }

    public static function getSubscribedEvents()
    {
        return [
            // after \Zikula\ExtensionsModule\Listener\Core3UpgradeListener::upgrade = priority 0
            CoreUpgradePreExtensionUpgrade::class => ['resetExtensionVersions', -10]
        ];
    }

    /**
     * This listener is intended to be dispatched only one time on the upgrade from Core-2.x.x to Core-3.0.0.
     * It 'resets' the version number of the indicated extensions backward to 2.9.9 so that when the upgrade
     * is complete, they will then be upgraded to version 3.0.0. This will then synchronize the versions of all
     * core extensions to the same as the core release e.g. 3.0.0.
     */
    public function resetExtensionVersions(CoreUpgradePreExtensionUpgrade $event): void
    {
        if (!version_compare($event->getCurrentCoreVersion(), '3.0.0', '<')) {
            return;
        }
        $extensionsToReset = ['ZikulaExtensionsModule', 'ZikulaBlocksModule', 'ZikulaThemeModule', 'ZikulaUsersModule'];
        /** @var \Zikula\ExtensionsModule\Entity\ExtensionEntity[] $extensions */
        $extensions = $this->extensionRepository->findBy(['name' => $extensionsToReset]);
        foreach ($extensions as $extension) {
            $extension->setVersion('2.9.9');
        }
        $this->managerRegistry->getManager()->flush();
    }
}
