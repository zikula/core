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
use \Zikula\Core\Event\GenericEvent;
use Zikula\Common\ServiceManager\ServiceManager;
use Zikula\Common\EventManager\EventManager;

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
 * public function handler(GenericEvent $event)
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
    protected $eventManager;

    /**
     * ServiceManager instance.
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * This object's reflection.
     *
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param EventManager $eventManager EventManager.
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->serviceManager = $this->eventManager->getServiceManager();
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
     *    $this->addHandlerDefinition('some.event', 'handler', 10);
     *    $this->addHandlerDefinition('some.event', 'handler2', 10);
     * </Samp>
     *
     * @return void
     */
    abstract protected function setupHandlerDefinitions();

    /**
     * Add Event definition to handler.
     *
     * @param string  $name   Name of event.
     * @param string  $method Method to invoke when called.
     * @param integer $weight Handler weight, defaults to 10.
     *
     * @throws \InvalidArgumentException If method specified is invalid.
     *
     * @return void
     */
    protected function addHandlerDefinition($name, $method, $weight=10)
    {
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(sprintf('Method %1$s does not exist in this EventHandler class %2$s', $method, get_class($this)));
        }

        $this->eventNames[] = array('name' => $name, 'method' => $method, 'weight' => $weight);
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
     * Get eventManager.
     *
     * @return \Zikula\Common\EventManager\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get servicemanager.
     *
     * @return \Zikula\Common\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Attach handler with EventManager.
     *
     * @return void
     */
    public function attach()
    {
        foreach ($this->eventNames as $callable) {
            $this->eventManager->attach($callable['name'], array($this, $callable['method']), $callable['weight']);
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
            $this->eventManager->detach($callable['name'], array($this, $callable['method']));
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