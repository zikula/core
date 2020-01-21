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
 * This class collects names of custom events used for changing module states.
 *
 * @see \Zikula\Bundle\CoreBundle\Event\ModuleStateEvent
 */
final class CoreEvents
{
    /**
     * Occurs during Core installation before the modules are installed.
     * Stop propagation of the event to cause the core installer to fail.
     */
    public const CORE_INSTALL_PRE_MODULE = 'core.install.pre.module';

    /**
     * Occurs during Core upgrade before the modules are upgraded.
     * Stop propagation of the event to cause the core upgrader to fail.
     */
    public const CORE_UPGRADE_PRE_MODULE = 'core.upgrade.pre.module';

    /**
     * Occurs when a module has been installed.
     */
    public const MODULE_INSTALL = 'module.install';

    /**
     * Occurs after a module has been installed (on reload of the extensions view).
     */
    public const MODULE_POSTINSTALL = 'module.postinstall';

    /**
     * Occurs when a module has been upgraded to a newer version.
     */
    public const MODULE_UPGRADE = 'module.upgrade';

    /**
     * Occurs when a module has been enabled after it has been disabled before.
     */
    public const MODULE_ENABLE = 'module.enable';

    /**
     * Occurs when a module has been disabled.
     */
    public const MODULE_DISABLE = 'module.disable';

    /**
     * Occurs when a module has been removed entirely.
     */
    public const MODULE_REMOVE = 'module.remove';
}
