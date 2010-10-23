<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * @Entity
 * @Table(name="hook_registry")
 */
class Zikula_Doctrine_Model_Hook
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @Column(type="string", length=100)
     */
    private $hookname; // links to HookAssociation::hookname

    /**
     * @Column(type="string", length=100)
     */
    private $servicename;

    /**
     * @Column(type="string", length=100)
     */
    private $handlerclass;

    /**
     * @Column(type="string", length=100)
     */
    private $handlermethod;

    public function getId()
    {
        return $this->id;
    }

    public function getHookName()
    {
        return $this->hookname;
    }

    public function getServiceName()
    {
        return $this->servicename;
    }

    public function getHandlerClass()
    {
        return $this->handlerclass;
    }

    public function getHandlerMethod()
    {
        return $this->handlermethod;
    }

    public function setHookName($value)
    {
        $this->hookname = $value;
    }

    public function setServiceName($value)
    {
        $this->servicename = $value;
    }

    public function setHookClass($value)
    {
        $this->hookclass = $value;
    }

    public function setHandlerMethod($value)
    {
        $this->handlermethod = $value;
    }

    public function set($hookName, $serviceName, $handlerClass, $handlerMethod)
    {
        $this->hookname = $hookName;
        $this->servicename = $serviceName;
        $this->handlerclass = $handlerClass;
        $this->handlermethod = $handlerMethod;
    }
}