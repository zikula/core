<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ZEvent
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class ZEvent
{
    // properties
    protected $name;
    protected $subject;
    protected $args;
    protected $notified;

    // storage
    protected $results;

    /**
     * Encapsulate an event called $name with $subject
     *
     * @param string $name
     * @param object $subject
     * @param array $args
     */
    public function __construct($name, $subject = null, array $args = array())
    {
        // must have a name
        if (empty($name)) {
            throw new InvalidArgumentException('$name is a required argument.');
        }
        // validate $args must be an associative array of keys (non-numeric)
        if (count($args) > 0 && isset($args[0])) {
            throw new InvalidArgumentException('$args must be an empty or associative array.');
        }

        $this->subject = $subject;
        $this->name = (string) $name;
        $this->args = $args;

        // setup state information
        $this->notified = false;
        $this->results = null;
    }

    // setters

    /**
     * @param $subject the $subject to set
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }


    // getters

    /**
     * return the value of a parameter
     *
     * @param string $key
     */
    public function getArgs($key)
    {
        if (isset($this->args[$key])) {
            return $this->args[$key];
        }
        //throw new InvalidArgumentException('$key not found in "args" property..');
        return false;
    }

    /**
     * @return the $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return the $storage
     */
    public function getResults()
    {
        return $this->results;
    }

    // methods

    /**
     * Answer questionm is the event completed?
     *
     * @return the $complete
     */
    public function isNotified()
    {
        return $this->notified;
    }

    /**
     * Flag event as completed
     */
    public function setNotified()
    {
        $this->notified = true;
    }

    /**
     * @param the $vars
     */
    public function saveResults($vars)
    {
        $this->results = $vars;
    }

}