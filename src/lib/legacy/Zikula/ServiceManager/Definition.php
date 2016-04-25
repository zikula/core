<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_ServiceManager_Definition configuration describes a service for ServiceManager.
 *
 * @deprecated since 1.4.0
 * @see \Symfony\Component\DependencyInjection\Definition
 */
class Zikula_ServiceManager_Definition extends \Symfony\Component\DependencyInjection\Definition
{
    /**
     * Methods definition storage.
     *
     * @var array
     */
    protected $methods;

    /**
     * Construct a definition.
     *
     * To avoid confusion, use the setters constructArgs() and addMethod().
     *
     * @param string $className       Name of the class (with full namespace if applicable).
     * @param array  $constructorArgs Non associative array of parameters.
     * @param array  $methods         Associative array of array($method => array(0 => array($param1, $param2...),
     *                                                                            1 => array($param1, $param2...).
     */
    public function __construct($className, array $constructorArgs = array(), array $methods = array())
    {
        parent::__construct($className, $constructorArgs);

        if ($methods) {
            $this->setMethods($methods);
        }
    }

    /**
     * Getter for className property.
     *
     * @return string $className
     */
    public function getClassName()
    {
        return $this->getClass();
    }

    /**
     * Getter for the constructor arguments.
     *
     * @return array
     */
    public function getConstructorArgs()
    {
        return $this->getArguments();
    }

    /**
     * Configure the constructor arguments.
     *
     * @param array $args Non associative array of arguments, array() means none.
     *
     * @return void
     */
    public function setConstructorArgs(array $args = array())
    {
        $this->setArguments($args);
    }

    /**
     * Has constructor args test.
     *
     * @return boolean True if constructor arguments exists
     */
    public function hasConstructorArgs()
    {
        return (bool)$this->getArguments();
    }

    /**
     * Return the methods to be called on instanciation of class.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->getMethodCalls();
    }

    /**
     * Setter for methods property.
     *
     * @param array $methods Associative array of array($method => array(0 => array($param1, $param2...),
     *                                                                   1 => array($param1, $param2...).
     *
     * @return void
     */
    public function setMethods(array $methods)
    {
        foreach ($methods as $method => $args) {
            $this->addMethodCall($method, $args[0]);
        }
    }

    /**
     * Add method to be called after class is instanciated.
     *
     * Note $args must be a non-associative array of arguments
     * or array() for none.
     *
     * @param string $method Method to be called.
     * @param array  $args   Default = array() meaning no args are passed, else array of args.
     *
     * @return void
     */
    public function addMethod($method, array $args = array())
    {
        $this->addMethodCall($method, $args);
    }

    /**
     * Are there any methods that need to be called after creating this service.
     *
     * @return boolean True or false.
     */
    public function hasMethods()
    {
        return (bool)$this->getMethodCalls();
    }
}
