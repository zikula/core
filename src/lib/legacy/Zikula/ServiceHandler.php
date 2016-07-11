<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ServiceHandler class.
 *
 * This is a container for a service managed by ServiceManager (by ID) and
 * a corresponding method in that service that will handler and event.
 *
 * @deprecated since 1.4.0
 */
class Zikula_ServiceHandler
{
    /**
     * Id of a service.
     *
     * @var string
     */
    protected $id;

    /**
     * Method to call in service.
     *
     * @var string
     */
    protected $methodName;

    /**
     * ServiceHandler constructor.
     *
     * @param string $id         The identifier of a service manageg by ServiceManager.
     * @param string $methodName The method that handles event.
     */
    public function __construct($id, $methodName)
    {
        $this->id = $id;
        $this->methodName = $methodName;
    }

    /**
     * Getter for id property.
     *
     * @return string Service ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter for methodName property.
     *
     * @return string The name of the method that handles the event.
     */
    public function getMethodName()
    {
        return $this->methodName;
    }
}
