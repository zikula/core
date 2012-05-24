<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Request
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Request class.
 */
abstract class Zikula_Request_AbstractRequest
{
    /**
     * Requests array (ArrayIterator).
     *
     * @var ArrayIterator
     */
    protected $requests;

    /**
     * Request arguments.
     *
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @param array $requests Array of requests.
     * @param array $options  Indexed array of arrays requests arguments.
     */
    public function __construct(array $requests = array(), array $options = array())
    {
        $this->requests = new ArrayIterator($requests);
        $this->args = isset($options['args']) ? new Zikula_Request_Collection($options['args']) : new Zikula_Request_Collection(array());
        $this->initialize($options);
    }

    /**
     * Initialization function.
     *
     * @param array $options Array of arrays (e.g. get, post, server).
     *
     * @return void
     */
    abstract protected function initialize(array $options = array());

    /**
     * Get current module name from the request stack.
     *
     * @return string
     */
    public function getModuleName()
    {
        $array = $this->requests->current();

        return $array['module'];
    }

    /**
     * Get current controller name from the request stack.
     *
     * @return string
     */
    public function getControllerName()
    {
        $array = $this->requests->current();

        return $array['controller'];
    }

    /**
     * Get current action name from the request stack.
     *
     * @return string
     */
    public function getActionName()
    {
        $array = $this->requests->current();

        return $array['action'];
    }

    /**
     * Add request to the stack.
     *
     * @param string $module     Module controller name.
     * @param string $controller Controller type.
     * @param string $action     Action name.
     *
     * @return void
     */
    public function addRequest($module, $controller, $action)
    {
        $this->requests->append(array('module' => $module, 'controller' => $controller, 'action' => $action));
    }

    /**
     * Getter for request property.
     *
     * @return object ArrayIterator.
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Getter for args property.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Setter for args property.
     *
     * @param array $args Array of args.
     *
     * @return void
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    /**
     * Get arg by key.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not set.
     *
     * @return mixed
     */
    public function getArg($key, $default = null)
    {
        return $this->arg->get($key, $default);
    }

    /**
     * Set single key in args property.
     *
     * @param string $key   Key.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;
    }

    /**
     * Check if args has $key.
     *
     * @param string $key Key.
     *
     * @return boolean
     */
    public function hasArg($key)
    {
        return isset($this->args[$key]);
    }

    /**
     * Unset $key from args property.
     *
     * @param string $key Key.
     *
     * @return void
     */
    public function unsetArg($key)
    {
        unset($this->args[$key]);
    }
}
