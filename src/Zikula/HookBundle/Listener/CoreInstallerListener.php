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

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\CoreEvents;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\HookBundle\HookBundleInstaller;

/**
 * Class CoreInstallerListener
 */
class CoreInstallerListener implements EventSubscriberInterface
{
    private $hookBundleInstaller;

    public function __construct(HookBundleInstaller $hookBundleInstaller)
    {
        $this->hookBundleInstaller = $hookBundleInstaller;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::CORE_INSTALL_PRE_MODULE => 'installHookBundle',
            CoreEvents::CORE_UPGRADE_PRE_MODULE => 'upgradeHookBundle'
        ];
    }

    public function installHookBundle(GenericEvent $event): void
    {
        if (!$this->hookBundleInstaller->install()) {
            $event->stopPropagation();
        }
    }

    public function upgradeHookBundle(GenericEvent $event): void
    {
        $currentVersion = $event->getArgument('currentVersion');
        if (!$this->hookBundleInstaller->upgrade($currentVersion)) {
            $event->stopPropagation();
        }
    }
}
