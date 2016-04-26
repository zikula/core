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
 * Pending content aggregate.
 */
class Zikula_Provider_AggregateItem
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
     * Controller.
     *
     * @var string
     */
    protected $controller;

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
     * @param string  $type        Type of aggregate item.
     * @param string  $description Description of aggregate.
     * @param integer $number      Number of items in aggregate.
     * @param string  $controller  Name of controller (to view action).
     * @param string  $method      Name of method in controller (to view action).
     * @param array   $args        Arguments for method.
     */
    public function __construct($type, $description, $number, $controller, $method, array $args = [])
    {
        $this->type = (string)$type;
        $this->description = (string)$description;
        $this->number = (int)$number;
        $this->controller = (string)$controller;
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
        return $this->controller;
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
