<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

class ExtensionEvents
{
    /**
     * Event occurs when extension list is viewed and can veto the re-syncing of the extension list.
     * Stop propagation of the event to prevent re-sync.
     */
    const REGENERATE_VETO = 'extensions_module.extension_events.regenerate_veto';

    /**
     * Event occurs when syncing filesystem to database and new extensions are found and attempted to be inserted.
     * Stop propagation of the event to prevent extension insertion.
     * The subject of the event is the ExtensionEntity
     */
    const INSERT_VETO = 'extensions_module.extension_events.insert_veto';

    /**
     * Event occurs before an extension is removed.
     * Stop propagation of the event to prevent extension removal.
     * The subject of the event is the ExtensionEntity
     */
    const REMOVE_VETO = 'extensions_module.extension_events.remove_veto';

    /**
     * Event occurs before updating the state of an extension. The event itself cannot affect the workflow unless
     * an exception is thrown to completely halt. For example, performing a permissions check.
     * The subject of the event is the ExtensionEntity
     * The args of the event are an array with ['state' => <value>], where the state is the 'proposed' new state
     */
    const UPDATE_STATE = 'extensions_module.extension_events.update_state';
}
