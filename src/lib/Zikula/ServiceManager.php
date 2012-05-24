<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_ServiceManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ServiceManager class.
 */
class Zikula_ServiceManager implements ArrayAccess
{
    /**
     * Storage for services.
     *
     * @var array
     */
    private $services = array();

    /**
     * Argument storage.
     *
     * @var array
     */
    private $arguments = array();

    /**
     * ServiceManager constructor.
     *
     * Attaches a service $id to $this.
     *
     * @param string $id The identifier of this ServiceManager.
     */
    public function __construct($id = 'servicemanager')
    {
        $this->attachService($id, $this);
    }
    /**
     * Attach an existing service.
     *
     * @param string  $id      The ID of the service.
     * @param object  $service An already existing object.
     * @param boolean $shared  True if this is a single instance (default).
     *
     * @throws InvalidArgumentException If the service is already registered.
     *
     * @return object $service.
     */
    public function attachService($id, $service, $shared = true)
    {
        if ($this->hasService($id)) {
            throw new InvalidArgumentException(sprintf('Service %s is already attached', $id));
        }

        $this->services[$id] = new Zikula_ServiceManager_Service($id, null, $shared);
        $this->services[$id]->setService($service);

        return $service;
    }

    /**
     * Detach service.
     *
     * Alias for unregister service
     *
     * @param string $id Service ID.
     *
     * @throws Exception If the $id isn't registered.
     *
     * @return void
     */
    public function detachService($id)
    {
        $this->unregisterService($id);
    }

    /**
     * Register a service definition.
     *
     * This will register the definition as a service.
     *
     * @param string                           $id         Service Id.
     * @param Zikula_ServiceManager_Definition $definition Service definition.
     * @param boolean                          $shared     Shared type.
     *
     * @throws InvalidArgumentException If service ID is already registered.
     *
     * @return void
     */
    public function registerService($id, Zikula_ServiceManager_Definition $definition, $shared = true)
    {
        if ($this->hasService($id)) {
            throw new InvalidArgumentException(sprintf('Service %s is already registered', $id));
        }

        $this->services[$id] = new Zikula_ServiceManager_Service($id, $definition, $shared);
    }

    /**
     * Unregisters a service.
     *
     * @param string $id The service identifier.
     *
     * @throws InvalidArgumentException If the $id isn't registered.
     *
     * @return void
     */
    public function unregisterService($id)
    {
        if (!$this->hasService($id)) {
            throw new InvalidArgumentException(sprintf('Service %s is not registered', $id));
        }
        unset($this->services[$id]);
    }

    /**
     * Gets a service by identifier.
     *
     * If the service definition exists then the service will be created according to
     * the Definition class.  If it is singleInstance then it will be attached to the
     * service manager and the defintion deleted.  If this a multiple instance then
     * a new service will be instanciated each time it is requested.  If the service
     * exists already it will be returned.
     *
     * @param string $id The service identifier.
     *
     * @throws InvalidArgumentException If no identifier exists.
     *
     * @return object The service.
     */
    public function getService($id)
    {
        if (!$this->hasService($id)) {
            throw new InvalidArgumentException(sprintf('The service %s does not exist', $id));
        }

        $service = $this->services[$id];

        if ($service->isShared()) {
            if ($service->hasDefinition()) {
                $service->setService($this->createService($service->getDefinition()));
            }
        } else {
            if ($service->hasDefinition()) {
                return $this->createService($service->getDefinition());
            } else {
                // no definition means an instanciated object.
                return clone $service->getService();
            }
        }

        return $this->services[$id]->getService();
    }

    /**
     * True if we have the service $id registered.
     *
     * @param string $id True if the service is registered.
     *
     * @return boolean
     */
    public function hasService($id)
    {
        return array_key_exists($id, $this->services);
    }

    /**
     * Return an array of service IDs registered.
     *
     * @param string $prefix Filter service list by prefix, default = '' for no filtering.
     *
     * @return array Non associative array of service IDs.
     */
    public function listServices($prefix = '')
    {
        $list = array();
        foreach ($this->services as $service) {
            if (empty($prefix) || strpos($service->getId(), $prefix) === 0) {
                $list[] = $service->getId();
            }
        }

        return $list;
    }

