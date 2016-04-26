<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract controller for blocks.
 * @deprecated
 */
abstract class Zikula_Controller_AbstractBlock extends Zikula_AbstractController
{
    /**
     * Initialise interface.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Get info interface.
     *
     * @return array Blockinfo.
     */
    abstract public function info();

    /**
     * Display block.
     *
     * @param array $blockinfo Blockinfo.
     *
     * @return array Blockinfo.
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
        $event = new \Zikula\Core\Event\GenericEvent($this, ['method' => $method, 'args' => $args]);
        $this->eventManager->dispatch('block.method_not_found', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        throw new BadMethodCallException(__f('%1$s::%2$s() does not exist.', [get_class($this), $method]));
    }

    public function getType()
    {
        return $this->info()['text_type'];
    }
}
