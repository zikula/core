<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract controller for modules.
 */
abstract class AbstractApi extends AbstractBase
{
    public function __call($method, $args)
    {
        $event = new Event('controller_api.method_not_found', $this, array('method' => $method, 'args' => $args));
        EventManagerUtil::notifyUntil($event);
        if ($event->hasNotified()) {
            return $event->getData();
        }

        //throw new BadMethodCallException(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
        return false; // bah - BC requirements - drak
    }
}