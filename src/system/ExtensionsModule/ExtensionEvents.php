<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
     */
    const INSERT_VETO = 'extensions_module.extension_events.insert_veto';

    /**
     * Event occurs before updating the state of an extension. The event itself cannot affect the workflow unless
     * an exception is thrown to completely halt. For example, performing a permissions check.
     */
    const UPDATE_STATE = 'extensions_module.extension_events.update_state';
}