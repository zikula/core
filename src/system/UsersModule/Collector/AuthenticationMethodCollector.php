<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Collector;

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

    /**
     * Constructor.
     *
     * @param VariableApiInterface $variableApi
     * @param AuthenticationMethodInterface[] $methods
     */
    public function __construct(VariableApiInterface $variableApi, iterable $methods)
    {
        $this->authenticationMethodsStatus = $variableApi->getSystemVar('authenticationMethodsStatus', []);
        foreach ($methods as $method) {
            $this->add($method);
        }
    }

    /**
     * Add a method to the collection.
     *
     * @param AuthenticationMethodInterface $method
     */
    public function add(AuthenticationMethodInterface $method)
    {
        $alias = $method->getAlias();
        if (isset($this->authenticationMethods[$alias])) {
            throw new \InvalidArgumentException('Attempting to register an authentication method with a duplicate alias. (' . $alias . ')');
        }
        $this->authenticationMethods[$alias] = $method;
        if (isset($this->authenticationMethodsStatus[$alias]) && $this->authenticationMethodsStatus[$alias]) {
            $this->activeAuthenticationMethods[$alias] = $method;
        }
    }

    /**
     * Get an authenticationMethod from the collection by alias.
     * @param $alias
     * @return AuthenticationMethodInterface|null
     */
    public function get($alias)
    {
        return isset($this->authenticationMethods[$alias]) ? $this->authenticationMethods[$alias] : null;
    }

    /**
     * Get all the authenticationMethods in the collection.
     * @return AuthenticationMethodInterface[]
     */
    public function getAll()
    {
        return $this->authenticationMethods;
    }

    /**
     * Get all the active authenticationMethods in the collection.
     * @return AuthenticationMethodInterface[]
     */
    public function getActive()
    {
        return $this->activeAuthenticationMethods;
    }

    /**
     * @return integer[] of service aliases
     */
    public function getKeys()
    {
        return array_keys($this->authenticationMethods);
    }

    /**
     * @return integer[] of active service aliases
     */
    public function getActiveKeys()
    {
        return array_keys($this->activeAuthenticationMethods);
    }
}
