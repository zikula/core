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

namespace Zikula\ExtensionsModule;

class ExtensionEvents
{
    /**
     * Occurs when extension list is viewed and can veto the re-syncing of the extension list.
     * Stop propagation of the event to prevent re-sync.
     */
    public const REGENERATE_VETO = 'extensions_module.extension_events.regenerate_veto';

    /**
     * Occurs when syncing filesystem to database and new extensions are found and attempted to be inserted.
     * Stop propagation of the event to prevent extension insertion.
     * The subject of the event is the ExtensionEntity
     */
    public const INSERT_VETO = 'extensions_module.extension_events.insert_veto';

    /**
     * Occurs before an extension is removed.
     * Stop propagation of the event to prevent extension removal.
     * The subject of the event is the ExtensionEntity
     */
    public const REMOVE_VETO = 'extensions_module.extension_events.remove_veto';

    /**
     * Occurs before updating the state of an extension. The event itself cannot affect the workflow unless
     * an exception is thrown to completely halt. For example, performing a permissions check.
     * The subject of the event is the ExtensionEntity
     * The args of the event are an array with ['state' => <value>], where the state is the 'proposed' new state
     */
    public const UPDATE_STATE = 'extensions_module.extension_events.update_state';

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
