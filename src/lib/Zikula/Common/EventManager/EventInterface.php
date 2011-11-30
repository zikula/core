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
 * EventInterface interface.
 */
interface Zikula_EventInterface
{
    public function getName();
    public function getEventManager();
    public function setEventManager(Zikula_EventManagerInterface $eventManager);
    public function stop();
    public function isStopped();
}
