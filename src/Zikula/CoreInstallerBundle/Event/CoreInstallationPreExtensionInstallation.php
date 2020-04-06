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
 * Occurs during core installation before the modules are installed.
 * Stop propagation of the event to cause the core installer to fail.
 */
class CoreInstallationPreExtensionInstallation extends CoreInstallerBundleEvent
{
}
