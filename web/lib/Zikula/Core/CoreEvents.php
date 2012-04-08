<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core;

class CoreEvents
{
    /**
     * Sends in a Zikula\Core\Event\GenericEvent
     *
     * Subject is instance of Zikula\Core\Core
     */
    const PREINIT = 'core.preinit';

    /**
     * Sends in a Zikula\Core\Event\GenericEvent
     *
     * Subject is Zikula\Core\Core
     * $args = array('stages' => $stages).
     */
    const INIT = 'core.init';

    /**
     * Sends in a Zikula\Core\Event\GenericEvent
     *
     * Subjct Zikula\Core\Core
     */
    const POSTINIT = 'core.postinit';

    /**
     * Sends in a Zikula\Core\Event\GenericEvent
     *
     * Subject is null
     * $args = array('stages' => $stages).
     */
    const ERRORREPORTING = 'setup.errorreporting';
}