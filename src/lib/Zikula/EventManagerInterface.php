<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * EventManagerInterface interface.
 */
interface Zikula_EventManagerInterface
{
    public function attach($name, $handler, $priority = 10);
    public function detach($name, $handler);
    public function notify(Zikula_EventInterface $event);
    public function flushHandlers();
}
