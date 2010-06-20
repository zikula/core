<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract controller for blocks.
 */
abstract class Zikula_Block extends Zikula_Base
{
    /**
     * Initialise interface.
     */
    abstract public function init();

    /**
     * Get info interface.
     */
    abstract public function info();

    /**
     * Displat block.
     *
     * @param array $blockinfo Blockinfo.
     */
    abstract public function display($blockinfo);

    /**
     * Modify block interface.
     *
     * @param array $blockinfo Block info.
     *
     * @return string
     */
    public function modify($blockinfo)
    {
        return '';
    }

    /**
     * Update block interface.
     *
     * @param array $blockinfo Block info.
     *
     * @return array Blockinfo.
     */
    public function update($blockinfo)
    {
        return $blockinfo;
    }

    /**
     * Magic method to for method_not_found events.
     *
     * @param string $method Method invoked.
     * @param array  $args   Arguments.
     *
     * @throws BadMethodCallException If no event responds.
     *
     * @return string Data.
     */
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