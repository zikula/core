<?php
/**
 * Copyright 2010 Zikula Foundation
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

/**
 * MultiHook Event Handler class.
 */
class MultiHookHandler extends CustomEventHandler
{
    /**
     * Array of event names for this handler (usually just one).
     * 
     * @var array
     */
    protected $names = array('theme.init');

    /**
     * Event handler here.
     *
     * @param Event $event
     */
    public function handler(Event $event)
    {
        // subject is instance of Theme class.
        $subject = $event->getSubject();
        // register output filter to add MultiHook environment if requried
        if (pnModAvailable('MultiHook')) {
            $modinfo = pnModGetInfo(pnModGetIDFromName('MultiHook'));
            if (version_compare($modinfo['version'], '5.0', '>=') == 1) {
                $subject->load_filter('output', 'multihook');
                pnModAPIFunc('MultiHook', 'theme', 'preparetheme');
            }
        }
    }
}