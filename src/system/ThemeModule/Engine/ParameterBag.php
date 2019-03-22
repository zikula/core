<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * Class ParameterBag
 *
 * This class provides an abstracted method of collecting, managing and retrieving variables.
 * values can be stored in a namespaced array structure. i.e.
 *   'key' = ['subkey' => value, 'subkey2' => value2]
 *      or
 *   'key.subkey' = value
 *   'key.subkey2' = value2
 */
class ParameterBag implements \IteratorAggregate, \Countable
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

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

    /**
     * @param RequestStack $requestStack
     * @param VariableApiInterface $variableApi
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param array $parameters
     * @param string $namespaceChar
     */
    public function __construct(
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        ExtensionRepositoryInterface $extensionRepository,
        array $parameters = [],
        $namespaceChar = '.'
    ) {
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->extensionRepository = $extensionRepository;
        $this->parameters = $parameters;
        $this->ns = $namespaceChar;
    }

    /**
     * Allows Twig to fetch properties without use of ArrayAccess
     *
     * ArrayAccess is problematic because Twig uses isset() to
     * check if property field exists, so it's not possible
     * to get using default values, ie, empty.
     *
     * @param string $key
     * @param string $args
     *
     * @return string
     */
    public function __call($key, $args)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
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
     * @return mixed
     */
    public function get($key, $default = '')
    {
        $parameters = $this->resolvePath($key);
        $key = $this->resolveKey($key);

        $value = array_key_exists($key, $parameters) ? $parameters[$key] : $default;

        return 'title' == $key ? $this->prepareTitle($value) : $value;
    }

    /**
     * Sets value.
     *   can use 'key' = ['subkey' => value, 'subkey2' => value2]
     *      or
     *   'key.subkey' = value
     *   'key.subkey2' = value2
     *
     * @param string $key
     * @param mixed $value
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
     * @param string $key
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
        $this->parameters = [];
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
        $this->parameters = [];

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
     * @param string $key Key name
     * @param boolean $writeContext Write context, default false
     *
     * @return array
     */
    private function &resolvePath($key, $writeContext = false)
    {
        $array = &$this->parameters;
        $key = (0 === strpos($key, $this->ns)) ? substr($key, 1) : $key;

        // Check if there is anything to do, else return
        if (!$key) {
            return $array;
        }

        $parts = explode($this->ns, $key);
        if (count($parts) < 2) {
            if (!$writeContext) {
                return $array;
            }

            $array[$parts[0]] = [];

            return $array;
        }

        unset($parts[count($parts) - 1]);

        foreach ($parts as $part) {
            if (!array_key_exists($part, $array)) {
                if (!$writeContext) {
                    return $array;
                }

                $array[$part] = [];
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
        if (false !== strpos($key, $this->ns)) {
            $key = substr($key, strrpos($key, $this->ns) + 1, strlen($key));
        }

        return $key;
    }

    /**
     * Applies amendments to a title value before returning it.
     *
     * @param mixed $title
     *
     * @return string
     */
    private function prepareTitle($title)
    {
        if (!is_string($title)) {
            return $title;
        }

        $titleScheme = $this->variableApi->getSystemVar('pagetitle', '');
        if (!empty($titleScheme) && '%pagetitle%' != $titleScheme) {
            $title = str_replace('%pagetitle%', $title, $titleScheme);
            $title = str_replace('%sitename%', $this->variableApi->getSystemVar('sitename', ''), $title);

            $moduleDisplayName = '';
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request) {
                $controllerNameParts = explode('\\', $request->attributes->get('_controller'));
                $bundleName = count($controllerNameParts) > 1 ? $controllerNameParts[0] . $controllerNameParts[1] : '';
                if ('Module' == substr($bundleName, -6)) {
                    $module = $this->extensionRepository->get($bundleName);
                    if (null !== $module) {
                        $moduleDisplayName = $module->getDisplayName();
                    }
                }
            }
            $title = str_replace('%modulename%', $moduleDisplayName, $title);
        }

        return $title;
    }
}
