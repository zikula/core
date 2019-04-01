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

namespace Zikula\UsersModule\Collector;

use InvalidArgumentException;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\AuthenticationMethodInterface;

/**
 * Class AuthenticationMethodCollector
 */
class AuthenticationMethodCollector
{
    /**
     * @var AuthenticationMethodInterface[] e.g. ['alias' => ServiceObject]
     */
    private $authenticationMethods = [];

    /**
     * @var AuthenticationMethodInterface[] e.g. ['alias' => ServiceObject]
     */
    private $activeAuthenticationMethods = [];

    /**
     * @var array e.g. ['alias' => bool]
     */
    private $authenticationMethodsStatus;

    public function __construct(VariableApiInterface $variableApi, iterable $methods)
    {
        $this->authenticationMethodsStatus = $variableApi->getSystemVar('authenticationMethodsStatus', []);
        foreach ($methods as $method) {
            $this->add($method);
        }
    }

    /**
     * Add a method to the collection.
     */
    public function add(AuthenticationMethodInterface $method): void
    {
        $alias = $method->getAlias();
        if (isset($this->authenticationMethods[$alias])) {
            throw new InvalidArgumentException('Attempting to register an authentication method with a duplicate alias. (' . $alias . ')');
        }
        $this->authenticationMethods[$alias] = $method;
        if (isset($this->authenticationMethodsStatus[$alias]) && $this->authenticationMethodsStatus[$alias]) {
            $this->activeAuthenticationMethods[$alias] = $method;
        }
    }

    /**
     * Get an authenticationMethod from the collection by alias.
     */
    public function get(string $alias): ?AuthenticationMethodInterface
    {
        return $this->authenticationMethods[$alias] ?? null;
    }

    /**
     * Get all the authentication methods in the collection.
     *
     * @return AuthenticationMethodInterface[]
     */
    public function getAll(): iterable
    {
        return $this->authenticationMethods;
    }

    /**
     * Get all the active authenticationMethods in the collection.
     *
     * @return AuthenticationMethodInterface[]
     */
    public function getActive(): iterable
    {
        return $this->activeAuthenticationMethods;
    }

    /**
     * Get an array of all service aliases.
     */
    public function getKeys(): array
    {
        return array_keys($this->authenticationMethods);
    }

    /**
     * Get an array of active service aliases.
     */
    public function getActiveKeys(): array
    {
        return array_keys($this->activeAuthenticationMethods);
    }
}
