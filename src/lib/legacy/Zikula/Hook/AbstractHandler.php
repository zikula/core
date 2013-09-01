<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Custom Hook Handler interface.
 *
 * @deprecated since Core 1.3.6
 * @see Zikula\Core\Hook\AbstractHookListener
 */
abstract class Zikula_Hook_AbstractHandler extends Zikula\Core\Hook\AbstractHookListener
{
    function __construct(Zikula_EventManager $eventManager)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        parent::__construct($eventManager);
    }

    /**
     * Get eventManager.
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->getDispatcher();
    }
}
