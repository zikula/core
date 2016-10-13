<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

/**
 * This class collects names of custom events used for changing module states.
 *
 * @see \Zikula\Core\Event\ModuleStateEvent
 */
final class CoreEvents
{
    /**
     * Occurs during Core installation before the modules are installed.
     * Stop propagation of the event to cause the core installer to fail.
     */
    const CORE_INSTALL_PRE_MODULE = 'core.install.pre.module';

    /** Occurs when a module has been installed. */
    const MODULE_INSTALL = 'module.install';

    /** Occurs after a module has been installed (on reload of the extensions view). */
    const MODULE_POSTINSTALL = 'module.postinstall';

    /** Occurs when a module has been upgraded to a newer version. */
    const MODULE_UPGRADE = 'module.upgrade';

    /** Occurs when a module has been enabled after it has been disabled before. */
    const MODULE_ENABLE = 'module.enable';

    /** Occurs when a module has been disabled. */
    const MODULE_DISABLE = 'module.disable';

    /** Occurs when a module has been removed entirely. */
    const MODULE_REMOVE = 'module.remove';
}
