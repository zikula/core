<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage \Zikula\Core\Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Custom Event Handler interface.
 *
 * EventHandlers that implement this class should implement an indexed array
 * of eventname => handlerMethod like the following.  (Can contain multiple
 * index pairs).
 *
 * protected $eventNames = array('name' => 'handlerMethod')
 *
 * The handler methods must be implemented as followes:
 *
 * public function handler(Zikula_Event $event)
 */
abstract class Zikula_AbstractEventHandler extends Zikula\Framework\AbstractEventHandler
{
}