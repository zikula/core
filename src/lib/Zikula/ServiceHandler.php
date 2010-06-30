<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ServiceHandler class.
 *
 * This is a container for a service managed by ServiceManager (by ID) and
 * a corrisponding method in that service that will handler and event.
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
