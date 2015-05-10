<?php

namespace Zikula\Core\Controller;

abstract class AbstractBlockController extends AbstractController
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
     * @return array BlockInfo.
     */
    abstract public function info();

    /**
     * Display block.
     *
     * @param array $blockInfo Blockinfo.
     *
     * @return array Blockinfo.
     */
    abstract public function display($blockInfo);

    /**
     * Modify block interface.
     *
     * @param array $blockInfo Block info.
     *
     * @return string
     */
    public function modify($blockInfo)
    {
        return '';
    }

    /**
     * Update block interface.
     *
     * @param array $blockInfo Block info.
     *
     * @return array Blockinfo.
     */
    public function update($blockInfo)
    {
        return $blockInfo;
    }

    /**
     * Magic method to for method_not_found events.
     *
     * @param string $method Method invoked.
     * @param array  $args   Arguments.
     *
     * @throws \BadMethodCallException If no event responds.
     *
     * @return string Data.
     */
    public function __call($method, $args)
    {
        $event = new \Zikula\Core\Event\GenericEvent($this, array('method' => $method, 'args' => $args));
        $this->get('eventmanager')->dispatch('block.method_not_found', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        throw new \BadMethodCallException(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
    }
}
