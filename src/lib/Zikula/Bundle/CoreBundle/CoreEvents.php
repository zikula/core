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

namespace Zikula\Bundle\CoreBundle;

/**
 * This class collects names of custom events used for changing
 * module states during core installation and core upgrades.
 *
 * @see \Zikula\ExtensionsModule\Event\ModuleStateEvent
 */
final class CoreEvents
{
    /**
     * Occurs during core installation before the modules are installed.
     * Stop propagation of the event to cause the core installer to fail.
     */
    public const CORE_INSTALL_PRE_MODULE = 'core.install.pre.module';

    /**
     * Occurs during core upgrade before the modules are upgraded.
     * Stop propagation of the event to cause the core upgrader to fail.
     */
    public const CORE_UPGRADE_PRE_MODULE = 'core.upgrade.pre.module';
}
