<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * Container is a dependency injection container.
 *
 * It gives access to object instances (services).
 *
 * Services and parameters are simple key/pair stores.
 *
 * Parameter and service keys are case insensitive.
 *
 * A service id can contain lowercased letters, digits, underscores, and dots.
 * Underscores are used to separate words, and dots to group services
 * under namespaces:
 *
 * <ul>
 *   <li>request</li>
 *   <li>mysql_session_storage</li>
 *   <li>symfony.mysql_session_storage</li>
 * </ul>
 *
 * A service can also be defined by creating a method named
 * getXXXService(), where XXX is the camelized version of the id:
 *
 * <ul>
 *   <li>request -> getRequestService()</li>
 *   <li>mysql_session_storage -> getMysqlSessionStorageService()</li>
 *   <li>symfony.mysql_session_storage -> getSymfony_MysqlSessionStorageService()</li>
 * </ul>
 *
 * The container can have three possible behaviors when a service does not exist:
 *
 *  * EXCEPTION_ON_INVALID_REFERENCE: Throws an exception (the default)
 *  * NULL_ON_INVALID_REFERENCE:      Returns null
 *  * IGNORE_ON_INVALID_REFERENCE:    Ignores the wrapping command asking for the reference
 *                                    (for instance, ignore a setter if the service does not exist)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @api
 */
class Container implements ContainerInterface
{
    protected $parameterBag;
    protected $services;
    protected $scopes;
    protected $scopeChildren;
    protected $scopedServices;
    protected $scopeStacks;
    protected $loading = array();

    /**
     * Constructor.
     *
     * @param ParameterBagInterface $parameterBag A ParameterBagInterface instance
     *
     * @api
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = null === $parameterBag ? new ParameterBag() : $parameterBag;

        $this->services       = array();
        $this->scopes         = array();
        $this->scopeChildren  = array();
        $this->scopedServices = array();
        $this->scopeStacks    = array();

        $this->set('service_container', $this);
    }

    /**
     * Compiles the container.
     *
     * This method does two things:
     *
     *  * Parameter values are resolved;
     *  * The parameter bag is frozen.
     *
     * @api
     */
    public function compile()
    {
        $this->parameterBag->resolve();

        $this->parameterBag = new FrozenParameterBag($this->parameterBag->all());
    }

    /**
     * Returns true if the container parameter bag are frozen.
     *
     * @return Boolean true if the container parameter bag are frozen, false otherwise
     *
     * @api
     */
    public function isFrozen()
    {
        return $this->parameterBag instanceof FrozenParameterBag;
    }

    /**
     * Gets the service container parameter bag.
     *
     * @return ParameterBagInterface A ParameterBagInterface instance
     *
     * @api
     */
    public function getParameterBag()
    {
        return $this->parameterBag;
    }

    /**
     * Gets a parameter.
     *
     * @param  string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws  \InvalidArgumentException if the parameter is not defined
     *
     * @api
     */
    public function getParameter($name)
    {
        return $this->parameterBag->get($name);
    }

    /**
     * Checks if a parameter exists.
     *
     * @param  string $name The parameter name
     *
     * @return Boolean The presence of parameter in container
     *
     * @api
     */
    public function hasParameter($name)
    {
        return $this->parameterBag->has($name);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     *
     * @api
     */
    public function setParameter($name, $value)
    {
        $this->parameterBag->set($name, $value);
    }

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     * @param string $scope   The scope of the service
     *
     * @api
     */
    public function set($id, $service, $scope = self::SCOPE_CONTAINER)
    {
        if (self::SCOPE_PROTOTYPE === $scope) {
            throw new \InvalidArgumentException('You cannot set services of scope "prototype".');
        }

        $id = strtolower($id);

        if (self::SCOPE_CONTAINER !== $scope) {
            if (!isset($this->scopedServices[$scope])) {
                throw new \RuntimeException('You cannot set services of inactive scopes.');
            }

            $this->scopedServices[$scope][$id] = $service;
        }

        $this->services[$id] = $service;
    }

    /**
     * Returns true if the given service is defined.
     *
     * @param  string  $id      The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     *
     * @api
     */
    public function has($id)
    {
        $id = strtolower($id);

        return isset($this->services[$id]) || method_exists($this, 'get'.strtr($id, array('_' => '', '.' => '_')).'Service');
    }

    /**
     * Gets a service.
     *
     * If a service is both defined through a set() method and
     * with a set*Service() method, the former has always precedence.
     *
     * @param  string  $id              The service identifier
     * @param  integer $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws \InvalidArgumentException if the service is not defined
     *
     * @see Reference
     *
     * @api
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $id = strtolower($id);

        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->loading[$id])) {
            throw new ServiceCircularReferenceException($id, array_keys($this->loading));
        }

        if (method_exists($this, $method = 'get'.strtr($id, array('_' => '', '.' => '_')).'Service')) {
            $this->loading[$id] = true;

            try {
                $service = $this->$method();
            } catch (\Exception $e) {
                unset($this->loading[$id]);
                throw $e;
            }

            unset($this->loading[$id]);

            return $service;
        }

        if (self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new ServiceNotFoundException($id);
        }
    }

    /**
     * Gets all service ids.
     *
     * @return array An array of all defined service ids
     */
    public function getServiceIds()
    {
        $ids = array();
        $r = new \ReflectionClass($this);
        foreach ($r->getMethods() as $method) {
            if (preg_match('/^get(.+)Service$/', $method->getName(), $match)) {
                $ids[] = self::underscore($match[1]);
            }
        }

        return array_unique(array_merge($ids, array_keys($this->services)));
    }

    /**
     * This is called when you enter a scope
     *
     * @param string $name
     *
     * @api
     */
    public function enterScope($name)
    {
        if (!isset($this->scopes[$name])) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not exist.', $name));
        }

        if (self::SCOPE_CONTAINER !== $this->scopes[$name] && !isset($this->scopedServices[$this->scopes[$name]])) {
            throw new \RuntimeException(sprintf('The parent scope "%s" must be active when entering this scope.', $this->scopes[$name]));
        }

        // check if a scope of this name is already active, if so we need to
        // remove all services of this scope, and those of any of its child
        // scopes from the global services map
        if (isset($this->scopedServices[$name])) {
            $services = array($this->services, $name => $this->scopedServices[$name]);
            unset($this->scopedServices[$name]);

            foreach ($this->scopeChildren[$name] as $child) {
                $services[$child] = $this->scopedServices[$child];
                unset($this->scopedServices[$child]);
            }

            // update global map
            $this->services = call_user_func_array('array_diff_key', $services);
            array_shift($services);

            // add stack entry for this scope so we can restore the removed services later
            if (!isset($this->scopeStacks[$name])) {
                $this->scopeStacks[$name] = new \SplStack();
            }
            $this->scopeStacks[$name]->push($services);
        }

        $this->scopedServices[$name] = array();
    }

