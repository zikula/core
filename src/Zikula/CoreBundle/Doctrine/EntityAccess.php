<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Doctrine;

use ArrayAccess;
use ReflectionObject;
use RuntimeException;

class EntityAccess implements ArrayAccess
{
    /**
     * @var ReflectionObject
     */
    protected $reflection;

    public function getReflection(): ReflectionObject
    {
        if (null !== $this->reflection) {
            return $this->reflection;
        }

        $this->reflection = new ReflectionObject($this);

        return $this->reflection;
    }

    public function offsetExists($key): bool
    {
        try {
            $this->getGetterForProperty($key);

            return true;
        } catch (RuntimeException $exception) {
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

        return $this->{$method}();
    }

    public function offsetSet($key, $value): void
    {
        $method = $this->getSetterForProperty($key);
        $this->{$method}($value);
    }

    public function offsetUnset($key): void
    {
        $this->offsetSet($key, null);
    }

    /**
     * Returns an array representation of this entity.
     */
    public function toArray(): array
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
                if (in_array($property->name, $excluded, true)) {
                    continue;
                }

                $method = $this->getGetterForProperty($property->name);
                if (!empty($method)) {
                    $array[$property->name] = $this->{$method}();
                }
            }
        }

        return $array;
    }

    public function merge(array $array = []): void
    {
        foreach ($array as $key => $value) {
            $method = $this->getSetterForProperty($key);
            $this->{$method}($value);
        }
    }

    /**
     * Returns the accessor's method name for retrieving a certain property.
     */
    private function getGetterForProperty(string $name): string
    {
        $getMethod = 'get' . ucfirst($name);
        if (method_exists($this, $getMethod)) {
            return $getMethod;
        }

        $isMethod = 'is' . ucfirst($name);
        if (method_exists($this, $isMethod)) {
            return $isMethod;
        }

        // see #1863
        return '';
    }

    private function getSetterForProperty(string $name): string
    {
        $setMethod = 'set' . ucfirst($name);
        if (method_exists($this, $setMethod)) {
            return $setMethod;
        }

        $class = get_class($this);
        throw new RuntimeException("Entity \"${class}\" does not have a setter for property \"${name}\". Please add ${setMethod}().");
    }
}
