<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        // see #1863
        if (empty($method)) {
            return null;
        }

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

    /**
     * Returns an array representation of this entity.
     *
     * @return array An array containing properties of this entity
     */
    public function toArray()
    {
        $r = $this->getReflection();
        $array = [];
        $excluded = [
            'reflection',
            '_entityPersister',
            '_identifier',
            '__isInitialized__',
            '__initializer__',
            '__cloner__',
            'lazyPropertiesDefaults'
        ];

        while (false !== $r) {
            $properties = $r->getProperties();
            $r = $r->getParentClass();

            foreach ($properties as $property) {
                if (in_array($property->name, $excluded)) {
                    continue;
                }

                $method = $this->getGetterForProperty($property->name);
                if (!empty($method)) {
                    $array[$property->name] = $this->$method();
                }
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

    /**
     * Returns the accessor's method name for retrieving a certain property.
     *
     * @param string $name Name of property to be retrieved
     *
     * @return string Name of method to be used as accessor for the given property
     */
    private function getGetterForProperty($name)
    {
        $getMethod = 'get' . ucfirst($name);
        if (method_exists($this, $getMethod)) {
            return $getMethod;
        }

        $isMethod  = 'is' . ucfirst($name);
        if (method_exists($this, $isMethod)) {
            return $isMethod;
        }

        // see #1863
        return '';
    }

    private function getSetterForProperty($name)
    {
        $setMethod = 'set' . ucfirst($name);
        if (method_exists($this, $setMethod)) {
            return $setMethod;
        }

        $class = get_class($this);
        throw new \RuntimeException("Entity \"$class\" does not have a setter for property \"$name\". Please add $setMethod().");
    }
}
