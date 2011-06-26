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
    public function offsetExists($key)
    {
        method_exists($this, "get{$key}");
    }

    public function offsetGet($key)
    {
        return $this->$key;
    }

    public function offsetSet($key, $value)
    {
        $method = "set$key";
        $this->$method($value);
    }

    public function offsetUnset($key)
    {
        $this->offsetSet($key, null);
    }

    public function toArray()
    {
        $r = new ReflectionObject($this);
        $properties = $r->getProperties();
        $array = array();
        foreach ($properties as $property) {
            $method = "get{$property->name}";
            $array[$property] = $this->$method();
        }
        return $array;
    }

    public function merge(array $array)
    {
        foreach ($array as $key => $value) {
            $method = "set$key";
            $this->$method($value);
        }
    }
}