    /**
     * This is called to leave the current scope, and move back to the parent
     * scope.
     *
     * @param string $name The name of the scope to leave
     * @throws \InvalidArgumentException if the scope is not active
     *
     * @api
     */
    public function leaveScope($name)
    {
        if (!isset($this->scopedServices[$name])) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" is not active.', $name));
        }

        // remove all services of this scope, or any of its child scopes from
        // the global service map
        $services = array($this->services, $this->scopedServices[$name]);
        unset($this->scopedServices[$name]);
        foreach ($this->scopeChildren[$name] as $child) {
            if (!isset($this->scopedServices[$child])) {
                continue;
            }

            $services[] = $this->scopedServices[$child];
            unset($this->scopedServices[$child]);
        }
        $this->services = call_user_func_array('array_diff_key', $services);

        // check if we need to restore services of a previous scope of this type
        if (isset($this->scopeStacks[$name]) && count($this->scopeStacks[$name]) > 0) {
            $services = $this->scopeStacks[$name]->pop();
            $this->scopedServices += $services;

            array_unshift($services, $this->services);
            $this->services = call_user_func_array('array_merge', $services);
        }
    }

    /**
     * Adds a scope to the container.
     *
     * @param ScopeInterface $scope
     *
     * @api
     */
    public function addScope(ScopeInterface $scope)
    {
        $name = $scope->getName();
        $parentScope = $scope->getParentName();

        if (self::SCOPE_CONTAINER === $name || self::SCOPE_PROTOTYPE === $name) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" is reserved.', $name));
        }
        if (isset($this->scopes[$name])) {
            throw new \InvalidArgumentException(sprintf('A scope with name "%s" already exists.', $name));
        }
        if (self::SCOPE_CONTAINER !== $parentScope && !isset($this->scopes[$parentScope])) {
            throw new \InvalidArgumentException(sprintf('The parent scope "%s" does not exist, or is invalid.', $parentScope));
        }

        $this->scopes[$name] = $parentScope;
        $this->scopeChildren[$name] = array();

        // normalize the child relations
        while ($parentScope !== self::SCOPE_CONTAINER) {
            $this->scopeChildren[$parentScope][] = $name;
            $parentScope = $this->scopes[$parentScope];
        }
    }

    /**
     * Returns whether this container has a certain scope
     *
     * @param string $name The name of the scope
     * @return Boolean
     *
     * @api
     */
    public function hasScope($name)
    {
        return isset($this->scopes[$name]);
    }

    /**
     * Returns whether this scope is currently active
     *
     * This does not actually check if the passed scope actually exists.
     *
     * @param string $name
     * @return Boolean
     *
     * @api
     */
    public function isScopeActive($name)
    {
        return isset($this->scopedServices[$name]);
    }

    /**
     * Camelizes a string.
     *
     * @param string $id A string to camelize
     * @return string The camelized string
     */
    static public function camelize($id)
    {
        return preg_replace_callback('/(^|_|\.)+(.)/', function ($match) { return ('.' === $match[1] ? '_' : '').strtoupper($match[2]); }, $id);
    }

    /**
     * A string to underscore.
     *
     * @param string $id The string to underscore
     * @return string The underscored string
     */
    static public function underscore($id)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr($id, '_', '.')));
    }
}
