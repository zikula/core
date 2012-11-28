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

class Zikula_EntityAccess implements ArrayAccess
{
    /**
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Get this reflection.
     *
     * @return ReflectionObject
     */
    public function getReflection()
    {
        if (!is_null($this->reflection)) {
            return $this->reflection;
        }

        $this->reflection = new ReflectionObject($this);

        return $this->reflection;
    }

    public function offsetExists($key)
    {
        return method_exists($this, "get" . ucfirst($key));
    }

    public function offsetGet($key)
    {
        $method = "get" . ucfirst($key);

        return $this->$method();
    }

    public function offsetSet($key, $value)
    {
        $method = "set" . ucfirst($key);
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

        while ($r !== false) {
            $properties = $r->getProperties();
            $r = $r->getParentClass();

            foreach ($properties as $property) {
                if ($property->name == 'reflection') {
                    continue;
                }

                $method = "get" . ucfirst($property->name);
                if (method_exists($this, $method)) {
                    $array[$property->name] = $this->$method();
                }
            }
        }

        return $array;
    }

    public function merge(array $array)
    {
        foreach ($array as $key => $value) {
            $method = "set" . ucfirst($key);
            $this->$method($value);
        }
    }
}
