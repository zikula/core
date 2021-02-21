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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreInstallerBundle\Bridge\HookBundle\HookBundleInstaller;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreInstallationPreExtensionInstallation;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;

class HookBundleInstallerListener implements EventSubscriberInterface
{
    private $hookBundleInstaller;

    public function __construct(HookBundleInstaller $hookBundleInstaller)
    {
        $this->hookBundleInstaller = $hookBundleInstaller;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreInstallationPreExtensionInstallation::class => 'installHookBundle',
            CoreUpgradePreExtensionUpgrade::class => 'upgradeHookBundle'
        ];
    }

    public function installHookBundle(CoreInstallationPreExtensionInstallation $event): void
    {
        if (!$this->hookBundleInstaller->install()) {
            $event->stopPropagation();
        }
    }

    public function upgradeHookBundle(CoreUpgradePreExtensionUpgrade $event): void
    {
        if (!$this->hookBundleInstaller->upgrade($event->getCurrentCoreVersion())) {
            $event->stopPropagation();
        }
    }
}
