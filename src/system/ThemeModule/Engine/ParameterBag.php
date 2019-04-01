<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use Countable;
use IteratorAggregate;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * This class provides an abstracted method of collecting, managing and retrieving variables.
 * values can be stored in a namespaced array structure. i.e.
 *   'key' = ['subkey' => value, 'subkey2' => value2]
 *      or
 *   'key.subkey' = value
 *   'key.subkey2' = value2
 */
class ParameterBag implements IteratorAggregate, Countable
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
     * @param mixed $args
     * @return mixed
     */
    public function __call(string $key, $args)
    {
        return $this->get($key);
    }

    public function has(string $key): bool
    {
        $parameters = $this->resolvePath($key);
        $key = $this->resolveKey($key);

        return array_key_exists($key, $parameters);
    }

    /**
     * Gets key.
     *
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = '')
    {
        $parameters = $this->resolvePath($key);
        $key = $this->resolveKey($key);

        $value = array_key_exists($key, $parameters) ? $parameters[$key] : $default;

        return 'title' === $key ? $this->prepareTitle($value) : $value;
    }

    /**
     * Sets value.
     *   can use 'key' = ['subkey' => value, 'subkey2' => value2]
     *      or
     *   'key.subkey' = value
     *   'key.subkey2' = value2
     *
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        $parameters = &$this->resolvePath($key, true);
        $key = $this->resolveKey($key);
        $parameters[$key] = $value;
    }

    /**
     * Removes and returns value.
     *
     * @return mixed
     */
    public function remove(string $key)
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
     * Retrieve all parameters.
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Switch out array.
     */
    public function replace(array $parameters = []): void
    {
        $this->parameters = [];
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Clears and return all parameters.
     */
    public function clear(): array
    {
        $return = $this->parameters;
        $this->parameters = [];

        return $return;
    }

    /**
     * Returns an iterator for parameters.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     */
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * Resolves a path in parameters property and returns it as a reference.
     *
     * This method allows structured namespacing of parameters.
     */
    private function &resolvePath(string $key, bool $writeContext = false): array
    {
        $array = &$this->parameters;
        $key = (0 === mb_strpos($key, $this->ns)) ? mb_substr($key, 1) : $key;

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
     * This is the last part in a dot separated string.
     */
    private function resolveKey(string $key): string
    {
        if (false !== mb_strpos($key, $this->ns)) {
            $key = mb_substr($key, mb_strrpos($key, $this->ns) + 1, mb_strlen($key));
        }

        return $key;
    }

    /**
     * Applies amendments to a title value before returning it.
     */
    private function prepareTitle(string $title): string
    {
        if (!is_string($title)) {
            return $title;
        }

        $titleScheme = $this->variableApi->getSystemVar('pagetitle', '');
        if (!empty($titleScheme) && '%pagetitle%' !== $titleScheme) {
            $title = str_replace(
                ['%pagetitle%', '%sitename%'],
                [$title, $this->variableApi->getSystemVar('sitename', '')],
                $titleScheme
            );

            $moduleDisplayName = '';
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request) {
                $controllerNameParts = explode('\\', $request->attributes->get('_controller'));
                $bundleName = count($controllerNameParts) > 1 ? $controllerNameParts[0] . $controllerNameParts[1] : '';
                if ('Module' === mb_substr($bundleName, -6)) {
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
