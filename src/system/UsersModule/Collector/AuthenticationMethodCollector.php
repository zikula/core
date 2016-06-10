<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Collector;

use Zikula\UsersModule\AuthenticationMethodInterface\AuthenticationMethodInterface;

/**
 * Class AuthenticationMethodCollector
 */
class AuthenticationMethodCollector
{
    /**
     * @var AuthenticationMethodInterface[] e.g. ['alias' => ServiceObject]
     */
    private $authenticationMethods;

    public function __construct()
    {
        $this->authenticationMethods = [];
    }

    /**
     * Add a block to the collection.
     * @param string $alias
     * @param AuthenticationMethodInterface $method
     */
    public function add($alias, AuthenticationMethodInterface $method)
    {
        if (isset($this->authenticationMethods[$alias])) {
            throw new \InvalidArgumentException('Attempting to register an authentication method with a duplicate alias. (' . $alias . ')');
        }
        $this->authenticationMethods[$alias] = $method;
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

    public function getKeys()
    {
        return array_keys($this->authenticationMethods);
    }
}
