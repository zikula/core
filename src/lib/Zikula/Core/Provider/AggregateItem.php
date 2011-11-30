<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Provider
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Provider;

/**
 * Pending content aggregate.
 */
class AggregateItem
{
    /**
     * Type of aggregate.
     *
     * @var string
     */
    protected $type;

    /**
     * Description of aggregate.
     *
     * @var string
     */
    protected $description;

    /**
     * Number of items in aggregate.
     *
     * @var integer
     */
    protected $number;

    /**
     * Controller name.
     *
     * @var string
     */
    protected $controllerName;

    /**
     * Method.
     *
     * @var string
     */
    protected $method;

    /**
     * Arguments for method.
     *
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @param string  $type           Type of aggregate item.
     * @param string  $description    Description of aggregate.
     * @param integer $number         Number of items in aggregate.
     * @param string  $controllerName Name of controller (to view action).
     * @param string  $method         Name of method in controller (to view action).
     * @param array   $args           Arguments for method.
     */
    public function __construct($type, $description, $number, $controllerName, $method, array $args = array())
    {
        $this->type = (string)$type;
        $this->description = (string)$description;
        $this->number = (int)$number;
        $this->controllerName = (string)$controllerName;
        $this->method = (string)$method;
        $this->args = $args;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get number.
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Get Controller name.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controllerName;
    }

    /**
     * Get method (to view action).
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get method call args.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

}