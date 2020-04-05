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

namespace Zikula\Bundle\CoreInstallerBundle\Event;

/**
 * Occurs during core upgrade before the modules are upgraded.
 * Stop propagation of the event to cause the core upgrader to fail.
 */
class CoreUpgradePreExtensionUpgrade extends CoreInstallerBundleEvent
{
    private $currentCoreVersion;

    public function __construct($currentCoreVersion)
    {
        $this->currentCoreVersion = $currentCoreVersion;
    }

    public function getCurrentCoreVersion(): string
    {
        return $this->currentCoreVersion;
    }
}
