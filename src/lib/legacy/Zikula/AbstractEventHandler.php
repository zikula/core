<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Custom Event Handler interface.
 *
 * EventHandlers that implement this class should implement an indexed array
 * of eventname => handlerMethod like the following.  (Can contain multiple
 * index pairs).
 *
 * protected $eventNames = ['name' => 'handlerMethod']
 *
 * The handler methods must be implemented as followes:
 *
 * public function handler(Zikula_Event $event)
 *
 * @deprecated
 */
abstract class Zikula_AbstractEventHandler
{
    /**
     * Event names.
     *
     * @var array
     */
    protected $eventNames = [];

    /**
     * EventManager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * ServiceManager instance.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * This object's reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param Zikula_EventManager $eventManager EventManager
     */
    public function __construct(EventDispatcherInterface $eventManager)
    {
        @trigger_error('Zikula_AbstractEventHandler is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        $this->eventManager = $eventManager;
        $this->serviceManager = $this->eventManager->getContainer();
        $this->setupHandlerDefinitions();
    }

    /**
     * Get reflection of this object.
     *
     * @return ReflectionObject
     */
    public function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new ReflectionObject($this);
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
     * @param string  $name   Name of event
     * @param string  $method Method to invoke when called
     * @param integer $weight Handler weight, defaults to 10
     *
     * @throws InvalidArgumentException If method specified is invalid
     *
     * @return void
     */
    protected function addHandlerDefinition($name, $method, $weight = 10)
    {
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException(sprintf('Method %1$s does not exist in this EventHandler class %2$s', $method, get_class($this)));
        }

        $this->eventNames[] = [
            'name' => $name,
            'method' => $method,
            'weight' => $weight
        ];
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
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get servicemanager.
     *
     * @return Zikula_ServiceManager
     */
    public function getContainer()
    {
        return $this->serviceManager;
    }

    /**
     * Get servicemanager.
     *
     * @return Zikula_ServiceManager
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
            $this->eventManager->addListener($callable['name'], [$this, $callable['method']], 0 - (int)$callable['weight']);
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
            $this->eventManager->removeListener($callable['name'], [$this, $callable['method']]);
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
