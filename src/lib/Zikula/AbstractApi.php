<?php
/**
 * Copyright 2010 Zikula Foundation.
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
 * Abstract API for modules.
 */
abstract class Zikula_AbstractApi extends Zikula_AbstractBase
{
    /**
     * Magic method for method_not_found events.
     *
     * @param string $method Method name called.
     * @param array  $args   Arguments passed to method call.
     *
     * @return mixed False if not found or mixed.
     */
    public function __call($method, $args)
    {
        $event = new Zikula_Event('api.method_not_found', $this, array('method' => $method, 'args' => $args));
        $this->eventManager->notify($event);
        if ($event->isStopped()) {
            return $event->getData();
        }

        //throw new BadMethodCallException(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
        return false; // bah - BC requirements - drak
    }
}
