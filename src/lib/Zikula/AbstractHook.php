<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package XXXX
 * @subpackage XXXX
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
/**
 * AbstractHook class.
 */
class Zikula_AbstractHook implements Zikula_HookInterface, ArrayAccess
{
    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * Arguments.
     *
     * @var array
     */
    protected $args = array();

    /**
     * Subscriber area id.
     *
     * @var integer
     */
    protected $id;

    /**
     * Subscriber area id.
     *
     * @var integer
     */
    protected $areaId;

    /**
     * Caller.
     *
     * @var string
     */
    protected $caller;

    /**
     * Stop notification flag.
     *
     * @var boolean
     */
    protected $stopped = false;

    /**
     * Get caller.
     *
     * @return string
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     * Set caller.
     *
     * @return Zikula_AbstractHook
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
        return $this;
    }

    /**
     * Get subscriber area id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->areaId;
    }

    /**
     * Get subscriber area id.
     *
     * @return integer
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * Set subscriber area id.
     *
     * @param type $areaId
     *
     * @return Zikula_DisplayHook
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
        return $this;
    }

    public function stop()
    {
        $this->stopped = true;
        return $this;
    }

    public function isStopped()
    {
        return $this->stopped;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Set event name.
     *
     * @param type $name Hook event Name
     *
     * @return Zikula_AbstractHook
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add argument to event.
     *
     * @param string $key   Argument name.
     * @param mixed  $value Value.
     *
     * @return Zikula_AbstractHook
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;
        return $this;
    }

    /**
     * Set args property.
     *
     * @param array $args Arguments.
     *
     * @return Zikula_AbstractHook
     */
    public function setArgs(array $args = array())
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Get argument by key.
     *
     * @param string $key Key.
     *
     * @throws InvalidArgumentException If key is not found.
     *
     * @return mixed Contents of array key.
     */
    public function getArg($key)
    {
        if ($this->hasArg($key)) {
            return $this->args[$key];
        }

        throw new InvalidArgumentException(sprintf('%s not found in %s', $key, $this->name));
    }

    /**
     * Getter for all arguments.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Has argument.
     *
     * @param string $key Key of arguments array.
     *
     * @return boolean
     */
    public function hasArg($key)
    {
        return array_key_exists($key, $this->args);
    }

    /**
     * ArrayAccess for argument getter.
     *
     * @param string $key Array key.
     *
     * @throws InvalidArgumentException If key does not exist in $this->args.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ($this->hasArg($key)) {
            return $this->args[$key];
        }

        throw new InvalidArgumentException(sprintf('The requested key %s does not exist', $key));
    }

    /**
     * ArrayAccess for argument setter.
     *
     * @param string $key   Array key to set.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->setArg($key, $value);
    }

    /**
     * ArrayAccess for unset argument.
     *
     * @param string $key Array key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->hasArg($key)) {
            unset($this->args[$key]);
        }
    }

    /**
     * AccessArray has argument.
     *
     * @param string $key Array key.
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->hasArg($key);
    }
}
