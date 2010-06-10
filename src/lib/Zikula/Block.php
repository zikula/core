<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract controller for blocks.
 */
abstract class Zikula_Block extends Zikula_Base
{
    abstract public function init();

    abstract public function info();

    abstract public function display($blockinfo);

    public function modify($blockinfo)
    {
        return '';
    }

    public function update($blockinfo)
    {
        return $blockinfo;
    }

    public function __call($method, $args)
    {
        $event = new Zikula_Event('block.method_not_found', $this, array('method' => $method, 'args' => $args));
        EventUtil::notifyUntil($event);
        if ($event->hasNotified()) {
            return $event->getData();
        }

        throw new BadMethodCallException(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
    }
}