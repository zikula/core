<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

/**
 * Class ParameterBag
 * @package Zikula\ThemeModule\Engine
 *
 * This class provides an abstracted method of collecting, managing and retrieving variables.
 * values can be stored in a namespaced array structure. i.e.
 *   'key' = array('subkey' => value, 'subkey2' => value2)
 *      or
 *   'key.subkey' = value
 *   'key.subkey2' = value2
 */
class ParameterBag implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * Namespace character.
     *
     * @var string
     */
    private $ns;

    public function __construct(array $array = array(), $ns = '.')
    {
        $this->parameters = $array;
        $this->ns = $ns;
    }

    /**
     * Allows Twig to fetch properties without use of ArrayAccess
     *
     * ArrayAccess is problematic because Twig uses isset() to
     * check if property field exists, so it's not possible
     * to get using default values, ie, empty.
     *
     * @param $key
     * @param $args
     *
     * @return string
     */
    public function __call($key, $args)
    {
        return $this->get($key);
    }

    public function has($key)
    {
        $parameters = $this->resolvePath($key);
        $key = $this->resolveKey($key);

        return array_key_exists($key, $parameters);
    }

    /**
     * Gets key
     *
     * @param string $key
     * @param string $default ''
     *
     * @return string
     */
    public function get($key, $default = '')
    {
        $parameters = $this->resolvePath($key);
        $key = $this->resolveKey($key);

        return array_key_exists($key, $parameters) ? $parameters[$key] : $default;
    }

    /**
     * Sets value.
     *   can use 'key' = array('subkey' => value, 'subkey2' => value2)
     *      or
     *   'key.subkey' = value
     *   'key.subkey2' = value2
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $parameters = &$this->resolvePath($key, true);
        $key = $this->resolveKey($key);
        $parameters[$key] = $value;
    }

    /**
     * Removes value
     *
     * @param $key
     *
     * @return null
     */
    public function remove($key)
    {
        $retval = null;
        $parameters = &$this->resolvePath($key);
        $key = $this->resolveKey($key);
        if (array_key_exists($key, $parameters)) {
            $retval = $parameters[$key];
            unset($parameters[$key]);
        }

        return $retval;
    }

    /**
     * Retrieve all parameters
     *
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Switch out array
     *
     * @param array $parameters
     */
    public function replace(array $parameters)
    {
        $this->parameters = array();
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Clears params.
     *
     * @return array
     */
    public function clear()
    {
        $return = $this->parameters;
        $this->parameters = array();

        return $return;
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return count($this->parameters);
    }

    /**
     * Resolves a path in parameters property and returns it as a reference.
     *
     * This method allows structured namespacing of parameters.
     *
     * @param string  $key         Key name
     * @param boolean $writeContext Write context, default false
     *
     * @return array
     */
    private function &resolvePath($key, $writeContext = false)
    {
        $array = &$this->parameters;
        $key = (strpos($key, $this->ns) === 0) ? substr($key, 1) : $key;

        // Check if there is anything to do, else return
        if (!$key) {
            return $array;
        }

        $parts = explode($this->ns, $key);
        if (count($parts) < 2) {
            if (!$writeContext) {
                return $array;
            }

            $array[$parts[0]] = array();

            return $array;
        }

        unset($parts[count($parts) - 1]);

        foreach ($parts as $part) {
            if (!array_key_exists($part, $array)) {
                if (!$writeContext) {
                    return $array;
                }

                $array[$part] = array();
            }

            $array = &$array[$part];
        }

        return $array;
    }

    /**
     * Resolves the key from the name.
     *
     * This is the last part in a dot separated string.
     *
     * @param string $key
     *
     * @return string
     */
    private function resolveKey($key)
    {
        if (strpos($key, $this->ns) !== false) {
            $key = substr($key, strrpos($key, $this->ns) + 1, strlen($key));
        }

        return $key;
    }
}
