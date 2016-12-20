<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract API for modules.
 *
 * @deprecated
 */
abstract class Zikula_AbstractApi extends Zikula_AbstractBase
{
    /**
     * Magic method for method_not_found events.
     *
     * @param string $method Method name called
     * @param array  $args   Arguments passed to method call
     *
     * @return mixed False if not found or mixed
     */
    public function __call($method, $args)
    {
        @trigger_error('Zikula_AbstractApi is deprecated.', E_USER_DEPRECATED);

        $event = new \Zikula\Core\Event\GenericEvent($this, ['method' => $method, 'args' => $args]);
        $this->eventManager->dispatch('api.method_not_found', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        //throw new BadMethodCallException(__f('%1$s::%2$s() does not exist.', [get_class($this), $method]));
        return false; // BC requirements
    }
}
