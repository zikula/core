<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Doctrine;

class EntityAccess implements \ArrayAccess
{
    /**
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * Get this reflection.
     *
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        if (!is_null($this->reflection)) {
            return $this->reflection;
        }

        $this->reflection = new \ReflectionObject($this);

        return $this->reflection;
    }

    public function offsetExists($key)
    {
        try {
            $this->getGetterForProperty($key);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    public function offsetGet($key)
    {
        $method = $this->getGetterForProperty($key);

        return $this->$method();
    }

    public function offsetSet($key, $value)
    {
        $method = $this->getSetterForProperty($key);
        $this->$method($value);
    }

    public function offsetUnset($key)
    {
        $this->offsetSet($key, null);
    }

    public function toArray()
    {
        $r = $this->getReflection();
        $array = array();
        $excluded = array(
            'reflection',
            '_entityPersister',
            '_identifier',
            '__isInitialized__',
            '__initializer__',
            '__cloner__',
            'lazyPropertiesDefaults'
        );

        while($r !== false) {
            $properties = $r->getProperties();
            $r = $r->getParentClass();

            foreach ($properties as $property) {
                if (in_array($property->name, $excluded)) {
                    continue;
                }

                $method = $this->getGetterForProperty($property->name);
                $array[$property->name] = $this->$method();
            }
        }

        return $array;
    }

    public function merge(array $array)
    {
        foreach ($array as $key => $value) {
            $method = $this->getSetterForProperty($key);
            $this->$method($value);
        }
    }

    private function getGetterForProperty($name)
    {
        $getMethod = "get" . ucfirst($name);
        if (method_exists($this, $getMethod)) {
            return $getMethod;
        }

        $isMethod  = "is" . ucfirst($name);
        if (method_exists($this, $isMethod)) {
            return $isMethod;
        }

        $class = get_class($this);
        throw new \RuntimeException("Entity \"$class\" does not have a getter for property \"$name\". Please either add $getMethod() or $isMethod().");
    }

    private function getSetterForProperty($name)
    {
        $setMethod = "set" . ucfirst($name);
        if (method_exists($this, $setMethod)) {
            return $setMethod;
        }

        $class = get_class($this);
        throw new \RuntimeException("Entity \"$class\" does not have a setter for property \"$name\". Please add $setMethod().");
    }
}
