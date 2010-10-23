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
 * @Table(name="hook_bindings")
 */
class Zikula_Doctrine_Model_HookBinding
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(type="string", length=100)
     */
    private $hookname;

    /**
     * @Column(type="string", length=100)
     */
    private $who;

    /**
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $weight;

    public function getId()
    {
        return $this->id;
    }

    public function getHookName()
    {
        return $this->hookname;
    }

    public function getWho()
    {
        return $this->who;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setHookName($value)
    {
        $this->hookname = $value;
    }

    public function setWho($value)
    {
        $this->who = strtolower($value);
    }

    public function setWeight($value)
    {
        $this->weight = $value;
    }

    public function set($hookname, $who, $weight = 1)
    {
        $this->hookname = $hookname;
        $this->who = $who;
        $this->weight = $weight;
    }
}