    /**
     * Dynamically create the service according to the Definition class.
     *
     * @param Zikula_ServiceManager_Definition $definition The definition class.
     *
     * @return object The newly created service.
     */
    private function createService(Zikula_ServiceManager_Definition $definition)
    {
        $reflection = new ReflectionClass($definition->getClassName());

        if (($reflection->hasMethod('__construct') || $reflection->hasMethod($definition->getClassName()) && $definition->hasConstructorArgs())) {
            $service = $reflection->newInstanceArgs($this->compileArguments($definition->getConstructorArgs()));
        } else {
            $service = $reflection->newInstance();
        }

        if ($definition->hasMethods()) {
            foreach ($definition->getMethods() as $method => $arguments) {
                foreach ($arguments as $args) {
                    $reflectionMethod = new ReflectionMethod($definition->getClassName(), $method);
                    if (count($args) > 0) {
                        $reflectionMethod->invokeArgs($service, $this->compileArguments($args));
                    } else {
                        // no args
                        $reflectionMethod->invoke($service);
                    }
                }
            }
        }

        return $service;
    }

    /**
     * Compile any parameters that are Definitions, Services or Argument definitions.
     *
     * @param array $arguments Non associative array of arguments.
     *
     * @throws InvalidArgumentException If unrecognised object type.
     *
     * @return array Compiled arguments.
     */
    private function compileArguments($arguments)
    {
        $compiledArguments = array();
        foreach ($arguments as $argument) {
            switch (true) {
                case (!is_object($argument)):
                    $compiledArguments[] = $argument;
                    break;
                case ($argument instanceof Zikula_ServiceManager_Argument):
                    $compiledArguments[] = $this->getArgument($argument->getId());
                    break;
                case ($argument instanceof Zikula_ServiceManager_Reference):
                    $compiledArguments[] = $this->getService($argument->getId());
                    break;
                case ($argument instanceof Zikula_ServiceManager_Definition):
                    $compiledArguments[] = $this->createService($argument);
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Invalid argument object %s', get_class($argument)));
                    break;
             }
         }

        return $compiledArguments;
    }

    /**
     * Getter for arguments property.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Setter for arguments property.
     *
     * @param array $array Array of id=>value.
     *
     * @return void
     */
    public function setArguments(array $array)
    {
        $this->arguments = $array;
    }

    /**
     * Has argument.
     *
     * @param string $id Id.
     *
     * @return boolean
     */
    public function hasArgument($id)
    {
        return array_key_exists($id, $this->arguments);
    }

    /**
     * Set one argument.
     *
     * @param string $id    Argument id.
     * @param string $value Argument value.
     *
     * @return void
     */
    public function setArgument($id, $value)
    {
        $this->arguments[$id] = $value;
    }

    /**
     * Get argument.
     *
     * @param string $id Argument id.
     *
     * @throws InvalidArgumentException If id is not set.
     *
     * @return mixed
     */
    public function getArgument($id)
    {
        if (!$this->hasArgument($id)) {
            throw new InvalidArgumentException(sprintf('No argument "%s" is registered with Zikula_ServiceManager', $id));
        }

        return $this->arguments[$id];
    }

    /**
     * Load multiple arguments.
     *
     * @param array $array Array of id=>$value.
     *
     * @return void
     */
    public function loadArguments(array $array)
    {
        foreach ($array as $id => $value) {
            $this->setArgument($id, $value);
        }
    }

    /**
     * Getter for ArrayAccess interface.
     *
     * @param string $id Argument id.
     *
     * @return mixed Argument value.
     */
    public function offsetGet($id)
    {
        return $this->getArgument($id);
    }

    /**
     * Setter for ArrayAccess interface.
     *
     * @param string $id    Argument id.
     * @param mixed  $value Argument value.
     *
     * @return void
     */
    public function offsetSet($id, $value)
    {
        $this->setArgument($id, $value);
    }

    /**
     * Has() method on argument property for ArrayAccess interface.
     *
     * @param string $id Argument id.
     *
     * @return boolean
     */
    public function offsetExists($id)
    {
        return $this->hasArgument($id);
    }

    /**
     * Unset argument by id, implmentation for ArrayAccess.
     *
     * @param string $id Id.
     *
     * @return void
     */
    public function offsetUnset($id)
    {
        if ($this->hasArgument($id)) {
            unset($this->arguments[$id]);
        }
    }
}
