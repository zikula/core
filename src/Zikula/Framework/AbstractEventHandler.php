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

namespace Zikula\Framework;

use Zikula\Core\Event\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

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
 * public function handler(Event $event)
 */
abstract class AbstractEventHandler
{
    /**
     * Event names.
     *
     * @var array
     */
    protected $eventNames = array();

    /**
     * EventManager instance.
     *
     * @var EventManager
     */
    protected $dispatcher;

    /**
     * DependencyInjection Container instance.
     *
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * This object's reflection.
     *
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param ContainerAwareEventDispatcher $dispatcher ContainerAwareEventDispatcher.
     */
    public function __construct(ContainerAwareEventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->container = $this->dispatcher->getContainer(); // get rid of this as it's available already in $dispatcher
        $this->setupHandlerDefinitions();
    }

    /**
     * Get reflection of this object.
     *
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new \ReflectionObject($this);
        }

        return $this->reflection;
    }

    /**
     * Required setup of handler definitions.
     *
     * Example:
     * <Samp>
     *    $this->addHandlerDefinition('some.event', 'handler', 0);
     *    $this->addHandlerDefinition('some.event', 'handler2', 0);
     * </Samp>
     *
     * @deprecated
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {

    }

    /**
     * Add Event definition to handler.
     *
     * @param string  $name   Name of event.
     * @param string  $method Method to invoke when called.
     * @param integer $priority Handler weight, defaults to 0.
     *
     * @throws \InvalidArgumentException If method specified is invalid.
     *
     * @return void
     */
    protected function addHandlerDefinition($name, $method, $priority = 0)
    {
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(sprintf('Method %1$s does not exist in this Listener class %2$s', $method, get_class($this)));
        }

        $this->eventNames[] = array('name' => $name, 'method' => $method, 'weight' => $priority);
    }

    /**
     * Get event names.
     *
     * @return array
     */
    public function getEventNames()
    {
        return $this->eventNames;
    }

    /**
     * Get dispatcher.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Get servicemanager.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Attach handler with EventManager.
     *
     * @return void
     */
    public function attach()
    {
        foreach ($this->eventNames as $callable) {
            $this->dispatcher->addListener($callable['name'], array($this, $callable['method']), $callable['weight']);
        }
    }

    /**
     * Detach event from EventManager.
     *
     * @return void
     */
    public function detach()
    {
        foreach ($this->eventNames as $callable) {
            $this->dispatcher->removeListener($callable['name'], array($this, $callable['method']));
        }
    }

    /**
     * Post constructor hook.
     *
     * @return void
     */
    public function setup()
    {
    }
}